<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomSelect from '@/Components/CustomSelect.vue';
import CustomSelectSearch from '@/Components/CustomSelectSearch.vue'; // <-- IMPORT BARU
import ProdukRow from '@/Components/ProdukRow.vue';
import { alertStore } from '@/Utils/alertStore';
import axios from 'axios';
import CustomInputNumber from '@/Components/CustomInputNumber.vue';

const initDB = () => new Promise((resolve, reject) => {
    const req = indexedDB.open('POS_DB', 1);
    req.onupgradeneeded = () => req.result.createObjectStore('cart_files');
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
});

const getFilesFromDB = async (id) => {
    const db = await initDB();
    return new Promise((resolve) => {
        const tx = db.transaction('cart_files', 'readonly');
        const req = tx.objectStore('cart_files').get(id);
        req.onsuccess = () => resolve(req.result || []);
    });
};

const deleteFilesFromDB = async (id) => {
    const db = await initDB();
    return new Promise((resolve) => {
        const tx = db.transaction('cart_files', 'readwrite');
        tx.objectStore('cart_files').delete(id);
        tx.oncomplete = resolve;
    });
};

const clearAllFilesDB = async () => {
    const db = await initDB();
    return new Promise((resolve) => {
        const tx = db.transaction('cart_files', 'readwrite');
        tx.objectStore('cart_files').clear();
        tx.oncomplete = resolve;
    });
};

const props = defineProps({
    kategoris: Array,
    produks: Array,
    customers: Array,
    vouchers: Array,
});

const filterKategori = ref('semua');
const cartItems = ref([]);

const pembayaranOptions = [
    { id: 'belum_lunas', nama: 'Belum Lunas' },
    { id: 'dibayar_sebagian', nama: 'Dibayar Sebagian (DP)' },
    { id: 'lunas', nama: 'Lunas (Bayar Penuh)' },
];

const ekspedisiOptions = [
    { id: 'Ambil di Toko', nama: 'Ambil di Toko (Ongkir Rp 0)' },
    { id: 'Kurir Toko', nama: 'Kurir Toko / Lokal / Instan' },
    { id: 'jne', nama: 'JNE' },
    { id: 'sicepat', nama: 'SiCepat' },
    { id: 'jnt', nama: 'J&T' },
    { id: 'pos', nama: 'POS Indonesia' },
];

const manualLayananOptions = [
    { id: 'Gojek / Grab (Instan)', nama: 'Gojek / Grab (Instan)' },
    { id: 'Lalamove / Deliveree', nama: 'Lalamove / Deliveree' },
    { id: 'Kurir Toko (Motor)', nama: 'Kurir Toko (Motor)' },
    { id: 'Kurir Toko (Mobil)', nama: 'Kurir Toko (Mobil)' },
    { id: 'Titip Travel / Bus', nama: 'Titip Travel / Bus' },
    { id: 'Lainnya', nama: 'Lainnya' },
];

const savedState = JSON.parse(localStorage.getItem('pos_form_state')) || {};

const form = useForm({
    id_customer: savedState.id_customer || '',
    id_alamat: savedState.id_alamat || '',
    status_pembayaran: savedState.status_pembayaran || 'belum_lunas',
    nominal_bayar: savedState.nominal_bayar || 0,
    kode_voucher: savedState.kode_voucher || '',
    diskon_voucher_nominal: savedState.diskon_voucher_nominal || 0,
    ekspedisi_nama: savedState.ekspedisi_nama || 'Ambil di Toko',
    ekspedisi_layanan: savedState.ekspedisi_layanan || '',
    harga_ongkir: savedState.harga_ongkir || 0,
    items: [],
});

