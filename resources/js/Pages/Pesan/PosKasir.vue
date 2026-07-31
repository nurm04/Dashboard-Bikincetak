<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import axios from 'axios';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomSelectSearch from '@/Components/Form/CustomSelectSearch.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import CustomInputNumber from '@/Components/Form/CustomInputNumber.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';

// Komponen Reusable
import OrderItemsTable from './Partials/OrderItemsTable.vue';
import OrderSummary from './Partials/OrderSummary.vue';
import OrderFormCard from './Partials/OrderFormCard.vue';

const props = defineProps({
    customers: Array,
    vouchers: Array,
    enumPembayaran: Array,
    kategoris: Array,
    produks: Array,
});

// ==========================================
// 1. INDEXED_DB LOGIC
// ==========================================
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


// ==========================================
// 2. STATE FORM & LOCAL STORAGE
// ==========================================
const cartItems = ref([]);
const savedState = JSON.parse(localStorage.getItem('pos_form_state')) || {};

const form = useForm({
    id_customer: savedState.id_customer || '',
    id_alamat: savedState.id_alamat || '',
    status_pembayaran: savedState.status_pembayaran || 'belum_lunas',
    nominal_bayar: savedState.nominal_bayar || 0,
    kode_voucher: savedState.kode_voucher || '',
    diskon_voucher_nominal: savedState.diskon_voucher_nominal || 0,
    ekspedisi_nama: savedState.ekspedisi_nama || 'Ambil di Toko',
    ekspedisi_layanan: savedState.ekspedisi_layanan || 'Ambil Sendiri',
    harga_ongkir: savedState.harga_ongkir || 0,
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

onMounted(() => {
    cartItems.value = JSON.parse(localStorage.getItem('pos_cart')) || [];
});

watch(cartItems, (newCart) => {
    localStorage.setItem('pos_cart', JSON.stringify(newCart));
}, { deep: true });


// ==========================================
// 3. PELANGGAN, ALAMAT & HITUNG ULANG OTOMATIS
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
        const hargaAwal = item.harga_dasar_awal_snapshot || 0;
        let diskonMember = 0;
        let namaDiskonMember = '';

        if (roleId) {
            const d = item.master_diskon_customer.find(x => String(x.id_role_customer) === String(roleId));
            if (d) {
                diskonMember = d.tipe === 'persen' ? hargaAwal * (Number(d.nilai) / 100) : Number(d.nilai);
                namaDiskonMember = d.tipe === 'persen' ? `Diskon ${roleName} (${d.nilai}%)` : `Diskon ${roleName} (Nominal)`;
            }
        }

        let diskonGrosir = 0;
        let namaDiskonGrosir = '';
        const tier = [...(item.master_harga_bertingkat || [])]
            .sort((a, b) => b.min - a.min)
            .find(t => qty >= t.min && (t.max === 0 || t.max === null || qty <= t.max));

        if (tier) {
            diskonGrosir = tier.tipe === 'persen' ? hargaAwal * (Number(tier.nilai) / 100) : Number(tier.nilai);
            namaDiskonGrosir = tier.tipe === 'persen' ? `Harga Grosir Qty ${qty} (${tier.nilai}%)` : `Harga Grosir Qty ${qty}`;
        }

        const rincianDiskon = [];
        if (diskonGrosir > 0) rincianDiskon.push({ nama: namaDiskonGrosir, nominal: diskonGrosir });
        if (diskonMember > 0) rincianDiskon.push({ nama: namaDiskonMember, nominal: diskonMember });

        const totalDiskonSatuan = diskonGrosir + diskonMember;
        item.total_diskon_snapshot = totalDiskonSatuan;
        item.rincian_diskon_snapshot = rincianDiskon;
        item.harga_satuan_snapshot = Math.max(0, hargaAwal - totalDiskonSatuan);

        // ==== PERBAIKAN RUMUS CETAK BUKU DI KERANJANG ====
        let hargaSatuProdukFull = item.harga_satuan_snapshot;

        if (item.tipe_kalkulasi === 'cetak_buku') {
            let hal = parseInt(item.atribut_custom_snapshot?.['Jumlah Halaman'], 10);
            if (isNaN(hal) || hal < 1) hal = 1;

            const sisi = Number(item.atribut_custom_snapshot?.['Sisi Cetak']) || 1;

            // Halaman 1 Gratis
            const tambahanHalaman = Math.max(0, hal - 1);
            const biayaHalaman = tambahanHalaman * sisi * 1500;

            hargaSatuProdukFull += biayaHalaman; // Tambah Harga Kertas ke Harga Dasar
        }
        // =================================================

        const totalHargaProduk = hargaSatuProdukFull * qty;
        const totalFinishing = hitungTotalFinishing(item, qty, hargaSatuProdukFull);
        const newTotalProduk = totalHargaProduk + totalFinishing;

        if (item.master_harga_pengerjaan) {
            const p = item.master_harga_pengerjaan.find(o => o.pengerjaan === item.estimasi_pengerjaan_snapshot);
            if (p) {
                item.harga_pengerjaan_snapshot = p.tipe === 'persen' ? newTotalProduk * (Number(p.nilai) / 100) : Number(p.nilai);
            }
        }

        item.total_produk = newTotalProduk;
        item.total_sla = item.harga_pengerjaan_snapshot;
        item.subtotal = newTotalProduk + item.total_sla;

        return item;
    });

    cartItems.value = [...cartItems.value];

    if (hasUpdates) {
        alertStore.show('Harga disesuaikan dengan Diskon Pelanggan.', 'info');
    }
};

