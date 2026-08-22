<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import axios from 'axios'; // Pastiin axios di-import buat fetchOngkir
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomSelectSearch from '@/Components/Form/CustomSelectSearch.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import CustomInputNumber from '@/Components/Form/CustomInputNumber.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomTextarea from '@/Components/Form/CustomTextarea.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';

// Komponen Reusable
import OrderItemsTable from '../Pesan/Partials/OrderItemsTable.vue';
import OrderFormCard from '../Pesan/Partials/OrderFormCard.vue';
import OrderSummary from '../Pesan/Partials/OrderSummary.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    customers: Array,
});

// ==========================================
// 1. STATE FORM PENAWARAN & KERANJANG
// ==========================================
const cartItems = ref([]);

const form = useForm({
    id_customer: '',
    id_alamat: '',
    berlaku_sampai: '',
    catatan_penawaran: 'Catatan:\n- Proses Pengerjaan 7 Hari (Minggu/Tanggal Merah tidak dihitung)\n- Harga belum termasuk biaya pengiriman\n- Harga sewaktu-waktu dapat berubah sebelum ada persetujuan\n- Pembayaran DP 50% untuk pengerjaan & pelunasan sebelum pengiriman',

    // Ekspedisi State
    ekspedisi_nama: 'Belum Termasuk Biaya Kirim',
    ekspedisi_layanan: '',
    harga_ongkir: 0,
});

// ==========================================
// 2. PELANGGAN, ALAMAT & HITUNG ULANG OTOMATIS
// ==========================================
const customerOptions = computed(() => {
    return props.customers.map(c => ({
        id_customer: c.id_customer,
        nama_tampilan: `${c.user?.name || 'Walk-In'} (${c.no_hp})`
    }));
});

const alamatOptions = computed(() => {
    if (!form.id_customer) return [];
    const selectedCust = props.customers.find(c => c.id_customer === form.id_customer);
    if (!selectedCust || !selectedCust.alamat) return [];

    return selectedCust.alamat.map(a => ({
        id_alamat: a.id_alamat,
        alamat_lengkap: `${a.label || 'Alamat'} - ${a.alamat_lengkap} (${a.kota})`
    }));
});