watch(() => form, (newForm) => {
    localStorage.setItem('pos_form_state', JSON.stringify({
        id_customer: newForm.id_customer,
        id_alamat: newForm.id_alamat,
        status_pembayaran: newForm.status_pembayaran,
        nominal_bayar: newForm.nominal_bayar,
        kode_voucher: newForm.kode_voucher,
        diskon_voucher_nominal: newForm.diskon_voucher_nominal,
        ekspedisi_nama: newForm.ekspedisi_nama,
        ekspedisi_layanan: newForm.ekspedisi_layanan,
        harga_ongkir: newForm.harga_ongkir,
    }));
}, { deep: true });

const customerOptions = computed(() => {
    return props.customers.map(c => ({
        id_customer: c.id_customer,
        nama_tampilan: `${c.user?.name || 'Walk-In'} (${c.id_customer})`
    }));
});

const voucherOptions = computed(() => {
    if (!props.vouchers) return [];
    return props.vouchers.map(v => ({
        value: v.kode_voucher,
        label: `${v.kode_voucher} - Diskon ${v.persentase_diskon}%`
    }));
});

const alamatOptions = computed(() => {
    if (!form.id_customer) return [];
    const selectedCust = props.customers.find(c => c.id_customer === form.id_customer);
    if (!selectedCust || !selectedCust.alamat) return [];

    return selectedCust.alamat.map(a => ({
        id_alamat: a.id_alamat,
        alamat_lengkap: a.alamat_lengkap || a.alamat || a.id_alamat
    }));
});

watch(() => form.id_customer, (newCustomerId, oldCustomerId) => {
    if (newCustomerId) {
        const selectedCust = props.customers.find(c => c.id_customer === newCustomerId);
        if (selectedCust) {
            localStorage.setItem('pos_active_customer', JSON.stringify(selectedCust));
        }

        if (oldCustomerId !== undefined && oldCustomerId !== newCustomerId) {
            const listAlamat = alamatOptions.value;
            if (listAlamat.length > 0) {
                form.id_alamat = listAlamat[0].id_alamat;
            } else {
                form.id_alamat = '';
                alertStore.show('Customer ini belum mendaftarkan alamat pengiriman!', 'info');
            }
        }
    } else {
        localStorage.removeItem('pos_active_customer');
        form.id_alamat = '';
    }
});

const layananOptions = ref([]);
const isLoadingOngkir = ref(false);

const isManualEkspedisi = computed(() => {
    return ['Ambil di Toko', 'Kurir Toko'].includes(form.ekspedisi_nama);
});

const fetchOngkir = async () => {
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
            costs = data.data.map(item => ({
                id: item.service,
                nama: `${item.service} (${item.etd || item.estimation || '-'} Hari) - Rp ${formatRupiah(item.cost)}`,
                cost: item.cost
            }));
        } else if (data?.rajaongkir?.results?.[0]?.costs) {
            costs = data.rajaongkir.results[0].costs.map(srv => ({
                id: srv.service,
                nama: `${srv.service} (${srv.cost[0]?.etd || '-'} Hari) - Rp ${formatRupiah(srv.cost[0]?.value)}`,
                cost: srv.cost[0]?.value
            }));
        }

        if (costs.length > 0) {
            layananOptions.value = costs;
        } else {
            alertStore.show('Layanan kurir ini tidak tersedia untuk rute tersebut.', 'error');
            form.ekspedisi_nama = 'Kurir Toko';
        }
    } catch (error) {
        console.error(error);
        alertStore.show('Gagal menghubungi server ongkir.', 'error');
        form.ekspedisi_nama = 'Kurir Toko';
    } finally {
        isLoadingOngkir.value = false;
    }
};

watch(() => form.ekspedisi_nama, async (newCourier) => {
    if (newCourier === 'Ambil di Toko') {
        layananOptions.value = [];
        form.ekspedisi_layanan = 'Ambil Sendiri';
        form.harga_ongkir = 0;
    } else if (newCourier === 'Kurir Toko') {
        layananOptions.value = [];
        form.ekspedisi_layanan = '';
        form.harga_ongkir = 0;
    } else {
        if (!form.id_alamat) {
            alertStore.show('Pilih Alamat Pengiriman terlebih dahulu!', 'warning');
            form.ekspedisi_nama = 'Kurir Toko';
            return;
        }
        if (cartItems.value.length === 0) {
            alertStore.show('Keranjang belanja kosong!', 'warning');
            form.ekspedisi_nama = 'Kurir Toko';
            return;
        }
        await fetchOngkir();
    }
});