// Watcher Customer Baru
watch(() => form.id_customer, (newId, oldId) => {
    const selectedCust = props.customers.find(c => c.id_customer === newId);
    localStorage.setItem('pos_active_customer', JSON.stringify(selectedCust || null));

    if (oldId !== undefined && newId !== oldId) {
        const list = alamatOptions.value;
        if (list.length > 0) {
            form.id_alamat = list[0].id_alamat;
        } else {
            form.id_alamat = '';
            if (newId) alertStore.show('Customer ini belum mendaftarkan alamat!', 'info');
        }

        recalculateCartItems(selectedCust);
    }
}, { immediate: true });


// ==========================================
// 4. LOGIKA PENGIRIMAN & ONGKIR
// ==========================================
const ekspedisiOptions = [
    { id: 'Ambil di Toko', nama: 'Ambil di Toko (Rp 0)' },
    { id: 'Kurir Toko', nama: 'Kurir Lokal / Instan' },
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
    { id: 'Titip Travel', nama: 'Titip Travel' },
    { id: 'Lainnya', nama: 'Lainnya' },
];

const layananOptions = ref([]);
const isLoadingOngkir = ref(false);

const isManualEkspedisi = computed(() => ['Ambil di Toko', 'Kurir Toko'].includes(form.ekspedisi_nama));

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
            alertStore.show('Tarif Ongkir berhasil dihitung ulang!', 'success');
        } else {
            alertStore.show('Layanan kurir tidak tersedia untuk rute tersebut.', 'error');
            form.ekspedisi_nama = 'Kurir Toko';
        }
    } catch (error) {
        alertStore.show('Gagal menghubungi server ongkir.', 'error');
        form.ekspedisi_nama = 'Kurir Toko';
    } finally {
        isLoadingOngkir.value = false;
    }
};

// Hitung Ongkir Otomatis Jika Alamat Berubah
watch(() => form.id_alamat, (newAlamat, oldAlamat) => {
    if (oldAlamat !== undefined && newAlamat !== oldAlamat) {
        if (cartItems.value.length > 0 && !isManualEkspedisi.value) {
            fetchOngkir();
        }
    }
});

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
        if (selected) form.harga_ongkir = selected.cost;
    }
});


// ==========================================
// 5. MANAJEMEN KERANJANG & FORM ITEM
// ==========================================
const showOrderForm = ref(false);
const itemToEdit = ref(null);

const handleRequestAdd = () => { itemToEdit.value = null; showOrderForm.value = true; };
const handleRequestEdit = (item) => { itemToEdit.value = { ...item }; showOrderForm.value = true; };
const handleCancelForm = () => { itemToEdit.value = null; showOrderForm.value = false; };