const recalculateCartItems = (selectedCust) => {
    if (cartItems.value.length === 0) return;

    const roleId = selectedCust?.id_role_customer;
    const roleName = selectedCust?.role_customer?.nama_role || 'Member';
    let hasUpdates = false;

    cartItems.value = cartItems.value.map(item => {
        if (!item.master_diskon_customer) return item;
        hasUpdates = true;

        const qty = Number(item.jumlah) || 1;
        const hargaAwalSla = item.harga_dasar_awal_snapshot || 0;

        let diskonGrosir = 0;
        let namaDiskonGrosir = '';

        // Diskon Grosir
        let activeTier = null;
        if (item.master_harga_bertingkat && item.master_harga_bertingkat.length > 0) {
             const tiersSlaSama = item.master_harga_bertingkat.filter(t => t.pengerjaan === item.estimasi_pengerjaan_snapshot);

             activeTier = [...tiersSlaSama]
                .sort((a, b) => b.min - a.min)
                .find(t => qty >= t.min && (t.max === 0 || t.max === null || qty <= t.max));

             if (activeTier) {
                 diskonGrosir = Math.max(0, hargaAwalSla - Number(activeTier.nilai));
                 namaDiskonGrosir = `Harga Grosir Qty ${qty}`;
             }
        }

        // Diskon Role Member
        let diskonMember = 0;
        let namaDiskonMember = '';

        if (roleId) {
            const d = item.master_diskon_customer.find(x => String(x.id_role_customer) === String(roleId));
            if (d) {
                const hargaSetelahGrosir = Math.max(0, hargaAwalSla - diskonGrosir);
                diskonMember = d.tipe === 'persen' ? hargaSetelahGrosir * (Number(d.nilai) / 100) : Number(d.nilai);
                namaDiskonMember = d.tipe === 'persen' ? `Diskon ${roleName} (${d.nilai}%)` : `Diskon ${roleName} (Nominal)`;
            }
        }

        const rincianDiskon = [];
        if (diskonGrosir > 0) rincianDiskon.push({ nama: namaDiskonGrosir, nominal: diskonGrosir });
        if (diskonMember > 0) rincianDiskon.push({ nama: namaDiskonMember, nominal: diskonMember });

        const totalDiskonSatuan = diskonGrosir + diskonMember;
        item.total_diskon_snapshot = totalDiskonSatuan;
        item.rincian_diskon_snapshot = rincianDiskon;

        // Harga Murni Setelah Diskon
        item.harga_satuan_snapshot = Math.max(0, hargaAwalSla - totalDiskonSatuan);

        // Kalkulasi Luas / Halaman Tambahan
        let hargaSatuProdukFull = item.harga_satuan_snapshot;

        if (item.tipe_kalkulasi === 'cetak_buku') {
            let hal = parseInt(item.atribut_custom_snapshot?.['Jumlah Halaman'], 10);
            if (isNaN(hal) || hal < 1) hal = 1;

            let sisi = 1;
            const namaSkuLengkap = (item.nama_produk_snapshot || '').toLowerCase();
            if (namaSkuLengkap.includes('2 sisi') || namaSkuLengkap.includes('dua sisi') || namaSkuLengkap.includes('bolak')) {
                sisi = 2;
            }

            const tambahanHalaman = Math.max(0, hal - 1);
            const biayaHalaman = tambahanHalaman * sisi * 1500;
            hargaSatuProdukFull += biayaHalaman;

        } else if (item.tipe_kalkulasi === 'cetak_meteran') {
            let luas = 1;
            if (item.atribut_custom_snapshot && typeof item.atribut_custom_snapshot === 'object') {
                luas = parseFloat(item.atribut_custom_snapshot['Luas Dihargai (m2)']) || 1;
            }
            if (luas < 1) luas = 1;
            hargaSatuProdukFull = hargaSatuProdukFull * luas;
        }

        const totalHargaProduk = hargaSatuProdukFull * qty;

        // Kalkulasi Biaya Finishing
        let totalFinishing = 0;
        const listFinishing = item.pesanan_item_finishing || item.finishing || [];
        listFinishing.forEach(f => {
            let biayaPerFinishing = f.tipe === 'persen'
                ? hargaSatuProdukFull * (Number(f.harga_finishing_snapshot) / 100)
                : (Number(f.harga_finishing_snapshot) || 0);

            if (f.kali_jumlah_pesan) biayaPerFinishing = biayaPerFinishing * qty;
            totalFinishing += biayaPerFinishing;
        });

        const newTotalProduk = totalHargaProduk + totalFinishing;
        item.total_sla = Number(item.harga_pengerjaan_snapshot) || 0;
        item.total_produk = newTotalProduk;
        item.subtotal = newTotalProduk + item.total_sla;

        return item;
    });

    cartItems.value = [...cartItems.value];

    if (hasUpdates) {
        alertStore.show('Harga disesuaikan dengan Diskon Pelanggan.', 'info');
    }
};

watch(() => form.id_customer, (newId, oldId) => {
    const selectedCust = props.customers.find(c => c.id_customer === newId);
    localStorage.setItem('pos_active_customer', JSON.stringify(selectedCust || null));

    if (oldId !== undefined && newId !== oldId) {
        const list = alamatOptions.value;
        if (list.length > 0) {
            form.id_alamat = list[0].id_alamat;
        } else {
            form.id_alamat = '';
        }
        recalculateCartItems(selectedCust);
    }
}, { immediate: true });

// ==========================================
// 3. LOGIKA PENGIRIMAN & ONGKIR
// ==========================================
const ekspedisiOptions = [
    { id: 'Belum Termasuk Biaya Kirim', nama: 'Belum Termasuk Biaya Kirim (Rp 0)' }, // Default Penawaran
    { id: 'Ambil di Toko', nama: 'Ambil di Toko (Rp 0)' },
    { id: 'Kurir Toko', nama: 'Kurir Lokal / Instan' },
    { id: 'jne', nama: 'JNE (Jalur Nugraha Ekakurir)' },
    { id: 'pos', nama: 'POS Indonesia' },
    { id: 'tiki', nama: 'TIKI' },
    { id: 'sicepat', nama: 'SiCepat Ekspres' },
    { id: 'jnt', nama: 'J&T Express' },
    { id: 'ninja', nama: 'Ninja Xpress' },
    { id: 'anteraja', nama: 'AnterAja' },
    { id: 'lion', nama: 'Lion Parcel' },
    { id: 'wahana', nama: 'Wahana Prestasi Logistik' },
    { id: 'rpx', nama: 'RPX Holding' },
    { id: 'sap', nama: 'SAP Express' },
    { id: 'ide', nama: 'ID Express' },
    { id: 'ncs', nama: 'NCS Express' },
    { id: 'rex', nama: 'REX Express' },
    { id: 'sentral', nama: 'Sentral Cargo' },
    { id: 'indah', nama: 'Indah Logistik' }
];