watch(() => form.ekspedisi_layanan, (newLayanan) => {
    if (!isManualEkspedisi.value && newLayanan) {
        const selected = layananOptions.value.find(l => l.id === newLayanan);
        if (selected) {
            form.harga_ongkir = selected.cost;
        }
    }
});

const loadCart = () => {
    cartItems.value = JSON.parse(localStorage.getItem('pos_cart')) || [];
};

onMounted(() => loadCart());

const hapusItem = async (index) => {
    const itemId = cartItems.value[index].cart_item_id;
    cartItems.value.splice(index, 1);
    localStorage.setItem('pos_cart', JSON.stringify(cartItems.value));

    await deleteFilesFromDB(itemId);

    alertStore.show('Item berhasil dihapus', 'success');
};

const resetFormDanKeranjang = async () => {
    cartItems.value = [];
    localStorage.removeItem('pos_cart');
    localStorage.removeItem('pos_form_state');
    localStorage.removeItem('pos_active_customer');
    await clearAllFilesDB();
    form.reset();
    form.id_customer = '';
    form.id_alamat = '';
    form.kode_voucher = '';
    form.diskon_voucher_nominal = 0;
    form.ekspedisi_nama = 'Ambil di Toko';
    form.ekspedisi_layanan = 'Ambil Sendiri';
    form.harga_ongkir = 0;
    form.status_pembayaran = 'belum_lunas';
    form.nominal_bayar = 0;
};

const kosongkanKeranjang = () => {
    if (confirm('Yakin ingin membersihkan semua isi keranjang belanja dan form pelanggan?')) {
        resetFormDanKeranjang();
        alertStore.show('Sesi kasir dibersihkan', 'success');
    }
};

const hitungTotalFinishing = (item) => {
    return (item.finishings || []).reduce((sum, f) => sum + (Number(f.harga_finishing_snapshot) || 0), 0);
};

const hitungHargaDasar = (item) => {
    return Number(item.harga_satuan_snapshot) - hitungTotalFinishing(item);
};

const hitungTotalItem = (item) => {
    const qty = Number(item.jumlah) || 1;
    const sla = Number(item.harga_pengerjaan_snapshot) || 0;
    const hargaIncludeFinishing = Number(item.harga_satuan_snapshot) || 0;
    return (hargaIncludeFinishing * qty) + sla;
};

watch([() => form.kode_voucher, cartItems], ([newKode, newItems]) => {
    if (!newKode) {
        form.diskon_voucher_nominal = 0;
        return;
    }

    const voucher = props.vouchers?.find(v => v.kode_voucher === newKode);

    if (!voucher) {
        form.diskon_voucher_nominal = 0;
        return;
    }

    const subtotal = newItems.reduce((total, item) => total + hitungTotalItem(item), 0);

    if (subtotal < Number(voucher.minimal_transaksi_rupiah)) {
        form.diskon_voucher_nominal = 0;
        alertStore.show(`Minimal transaksi untuk voucher ini adalah Rp ${formatRupiah(voucher.minimal_transaksi_rupiah)}`, 'warning');
        return;
    }

    let kalkulasiDiskon = 0;
    const persen = Number(voucher.persentase_diskon);

    if (voucher.tipe_target === 'semua_pesanan') {
        kalkulasiDiskon = (subtotal * persen) / 100;
    } else if (voucher.tipe_target === 'produk_tertentu') {
        const totalProdukTarget = newItems
            .filter(item => item.id_sku === voucher.id_sku_target)
            .reduce((sum, item) => sum + hitungTotalItem(item), 0);

        kalkulasiDiskon = (totalProdukTarget * persen) / 100;
    }

    const maksPotongan = Number(voucher.maksimal_potongan_rupiah);
    if (maksPotongan > 0 && kalkulasiDiskon > maksPotongan) {
        kalkulasiDiskon = maksPotongan;
    }

    form.diskon_voucher_nominal = Math.round(kalkulasiDiskon);
}, { deep: true });