const handleFormSubmit = async (payload) => {
    const cartId = itemToEdit.value ? itemToEdit.value.cart_id : 'cart_' + Date.now();

    if (payload.tipe_file === 'upload' && payload.file instanceof File) {
        try {
            const db = await initDB();
            const tx = db.transaction('cart_files', 'readwrite');
            tx.objectStore('cart_files').put([payload.file], cartId);
        } catch (error) {
            console.error('Gagal simpan file ke IndexedDB:', error);
            alertStore.show('Gagal memproses file desain!', 'error');
            return;
        }
    }

    const dataToSave = {
        ...payload,
        cart_id: cartId,
        pesanan_item_finishing: payload.finishing,
        estimasi_pengerjaan_snapshot: payload.estimasi_pengerjaan
    };

    if (dataToSave.file instanceof File) {
        dataToSave.file = {
            name: dataToSave.file.name,
            size: dataToSave.file.size,
            type: dataToSave.file.type
        };
    }

    if (itemToEdit.value) {
        const idx = cartItems.value.findIndex(c => c.cart_id === itemToEdit.value.cart_id);
        if (idx !== -1) cartItems.value[idx] = dataToSave;
    } else {
        cartItems.value.push(dataToSave);
    }

    handleCancelForm();
    alertStore.show('Item tersimpan di keranjang!', 'success');
};

const hapusItem = async (cartId) => {
    cartItems.value = cartItems.value.filter(c => c.cart_id !== cartId);
    await deleteFilesFromDB(cartId);
    alertStore.show('Item dihapus', 'info');
};


// ==========================================
// 6. KALKULASI TOTAL & VOUCHER
// ==========================================
const hitungTotalFinishing = (item, qtyPesanan, hargaDasarProduk) => {
    const listFinishing = item.pesanan_item_finishing || [];
    let totalBiayaFinishing = 0;

    listFinishing.forEach(f => {
        let biayaPerFinishing = 0;

        if (f.tipe === 'persen') {
            biayaPerFinishing = hargaDasarProduk * (Number(f.harga_finishing_snapshot) / 100);
        } else {
            biayaPerFinishing = Number(f.harga_finishing_snapshot) || 0;
        }

        if (f.kali_jumlah_pesan) {
            biayaPerFinishing = biayaPerFinishing * qtyPesanan;
        }

        totalBiayaFinishing += biayaPerFinishing;
    });

    return totalBiayaFinishing;
};

const hitungTotalItem = (item) => {
    const qty = Number(item.jumlah) || 1;
    let hargaSatuProdukFull = Number(item.harga_satuan_snapshot) || 0;

    // ==== PERBAIKAN RUMUS CETAK BUKU MASTER RECALCULATE ====
    if (item.tipe_kalkulasi === 'cetak_buku') {
        let hal = parseInt(item.atribut_custom_snapshot?.['Jumlah Halaman'], 10);
        if (isNaN(hal) || hal < 1) hal = 1;

        const sisi = Number(item.atribut_custom_snapshot?.['Sisi Cetak']) || 1;

        // Halaman 1 Gratis
        const tambahanHalaman = Math.max(0, hal - 1);
        const biayaHalaman = tambahanHalaman * sisi * 1500;

        hargaSatuProdukFull += biayaHalaman;
    }
    // =======================================================

    const totalHargaProduk = hargaSatuProdukFull * qty;
    const totalFinishing = hitungTotalFinishing(item, qty, hargaSatuProdukFull);
    const slaTotal = Number(item.harga_pengerjaan_snapshot) || 0;

    return totalHargaProduk + totalFinishing + slaTotal;
};

const totalProduk = computed(() => cartItems.value.reduce((total, item) => total + hitungTotalItem(item), 0));

const voucherOptions = computed(() => (props.vouchers || []).map(v => ({ value: v.kode_voucher, label: `${v.kode_voucher} - Diskon ${v.persentase_diskon}%` })));

watch([() => form.kode_voucher, cartItems], ([newKode, newItems]) => {
    if (!newKode) { form.diskon_voucher_nominal = 0; return; }
    const voucher = props.vouchers?.find(v => v.kode_voucher === newKode);
    if (!voucher) { form.diskon_voucher_nominal = 0; return; }

    const subtotal = newItems.reduce((total, item) => total + hitungTotalItem(item), 0);
    if (subtotal < Number(voucher.minimal_transaksi_rupiah)) {
        form.diskon_voucher_nominal = 0;
        alertStore.show(`Minimal transaksi voucher: Rp ${formatRupiah(voucher.minimal_transaksi_rupiah)}`, 'warning');
        return;
    }

    let kalkulasiDiskon = 0;
    const persen = Number(voucher.persentase_diskon);

    if (voucher.tipe_target === 'semua_pesanan') {
        kalkulasiDiskon = (subtotal * persen) / 100;
    } else if (voucher.tipe_target === 'produk_tertentu') {
        const totalProdukTarget = newItems.filter(i => i.id_sku === voucher.id_sku_target).reduce((s, i) => s + hitungTotalItem(i), 0);
        kalkulasiDiskon = (totalProdukTarget * persen) / 100;
    }

    const maksPotongan = Number(voucher.maksimal_potongan_rupiah);
    form.diskon_voucher_nominal = Math.round((maksPotongan > 0 && kalkulasiDiskon > maksPotongan) ? maksPotongan : kalkulasiDiskon);
}, { deep: true });