const manualLayananOptions = [
    { id: 'Gojek / Grab (Instan)', nama: 'Gojek / Grab (Instan)' },
    { id: 'Lalamove / Deliveree', nama: 'Lalamove / Deliveree' },
    { id: 'Kurir Toko (Motor)', nama: 'Kurir Toko (Motor)' },
    { id: 'Kurir Toko (Mobil)', nama: 'Kurir Toko (Mobil)' },
    { id: 'Titip Travel', nama: 'Titip Travel' },
    { id: 'Lainnya', nama: 'Lainnya' },
];

const layananOptions = ref([]);
const isLoadingOngkir = ref(false);

const isManualEkspedisi = computed(() => ['Belum Termasuk Biaya Kirim', 'Ambil di Toko', 'Kurir Toko'].includes(form.ekspedisi_nama));

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka || 0);
};

const fetchOngkir = async () => {
    if (!form.id_alamat || cartItems.value.length === 0 || isManualEkspedisi.value) return;

    isLoadingOngkir.value = true;
    layananOptions.value = [];
    form.ekspedisi_layanan = '';
    form.harga_ongkir = 0;

    try {
        const response = await axios.post('/ongkir/calculate', {
            id_alamat: form.id_alamat,
            courier: form.ekspedisi_nama,
            items: cartItems.value
        });

        const data = response.data;
        let costs = [];

        if (data?.data && Array.isArray(data.data)) {
            costs = data.data.map(i => ({ id: i.service, nama: `${i.service} (${i.etd || '-'} Hari) - Rp ${formatRupiah(i.cost)}`, cost: i.cost }));
        } else if (data?.rajaongkir?.results?.[0]?.costs) {
            costs = data.rajaongkir.results[0].costs.map(s => ({ id: s.service, nama: `${s.service} (${s.cost[0]?.etd || '-'} Hari) - Rp ${formatRupiah(s.cost[0]?.value)}`, cost: s.cost[0]?.value }));
        }

        if (costs.length > 0) {
            layananOptions.value = costs;
            alertStore.show('Tarif Ongkir berhasil dihitung!', 'success');
        } else {
            alertStore.show('Layanan kurir tidak tersedia untuk rute tersebut.', 'error');
            form.ekspedisi_nama = 'Belum Termasuk Biaya Kirim';
        }
    } catch (error) {
        alertStore.show('Gagal menghubungi server ongkir.', 'error');
        form.ekspedisi_nama = 'Belum Termasuk Biaya Kirim';
    } finally {
        isLoadingOngkir.value = false;
    }
};

watch(() => form.id_alamat, (newAlamat, oldAlamat) => {
    if (oldAlamat !== undefined && newAlamat !== oldAlamat) {
        if (cartItems.value.length > 0 && !isManualEkspedisi.value) {
            fetchOngkir();
        }
    }
});

watch(() => form.ekspedisi_nama, async (newCourier) => {
    if (newCourier === 'Belum Termasuk Biaya Kirim' || newCourier === 'Ambil di Toko') {
        layananOptions.value = [];
        form.ekspedisi_layanan = newCourier === 'Ambil di Toko' ? 'Ambil Sendiri' : '';
        form.harga_ongkir = 0;
    } else if (newCourier === 'Kurir Toko') {
        layananOptions.value = [];
        form.ekspedisi_layanan = '';
        form.harga_ongkir = 0;
    } else {
        if (!form.id_alamat) {
            alertStore.show('Pilih Alamat Instansi terlebih dahulu!', 'warning');
            form.ekspedisi_nama = 'Belum Termasuk Biaya Kirim';
            return;
        }
        if (cartItems.value.length === 0) {
            alertStore.show('Daftar barang masih kosong!', 'warning');
            form.ekspedisi_nama = 'Belum Termasuk Biaya Kirim';
            return;
        }
        await fetchOngkir();
    }
});