const grandTotal = computed(() => {
    const totalProduk = cartItems.value.reduce((total, item) => total + hitungTotalItem(item), 0);
    const ongkir = Number(form.harga_ongkir) || 0;
    const diskonVoucher = Number(form.diskon_voucher_nominal) || 0;

    const totalAkhir = (totalProduk + ongkir) - diskonVoucher;
    return totalAkhir > 0 ? totalAkhir : 0;
});

watch([() => form.status_pembayaran, grandTotal], ([newStatus, newTotal]) => {
    if (newStatus === 'lunas') {
        form.nominal_bayar = newTotal;
    } else if (newStatus === 'belum_lunas') {
        form.nominal_bayar = 0;
    }
});

const groupedProduks = computed(() => {
    const groups = {};
    props.produks.forEach(prod => {
        const idKat = prod.id_kategori;
        const kat = props.kategoris.find(k => k.id_kategori === idKat);
        const namaKat = kat ? kat.nama_kategori : 'Lainnya';

        if (!groups[idKat]) {
            groups[idKat] = { id_kategori: idKat, nama_kategori: namaKat, data: [] };
        }
        groups[idKat].data.push(prod);
    });
    return Object.values(groups);
});

const filteredGroupedProduks = computed(() => {
    if (filterKategori.value === 'semua') return groupedProduks.value;
    return groupedProduks.value.filter(g => g.id_kategori === filterKategori.value);
});

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka || 0);
};

const submitCheckout = async () => {
    if (cartItems.value.length === 0) return alertStore.show('Keranjang kosong!', 'error');
    if (!form.id_customer) return alertStore.show('Pilih pelanggan dulu!', 'error');
    if (!form.id_alamat) return alertStore.show('Pilih alamat dulu!', 'error');
    if (form.ekspedisi_nama !== 'Ambil di Toko' && !form.ekspedisi_layanan) return alertStore.show('Layanan Ekspedisi wajib dipilih!', 'error');

    const formData = new FormData();
    formData.append('id_customer', form.id_customer);
    formData.append('id_alamat', form.id_alamat);
    formData.append('status_pembayaran', form.status_pembayaran);
    formData.append('nominal_bayar', form.nominal_bayar || 0);

    formData.append('kode_voucher', form.kode_voucher || '');
    formData.append('diskon_voucher_nominal', form.diskon_voucher_nominal || 0);

    const namaEkspedisiAsli = ekspedisiOptions.find(e => e.id === form.ekspedisi_nama)?.nama || form.ekspedisi_nama;
    formData.append('ekspedisi_nama', isManualEkspedisi.value ? form.ekspedisi_nama : namaEkspedisiAsli.toUpperCase());
    formData.append('ekspedisi_layanan', form.ekspedisi_nama === 'Ambil di Toko' ? 'Ambil Sendiri' : form.ekspedisi_layanan);
    formData.append('harga_ongkir', form.ekspedisi_nama === 'Ambil di Toko' ? 0 : form.harga_ongkir);

    for (let index = 0; index < cartItems.value.length; index++) {
        const item = cartItems.value[index];
        formData.append(`items[${index}][id_sku]`, item.id_sku);
        formData.append(`items[${index}][jumlah]`, item.jumlah);
        formData.append(`items[${index}][nama_produk_snapshot]`, item.nama_produk_snapshot);
        formData.append(`items[${index}][harga_satuan_snapshot]`, hitungHargaDasar(item));
        formData.append(`items[${index}][estimasi_pengerjaan]`, item.estimasi_pengerjaan || 'Reguler');
        formData.append(`items[${index}][harga_pengerjaan_snapshot]`, item.harga_pengerjaan_snapshot || 0);
        formData.append(`items[${index}][catatan]`, item.catatan || '');

        formData.append(`items[${index}][harga_dasar_awal_snapshot]`, item.harga_dasar_awal_snapshot || item.harga_satuan_snapshot);
        formData.append(`items[${index}][total_diskon_snapshot]`, item.total_diskon_snapshot || 0);

        const rincianData = Array.isArray(item.rincian_diskon_snapshot) ? item.rincian_diskon_snapshot : [];
        formData.append(`items[${index}][rincian_diskon_snapshot]`, JSON.stringify(rincianData));

        const filesToUpload = await getFilesFromDB(item.cart_item_id);
        if (filesToUpload && filesToUpload.length > 0) {
            filesToUpload.forEach((file) => {
                formData.append(`items[${index}][file_desain][]`, file);
            });
        }

        const finishingData = Array.isArray(item.finishings) ? item.finishings : [];
        formData.append(`items[${index}][finishing]`, JSON.stringify(finishingData));
    }

    router.post(route('pesan.store'), formData, {
        forceFormData: true,
        onSuccess: () => {
            alertStore.show('Transaksi Berhasil!', 'success');
            resetFormDanKeranjang();
        },
        onError: (errors) => {
            console.error('Error dari Backend:', errors);
            const errorList = Object.values(errors);
            const firstError = errorList.length > 0 ? errorList[0] : 'Gagal checkout, periksa log backend!';
            alertStore.show(firstError, 'error');
        }
    });
};
</script>