const grandTotal = computed(() => Math.max(0, (totalProduk.value + (Number(form.harga_ongkir) || 0)) - (Number(form.diskon_voucher_nominal) || 0)));

const totalDibayar = computed(() => {
    if (form.status_pembayaran === 'lunas') return grandTotal.value;
    if (form.status_pembayaran === 'dibayar_sebagian') return Number(form.nominal_bayar) || 0;
    return 0;
});
const sisaTagihan = computed(() => Math.max(0, grandTotal.value - totalDibayar.value));

watch(() => form.status_pembayaran, (newStatus) => {
    if (newStatus !== 'dibayar_sebagian') form.nominal_bayar = 0;
});


// ==========================================
// 7. CHECKOUT & SUBMIT
// ==========================================
const resetFormDanKeranjang = async () => {
    cartItems.value = [];
    localStorage.removeItem('pos_cart');
    localStorage.removeItem('pos_form_state');
    localStorage.removeItem('pos_active_customer');
    await clearAllFilesDB();
    form.reset();
};

const submitCheckout = async () => {
    if (cartItems.value.length === 0) return alertStore.show('Keranjang kosong!', 'error');
    if (!form.id_customer) return alertStore.show('Pilih pelanggan dulu!', 'error');
    if (form.ekspedisi_nama !== 'Ambil di Toko' && !form.ekspedisi_layanan) return alertStore.show('Layanan Ekspedisi wajib dipilih!', 'error');

    const formData = new FormData();
    formData.append('id_customer', form.id_customer);
    formData.append('id_alamat', form.id_alamat || 'Alamat Toko');
    formData.append('status_pembayaran', form.status_pembayaran);
    formData.append('nominal_bayar', totalDibayar.value);
    formData.append('kode_voucher', form.kode_voucher || '');
    formData.append('diskon_voucher_nominal', form.diskon_voucher_nominal || 0);

    const namaEkspedisiAsli = ekspedisiOptions.find(e => e.id === form.ekspedisi_nama)?.nama || form.ekspedisi_nama;
    formData.append('ekspedisi_nama', isManualEkspedisi.value ? form.ekspedisi_nama : namaEkspedisiAsli.toUpperCase());
    formData.append('ekspedisi_layanan', form.ekspedisi_nama === 'Ambil di Toko' ? 'Ambil Sendiri' : form.ekspedisi_layanan);
    formData.append('harga_ongkir', form.ekspedisi_nama === 'Ambil di Toko' ? 0 : form.harga_ongkir);

    for (let index = 0; index < cartItems.value.length; index++) {
        const item = cartItems.value[index];

        formData.append(`items[${index}][id_sku]`, item.id_sku);
        formData.append(`items[${index}][jumlah]`, Number(item.jumlah) || 1);
        formData.append(`items[${index}][nama_produk_snapshot]`, item.nama_produk_snapshot);

        formData.append(`items[${index}][harga_satuan_snapshot]`, Number(item.harga_satuan_snapshot) || 0);

        formData.append(`items[${index}][estimasi_pengerjaan]`, item.estimasi_pengerjaan_snapshot || 'Reguler');
        formData.append(`items[${index}][harga_pengerjaan_snapshot]`, Number(item.harga_pengerjaan_snapshot) || 0);
        formData.append(`items[${index}][catatan]`, item.catatan || '');
        formData.append(`items[${index}][tipe_file]`, item.tipe_file || 'upload');
        formData.append(`items[${index}][link_file]`, item.link_file || '');

        const hargaDasarAwal = Number(item.harga_dasar_awal_snapshot) || Number(item.harga_satuan_snapshot) || 0;
        formData.append(`items[${index}][harga_dasar_awal_snapshot]`, hargaDasarAwal);
        formData.append(`items[${index}][total_diskon_snapshot]`, Number(item.total_diskon_snapshot) || 0);

        formData.append(`items[${index}][rincian_diskon_snapshot]`, JSON.stringify(item.rincian_diskon_snapshot || []));
        formData.append(`items[${index}][finishing]`, JSON.stringify(item.pesanan_item_finishing || []));

        // MAPPING JSON Atribut Custom (Buku / dll) ke request payload
        if (item.atribut_custom_snapshot && Object.keys(item.atribut_custom_snapshot).length > 0) {
            formData.append(`items[${index}][atribut_custom_snapshot]`, JSON.stringify(item.atribut_custom_snapshot));
        }

        if (item.tipe_file === 'upload') {
            const filesToUpload = await getFilesFromDB(item.cart_id);
            if (filesToUpload && filesToUpload.length > 0) {
                filesToUpload.forEach(file => formData.append(`items[${index}][file_desain][]`, file));
            }
        }
    }

    router.post(route('pesan.store'), formData, {
        forceFormData: true,
        onSuccess: () => {
            alertStore.show('Pesanan Berhasil Dibuat!', 'success');
            resetFormDanKeranjang();
        },
        onError: (errors) => {
            console.error("Detail Error Validasi Laravel:", errors);
            const firstError = Object.values(errors)[0];
            alertStore.show(firstError || 'Gagal checkout, periksa form input!', 'error');
        }
    });
};