watch(() => form.ekspedisi_layanan, (newLayanan) => {
    if (!isManualEkspedisi.value && newLayanan) {
        const selected = layananOptions.value.find(l => l.id === newLayanan);
        if (selected) form.harga_ongkir = selected.cost;
    }
});


// ==========================================
// 4. MANAJEMEN KERANJANG & FORM ITEM
// ==========================================
const showOrderForm = ref(false);
const itemToEdit = ref(null);

const handleRequestAdd = () => { itemToEdit.value = null; showOrderForm.value = true; };
const handleRequestEdit = (item) => { itemToEdit.value = { ...item }; showOrderForm.value = true; };
const handleCancelForm = () => { itemToEdit.value = null; showOrderForm.value = false; };

const handleFormSubmit = (payload) => {
    const cartId = itemToEdit.value ? itemToEdit.value.cart_id : 'cart_' + Date.now();

    const dataToSave = {
        ...payload,
        cart_id: cartId,
        pesanan_item_finishing: payload.finishing,
        estimasi_pengerjaan_snapshot: payload.estimasi_pengerjaan
    };

    if (itemToEdit.value) {
        const idx = cartItems.value.findIndex(c => c.cart_id === itemToEdit.value.cart_id);
        if (idx !== -1) cartItems.value[idx] = dataToSave;
    } else {
        cartItems.value.push(dataToSave);
    }

    handleCancelForm();
    alertStore.show('Item tersimpan di draf penawaran!', 'success');
};

const hapusItem = (cartId) => {
    cartItems.value = cartItems.value.filter(c => c.cart_id !== cartId);
    alertStore.show('Item dihapus', 'info');
};

const resetPenawaran = () => {
    cartItems.value = [];
    localStorage.removeItem('pos_active_customer');
    form.reset();
};

// ==========================================
// 5. KALKULASI TOTAL AKHIR
// ==========================================
const totalProduk = computed(() => {
    return cartItems.value.reduce((total, item) => total + (Number(item.subtotal) || 0), 0);
});

// Grand total sekarang juga menjumlahkan harga ongkir kalau ada
const grandTotal = computed(() => totalProduk.value + Number(form.harga_ongkir));

// ==========================================
// 6. SUBMIT PENAWARAN KE LARAVEL
// ==========================================
const submitPenawaran = async () => {
    if (cartItems.value.length === 0) return alertStore.show('Belum ada item yang ditawarkan!', 'error');
    if (!form.id_customer) return alertStore.show('Pilih pelanggan dulu!', 'error');

    const formData = new FormData();
    formData.append('id_customer', form.id_customer);
    formData.append('id_alamat', form.id_alamat || '');
    formData.append('berlaku_sampai', form.berlaku_sampai || '');
    formData.append('catatan_penawaran', form.catatan_penawaran || '');

    // Kirim data ekspedisi
    const namaEkspedisiAsli = ekspedisiOptions.find(e => e.id === form.ekspedisi_nama)?.nama || form.ekspedisi_nama;
    formData.append('ekspedisi_nama', isManualEkspedisi.value ? form.ekspedisi_nama : namaEkspedisiAsli.toUpperCase());
    formData.append('ekspedisi_layanan', form.ekspedisi_nama === 'Ambil di Toko' ? 'Ambil Sendiri' : form.ekspedisi_layanan);
    formData.append('harga_ongkir', form.ekspedisi_nama === 'Ambil di Toko' || form.ekspedisi_nama === 'Belum Termasuk Biaya Kirim' ? 0 : form.harga_ongkir);

    for (let index = 0; index < cartItems.value.length; index++) {
        const item = cartItems.value[index];
        formData.append(`items[${index}][id_sku]`, item.id_sku);
        formData.append(`items[${index}][jumlah]`, Number(item.jumlah) || 1);
        formData.append(`items[${index}][nama_produk_snapshot]`, item.nama_produk_snapshot);
        formData.append(`items[${index}][harga_satuan_snapshot]`, Number(item.harga_satuan_snapshot) || 0);
        formData.append(`items[${index}][estimasi_pengerjaan]`, item.estimasi_pengerjaan_snapshot || 'Reguler');
        formData.append(`items[${index}][harga_pengerjaan_snapshot]`, Number(item.harga_pengerjaan_snapshot) || 0);
        formData.append(`items[${index}][catatan]`, item.catatan || '');

        const hargaDasarAwal = Number(item.harga_dasar_awal_snapshot) || Number(item.harga_satuan_snapshot) || 0;
        formData.append(`items[${index}][harga_dasar_awal_snapshot]`, hargaDasarAwal);
        formData.append(`items[${index}][total_diskon_snapshot]`, Number(item.total_diskon_snapshot) || 0);

        formData.append(`items[${index}][rincian_diskon_snapshot]`, JSON.stringify(item.rincian_diskon_snapshot || []));
        formData.append(`items[${index}][finishing]`, JSON.stringify(item.pesanan_item_finishing || []));

        if (item.atribut_custom_snapshot && Object.keys(item.atribut_custom_snapshot).length > 0) {
            formData.append(`items[${index}][atribut_custom_snapshot]`, JSON.stringify(item.atribut_custom_snapshot));
        }
    }

    router.post(route('penawaran.store'), formData, {
        forceFormData: true,
        onSuccess: () => {
            alertStore.show('Surat Penawaran Berhasil Dibuat!', 'success');
            resetPenawaran();
        },
        onError: (errors) => {
            console.error("Detail Error:", errors);
            const firstError = Object.values(errors)[0];
            alertStore.show(firstError || 'Gagal menyimpan penawaran!', 'error');
        }
    });
};
</script>