<template>
    <Head title="POS Kasir Utama" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Sistem Kasir (Point of Sale)
            </h2>
        </template>

        <div class="px-4 py-6 mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 border shadow-sm rounded-3xl bg-base-100 border-base-300">
                <h3 class="pb-2 mb-4 text-xs font-black tracking-widest uppercase border-b opacity-50">Informasi Nota Transaksi</h3>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <CustomSelectSearch
                            v-model="form.id_customer"
                            :options="customerOptions"
                            labelKey="nama_tampilan"
                            valueKey="id_customer"
                            label="PELANGGAN / CUSTOMER"
                            placeholder="Ketik Nama Pelanggan..."
                        />
                    </div>

                    <div>
                        <label class="text-[10px] font-bold uppercase opacity-70 ml-1 block mb-1">Alamat Pengiriman</label>
                        <CustomSelect v-model="form.id_alamat" :options="alamatOptions" labelKey="alamat_lengkap" valueKey="id_alamat" :placeholder="form.id_customer ? 'Pilih Alamat...' : 'Pilih customer dulu'" :disabled="!form.id_customer || alamatOptions.length === 0" />
                    </div>

                    <div>
                        <label class="text-[10px] font-bold uppercase opacity-70 ml-1 block mb-1">Status Pembayaran</label>
                        <CustomSelect v-model="form.status_pembayaran" :options="pembayaranOptions" labelKey="nama" valueKey="id" />
                    </div>

                    <div v-if="form.status_pembayaran !== 'belum_lunas'">
                        <label class="text-[10px] font-bold uppercase opacity-70 ml-1 block mb-1">Nominal Uang Masuk</label>
                        <CustomInputNumber v-model="form.nominal_bayar" placeholder="Rp 0" :readonly="form.status_pembayaran === 'lunas'" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 pt-6 mt-6 border-t md:grid-cols-3 border-base-200">
                    <div>
                        <label class="text-[10px] font-bold uppercase opacity-70 ml-1 block mb-1">Kurir / Ekspedisi</label>
                        <CustomSelect v-model="form.ekspedisi_nama" :options="ekspedisiOptions" labelKey="nama" valueKey="id" />
                    </div>

                    <div v-if="form.ekspedisi_nama !== 'Ambil di Toko'">
                        <label class="text-[10px] font-bold uppercase opacity-70 ml-1 block mb-1">Layanan Pengiriman</label>
                        <CustomSelect
                            v-if="isManualEkspedisi"
                            v-model="form.ekspedisi_layanan"
                            :options="manualLayananOptions"
                            labelKey="nama" valueKey="id"
                            placeholder="Pilih Layanan Lokal/Instan..."
                        />
                        <div v-else>
                            <div v-if="isLoadingOngkir" class="flex items-center gap-2 px-2 py-3 text-xs font-bold text-primary">
                                <span class="loading loading-spinner loading-xs"></span> Mengambil Tarif...
                            </div>
                            <CustomSelect
                                v-else
                                v-model="form.ekspedisi_layanan"
                                :options="layananOptions"
                                labelKey="nama" valueKey="id"
                                placeholder="Pilih Layanan Ekspedisi..."
                            />
                        </div>
                    </div>

                    <div v-if="form.ekspedisi_nama !== 'Ambil di Toko'">
                        <label class="text-[10px] font-bold uppercase opacity-70 ml-1 block mb-1">Biaya Ongkos Kirim</label>
                        <CustomInputNumber v-model="form.harga_ongkir" placeholder="Rp 0" :readonly="!isManualEkspedisi" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 pt-6 mt-6 border-t md:grid-cols-2 border-base-200">
                    <div>
                        <CustomSelectSearch
                            v-model="form.kode_voucher"
                            :options="voucherOptions"
                            labelKey="label"
                            valueKey="value"
                            label="KODE VOUCHER PROMO"
                            placeholder="Cari Voucher Tersedia..."
                        />
                    </div>
                </div>

            </div>

            <div class="grid items-start grid-cols-1 gap-6 lg:grid-cols-12">
                <div class="p-6 space-y-6 border shadow-sm lg:col-span-7 rounded-3xl bg-base-100 border-base-300">
                    <div class="flex gap-2 pb-3 overflow-x-auto border-b border-base-200 scrollbar-hide">
                        <button @click="filterKategori = 'semua'" class="px-6 font-bold border-none btn btn-sm rounded-xl" :class="filterKategori === 'semua' ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content hover:bg-base-300'">Semua</button>
                        <button v-for="kat in kategoris" :key="kat.id_kategori" @click="filterKategori = kat.id_kategori" class="px-6 font-bold border-none btn btn-sm rounded-xl" :class="filterKategori === kat.id_kategori ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content hover:bg-base-300'">{{ kat.nama_kategori }}</button>
                    </div>
                    <div class="pr-2 overflow-y-auto max-h-125 scrollbar-hide">
                        <ProdukRow v-for="group in filteredGroupedProduks" :key="group.id_kategori" :title="group.nama_kategori" :data="group.data" />
                    </div>
                </div>

                <div class="lg:col-span-5 border rounded-3xl shadow-sm bg-base-100 border-base-300 overflow-hidden flex flex-col max-h-147.5">
                    <div class="flex items-center justify-between p-5 border-b border-base-200 bg-base-200/40">
                        <div>
                            <h3 class="text-sm font-black tracking-widest uppercase text-base-content">Struk Keranjang</h3>
                            <span class="text-[10px] font-bold opacity-50 uppercase tracking-wider">Total Item: {{ cartItems.length }}</span>
                        </div>
                        <button v-if="cartItems.length > 0" @click="kosongkanKeranjang" type="button" class="text-[10px] font-black uppercase text-error hover:underline">✕ Kosongkan Form & Keranjang</button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-base-200/20 min-h-62.5">
                        <div v-for="(item, idx) in cartItems" :key="idx" class="relative flex flex-col gap-1 p-4 border shadow-sm bg-base-100 rounded-2xl border-base-300 group">
                            <div class="absolute flex items-center transition-opacity opacity-0 top-3 right-3 group-hover:opacity-100">
                                <button @click="hapusItem(idx)" type="button" class="text-white btn btn-xs btn-circle btn-error">✕</button>
                            </div>

                            <span class="text-[9px] font-black uppercase tracking-wider text-warning">
                                ⏳ {{ item.estimasi_pengerjaan }}
                            </span>

                            <h4 class="pr-10 text-sm font-black truncate text-base-content">{{ item.nama_produk_snapshot }}</h4>

                            <div class="flex items-end justify-between pt-3 mt-3 border-t border-base-200">
                                <div class="text-[10px] font-bold opacity-60 flex flex-col gap-0.5">

                                    <span v-if="item.total_diskon_snapshot > 0">
                                        Harga Dasar:
                                        <span class="mr-1 line-through text-error">{{ formatRupiah(item.harga_dasar_awal_snapshot) }}</span>
                                        {{ formatRupiah(hitungHargaDasar(item)) }}
                                    </span>
                                    <span v-else>Harga Dasar: {{ formatRupiah(hitungHargaDasar(item)) }}</span>

                                    <div v-if="item.rincian_diskon_snapshot && item.rincian_diskon_snapshot.length > 0" class="flex flex-col gap-1 mt-0.5">
                                        <span v-for="(diskon, dIdx) in item.rincian_diskon_snapshot" :key="dIdx" class="text-[9px] text-success bg-success/10 px-1 py-0.5 rounded w-fit uppercase font-bold">
                                            ✨ {{ diskon.nama }}: -{{ formatRupiah(diskon.nominal) }}
                                        </span>
                                    </div>

                                    <span v-if="item.finishings?.length" class="mt-1">Finishing: + {{ formatRupiah(hitungTotalFinishing(item)) }}</span>
                                    <span class="mt-1 text-primary">QTY: {{ item.jumlah }} pcs</span>
                                </div>
                                <div class="text-right">
                                    <span v-if="item.harga_pengerjaan_snapshot > 0" class="block text-[9px] opacity-50 uppercase tracking-widest font-black mb-1">
                                        + SLA {{ formatRupiah(item.harga_pengerjaan_snapshot) }}
                                    </span>
                                    <span class="text-sm font-black text-base-content">
                                        {{ formatRupiah(hitungTotalItem(item)) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-if="cartItems.length === 0" class="flex flex-col items-center justify-center gap-2 py-20 opacity-30">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <p class="text-xs font-black tracking-widest uppercase">Belum Ada Item Terpilih</p>
                        </div>
                    </div>

                    <div class="p-5 space-y-3 border-t border-base-200 bg-base-100">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold opacity-60">Ongkos Kirim</span>
                            <span class="text-sm font-black" :class="form.harga_ongkir > 0 ? 'text-success' : ''">
                                + {{ formatRupiah(form.ekspedisi_nama === 'Ambil di Toko' ? 0 : form.harga_ongkir) }}
                            </span>
                        </div>
                        <div v-if="form.diskon_voucher_nominal > 0" class="flex items-center justify-between mt-2">
                            <span class="text-xs font-bold text-error opacity-80">Voucher Promo</span>
                            <span class="text-sm font-black text-error">
                                - {{ formatRupiah(form.diskon_voucher_nominal) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-base-200">
                            <span class="text-xs font-black tracking-widest uppercase opacity-60">Grand Total Nota</span>
                            <span class="text-2xl font-black text-primary">{{ formatRupiah(grandTotal) }}</span>
                        </div>

                        <CustomButton @click="submitCheckout" :disabled="form.processing || cartItems.length === 0" variant="primary" class="w-full py-4 mt-2 font-black tracking-widest text-center rounded-2xl">
                            {{ form.processing ? 'MEMPROSES...' : 'SIMPAN & CETAK NOTA' }}
                        </CustomButton>
                    </div>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