const pembayaranOptionsForm = [
    { value: 'belum_lunas', label: 'Belum Lunas (Piutang)' },
    { value: 'dibayar_sebagian', label: 'Dibayar Sebagian (DP)' },
    { value: 'lunas', label: 'Bayar Lunas' }
];
</script>

<template>
    <Head title="Sistem POS Kasir" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-xl font-bold leading-tight text-base-content flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6 text-primary"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" /></svg>
                    Point of Sale (POS)
                </h2>
                <button v-if="cartItems.length > 0" @click="resetFormDanKeranjang" class="btn btn-sm btn-error btn-outline rounded-xl text-[10px] font-black uppercase tracking-widest">
                    ✕ Reset Kasir
                </button>
            </div>
        </template>

        <div class="px-4 py-6 mx-auto max-w-350">
            <div class="grid items-start grid-cols-1 gap-6 lg:grid-cols-12">

                <!-- KOLOM KIRI: FORM & TABEL -->
                <div class="space-y-6 lg:col-span-8 xl:col-span-9">

                    <!-- CARD: INFORMASI PEMESAN & PENGIRIMAN -->
                    <div class="p-6 bg-base-100 border border-base-200/80 shadow-sm rounded-3xl">
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-base-200/50">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-primary opacity-80"><path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" /></svg>
                            <h3 class="text-[10px] font-black tracking-widest uppercase opacity-50">Informasi Pemesan & Pengiriman</h3>
                        </div>

                        <!-- GRID PELANGGAN -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <CustomSelectSearch
                                v-model="form.id_customer"
                                label="Pilih Pelanggan"
                                :options="customerOptions"
                                valueKey="id_customer" labelKey="nama_tampilan"
                                placeholder="Ketik Nama Pelanggan..."
                            />

                            <CustomSelect
                                v-model="form.id_alamat"
                                label="Alamat Tujuan"
                                :options="alamatOptions"
                                valueKey="id_alamat" labelKey="alamat_lengkap"
                                placeholder="-- Alamat Toko / Walk-in --"
                                :disabled="!form.id_customer || alamatOptions.length === 0"
                            />
                        </div>

                        <!-- GRID KURIR & ONGKIR -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 pt-6 border-t border-dashed border-base-200/80">
                            <div class="md:col-span-4">
                                <CustomSelect v-model="form.ekspedisi_nama" label="Kurir / Ekspedisi" :options="ekspedisiOptions" valueKey="id" labelKey="nama" />
                            </div>

                            <div class="md:col-span-4" v-if="form.ekspedisi_nama !== 'Ambil di Toko'">
                                <template v-if="isManualEkspedisi">
                                    <CustomSelect v-model="form.ekspedisi_layanan" label="Layanan Lokal" :options="manualLayananOptions" valueKey="id" labelKey="nama" placeholder="Pilih Instan/Lokal..." />
                                </template>
                                <template v-else>
                                    <div v-if="isLoadingOngkir" class="flex flex-col gap-1">
                                        <label class="text-[10px] font-bold uppercase opacity-70 ml-1 block">Layanan Ongkir</label>
                                        <div class="flex items-center gap-2 h-11 px-3 border border-base-300 bg-base-200/50 rounded-xl text-xs font-bold text-primary animate-pulse">
                                            <span class="loading loading-spinner loading-xs"></span> Mengambil Tarif...
                                        </div>
                                    </div>
                                    <CustomSelect v-else v-model="form.ekspedisi_layanan" label="Layanan Ongkir" :options="layananOptions" valueKey="id" labelKey="nama" placeholder="Pilih Layanan Ekspedisi..." />
                                </template>
                            </div>

                            <div class="md:col-span-4" v-if="form.ekspedisi_nama !== 'Ambil di Toko'">
                                <CustomInputNumber v-model="form.harga_ongkir" label="Biaya Ongkir (Rp)" placeholder="Rp 0" :readonly="!isManualEkspedisi" />
                            </div>
                        </div>
                    </div>

                    <!-- FORM TAMBAH ITEM -->
                    <div v-show="showOrderForm" class="transition-all duration-300">
                        <OrderFormCard
                            v-if="showOrderForm"
                            :isPosMode="true"
                            :editData="itemToEdit"
                            @cancel="handleCancelForm"
                            @submit="handleFormSubmit"
                        />
                    </div>

                    <!-- TABEL KERANJANG -->
                    <OrderItemsTable
                        :items="cartItems"
                        @requestEdit="handleRequestEdit"
                        @deleteItem="hapusItem"
                        @addItem="handleRequestAdd"
                    />

                </div>

                <!-- KOLOM KANAN: SUMMARY & BAYAR -->
                <div class="lg:col-span-4 xl:col-span-3">
                    <div class="sticky top-24 space-y-6">

                        <!-- Pilihan Status Pembayaran -->
                        <div class="p-5 bg-base-100 border border-base-200/80 shadow-sm rounded-3xl">
                            <h3 class="text-[10px] font-black tracking-widest uppercase opacity-40 mb-3 border-b border-base-200/50 pb-2">Opsi Pembayaran</h3>

                            <div class="flex flex-col gap-2.5 mt-4">
                                <label v-for="opt in pembayaranOptionsForm" :key="opt.value"
                                       class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-all duration-200"
                                       :class="form.status_pembayaran === opt.value ? 'border-primary bg-primary/5' : 'border-base-200 hover:border-base-300'">
                                    <input type="radio" v-model="form.status_pembayaran" :value="opt.value" class="radio radio-primary radio-sm" />
                                    <span class="text-[11px] font-black uppercase">{{ opt.label }}</span>
                                </label>
                            </div>

                            <div v-if="form.status_pembayaran === 'dibayar_sebagian'" class="mt-4 pt-4 border-t border-dashed border-base-200/80">
                                <CustomInputNumber
                                    v-model="form.nominal_bayar"
                                    label="Nominal DP Sekarang"
                                    placeholder="Rp ..."
                                    :max="grandTotal"
                                />
                            </div>

                            <div class="mt-4 pt-4 border-t border-dashed border-base-200/80">
                                <CustomSelectSearch
                                    v-model="form.kode_voucher"
                                    :options="voucherOptions"
                                    labelKey="label" valueKey="value"
                                    label="KODE VOUCHER PROMO"
                                    placeholder="Cari Voucher Tersedia..."
                                />
                            </div>
                        </div>

                        <!-- Ringkasan Tagihan (Reusable Component) -->
                        <OrderSummary
                            :total_tagihan="totalProduk"
                            :harga_ongkir="form.ekspedisi_nama === 'Ambil di Toko' ? 0 : form.harga_ongkir"
                            :diskon_voucher_nominal="form.diskon_voucher_nominal"
                            :kode_voucher="form.kode_voucher"
                            :kode_unik="0"
                            :total_transfer="grandTotal"
                            :total_dibayar="totalDibayar"
                            :sisa_tagihan="sisaTagihan"
                        />

                        <!-- Tombol Eksekusi -->
                        <CustomButton
                            variant="primary"
                            class="w-full h-14 rounded-2xl shadow-xl shadow-primary/20 text-sm tracking-widest font-black uppercase"
                            @click="submitCheckout"
                            :disabled="form.processing || cartItems.length === 0"
                        >
                            <span v-if="form.processing" class="loading loading-spinner loading-md"></span>
                            <span v-else>Proses & Buat Pesanan</span>
                        </CustomButton>
                    </div>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