<template>
    <Head title="Buat Surat Penawaran" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('penawaran.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <h2 class="flex items-center gap-2 text-xl font-bold leading-tight text-base-content">
                        Buat Surat Penawaran (Quotation)
                    </h2>
                </div>
                <button v-if="cartItems.length > 0" @click="resetPenawaran" class="btn btn-sm btn-error btn-outline rounded-xl text-[10px] font-black uppercase tracking-widest">
                    ✕ Reset
                </button>
            </div>
        </template>

        <div class="px-4 py-6 mx-auto max-w-350">
            <div class="grid items-start grid-cols-1 gap-6 lg:grid-cols-12">

                <!-- KOLOM KIRI: FORM & TABEL -->
                <div class="space-y-6 lg:col-span-8 xl:col-span-9">

                    <!-- CARD: INFORMASI PEMESAN & PENGIRIMAN -->
                    <div class="p-6 border shadow-sm bg-base-100 border-base-200/80 rounded-3xl">
                        <div class="flex items-center gap-2 pb-3 mb-4 border-b border-base-200/50">
                            <h3 class="text-[10px] font-black tracking-widest uppercase opacity-50">Informasi Pemesan & Pengiriman</h3>
                        </div>

                        <!-- GRID PELANGGAN -->
                        <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
                            <CustomSelectSearch
                                v-model="form.id_customer"
                                label="Pilih Pelanggan / Instansi"
                                :options="customerOptions"
                                valueKey="id_customer" labelKey="nama_tampilan"
                                placeholder="Ketik Nama Pelanggan..."
                            />

                            <CustomSelect
                                v-model="form.id_alamat"
                                label="Alamat Instansi (Opsional)"
                                :options="alamatOptions"
                                valueKey="id_alamat" labelKey="alamat_lengkap"
                                placeholder="-- Bisa dikosongkan --"
                                :disabled="!form.id_customer || alamatOptions.length === 0"
                            />
                        </div>

                        <!-- 👇 GRID KURIR & ONGKIR 👇 -->
                        <div class="grid grid-cols-1 gap-6 pt-6 border-t border-dashed md:grid-cols-12 border-base-200/80">
                            <div class="md:col-span-4">
                                <CustomSelect v-model="form.ekspedisi_nama" label="Kurir / Ekspedisi" :options="ekspedisiOptions" valueKey="id" labelKey="nama" />
                            </div>

                            <div class="md:col-span-4" v-if="form.ekspedisi_nama !== 'Ambil di Toko' && form.ekspedisi_nama !== 'Belum Termasuk Biaya Kirim'">
                                <template v-if="isManualEkspedisi">
                                    <CustomSelect v-model="form.ekspedisi_layanan" label="Layanan Lokal" :options="manualLayananOptions" valueKey="id" labelKey="nama" placeholder="Pilih Instan/Lokal..." />
                                </template>
                                <template v-else>
                                    <div v-if="isLoadingOngkir" class="flex flex-col gap-1">
                                        <label class="text-[10px] font-bold uppercase opacity-70 ml-1 block">Layanan Ongkir</label>
                                        <div class="flex items-center gap-2 px-3 text-xs font-bold border h-11 border-base-300 bg-base-200/50 rounded-xl text-primary animate-pulse">
                                            <span class="loading loading-spinner loading-xs"></span> Mengambil Tarif...
                                        </div>
                                    </div>
                                    <CustomSelect v-else v-model="form.ekspedisi_layanan" label="Layanan Ongkir" :options="layananOptions" valueKey="id" labelKey="nama" placeholder="Pilih Layanan Ekspedisi..." />
                                </template>
                            </div>

                            <div class="md:col-span-4" v-if="form.ekspedisi_nama !== 'Ambil di Toko' && form.ekspedisi_nama !== 'Belum Termasuk Biaya Kirim'">
                                <CustomInputNumber v-model="form.harga_ongkir" label="Biaya Ongkir (Rp)" placeholder="Rp 0" :readonly="!isManualEkspedisi" />
                            </div>
                        </div>
                        <!-- 👆 AKHIR GRID KURIR 👆 -->
                    </div>

                    <!-- FORM TAMBAH ITEM (REUSABLE DARI PESAN) -->
                    <div v-show="showOrderForm" class="transition-all duration-300">
                        <OrderFormCard
                            v-if="showOrderForm"
                            :isPosMode="true"
                            :editData="itemToEdit"
                            @cancel="handleCancelForm"
                            @submit="handleFormSubmit"
                        />
                    </div>

                    <!-- TABEL KERANJANG BARANG -->
                    <OrderItemsTable
                        :items="cartItems"
                        @requestEdit="handleRequestEdit"
                        @deleteItem="hapusItem"
                        @addItem="handleRequestAdd"
                    />

                    <!-- CATATAN & MASA BERLAKU PENAWARAN -->
                    <div class="p-6 border shadow-sm bg-base-100 border-base-200/80 rounded-3xl">
                        <div class="flex items-center gap-2 pb-3 mb-4 border-b border-base-200/50">
                            <h3 class="text-[10px] font-black tracking-widest uppercase opacity-50">Pengaturan Dokumen Penawaran</h3>
                        </div>

                        <div class="grid items-start grid-cols-1 gap-6 md:grid-cols-12">
                            <div class="md:col-span-4">
                                <CustomInput
                                    v-model="form.berlaku_sampai"
                                    type="date"
                                    label="Masa Berlaku (Valid Until)"
                                    :error="form.errors?.berlaku_sampai"
                                />
                                <span class="block mt-1 ml-1 text-xs opacity-50 label-text-alt">Batas akhir harga mengikat.</span>
                            </div>
                            <div class="md:col-span-8">
                                <CustomTextarea
                                    v-model="form.catatan_penawaran"
                                    label="Syarat & Ketentuan (Catatan)"
                                    :rows="5"
                                    placeholder="Catatan tambahan di dokumen PDF..."
                                    :error="form.errors?.catatan_penawaran"
                                />
                            </div>
                        </div>
                    </div>

                </div>

                <!-- KOLOM KANAN: SUMMARY & SIMPAN -->
                <div class="lg:col-span-4 xl:col-span-3">
                    <div class="sticky space-y-6 top-24">

                        <!-- Ringkasan Tagihan (Reusable Component) -->
                        <OrderSummary
                            :total_tagihan="totalProduk"
                            :harga_ongkir="form.ekspedisi_nama === 'Belum Termasuk Biaya Kirim' || form.ekspedisi_nama === 'Ambil di Toko' ? 0 : form.harga_ongkir"
                            :diskon_voucher_nominal="0"
                            :kode_unik="0"
                            :total_transfer="grandTotal"
                            :total_dibayar="0"
                            :sisa_tagihan="grandTotal"
                        />

                        <!-- Tombol Eksekusi -->
                        <CustomButton
                            variant="primary"
                            class="w-full text-sm font-black tracking-widest uppercase shadow-xl h-14 rounded-2xl shadow-primary/20"
                            @click="submitPenawaran"
                            :disabled="form.processing || cartItems.length === 0"
                        >
                            <span v-if="form.processing" class="loading loading-spinner loading-md"></span>
                            <span v-else>Simpan Surat Penawaran</span>
                        </CustomButton>
                    </div>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
