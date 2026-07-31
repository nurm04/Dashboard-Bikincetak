<script setup>
import { ref, computed, watch } from 'vue';
import { alertStore } from '@/Utils/alertStore';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { Clock, CheckCircle, Truck, Inbox, Plus, Trash2, Paperclip, UploadCloud, Printer } from 'lucide-vue-next';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomInputFile from '@/Components/Form/CustomInputFile.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';

const page = usePage();
const currentUser = page.props.auth?.user;

const props = defineProps({
    pesananProduksi: Array,
    vendors: Array,
    currentVendorId: String,
});

const formatTanggal = (tgl) => {
    if (!tgl) return '-';
    const date = new Date(tgl);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day} ${hours}:${minutes}`;
};

const isDeadlinePassed = (dateStr) => {
    return new Date(dateStr) < new Date();
};

const getFileDisplay = (item) => {
    if (item.file_desain) {
        let fileData = item.file_desain;
        if (typeof fileData === 'string') {
            try { fileData = JSON.parse(fileData); } catch(e) { return null; }
        }
        if (Array.isArray(fileData) && fileData.length > 0) return { tipe: 'upload', nilai: fileData[0] };
        if (typeof fileData === 'object' && !Array.isArray(fileData)) return fileData;
        return null;
    }
    if (item.tipe_file) {
        return {
            tipe: item.tipe_file,
            nilai: item.tipe_file === 'link' ? item.link_file : (item.file ? (item.file.name || 'File Uploaded') : 'Kosong')
        };
    }
    return null;
};

const cleanProductName = (name) => {
    if (!name) return '';
    return name.replace(/^[A-Za-z]+-\d+-/, '').replace(/-/g, ' ');
};

const parseAtribut = (atributStr) => {
    if (!atributStr) return null;
    if (typeof atributStr === 'object') return atributStr;
    try {
        return JSON.parse(atributStr);
    } catch (e) {
        console.error("Gagal parse atribut:", e);
        return null;
    }
};

const getValidAttributes = (atributStr) => {
    const parsed = parseAtribut(atributStr);
    if (!parsed || typeof parsed !== 'object') return [];

    return Object.entries(parsed)
        .filter(([key, value]) => value !== null && value !== undefined && value !== '')
        .map(([key, value]) => ({ key, value }));
};

const headersProses = ['Pelaksana', 'Instruksi / Keterangan', 'Qty', 'Status', 'Aksi'];

// ==========================================
// LOGIC AUTORISASI EDIT
// ==========================================
const checkAccess = (schedule) => {
    const role = currentUser?.role;

    if (role === 'vendor') {
        if (schedule.tipe_pengerjaan === 'vendor' && schedule.id_vendor === props.currentVendorId) {
            return 'edit';
        }
        return 'none';
    }

    const isAdmin = role === 'admin' || role === 'administrator';
    if (schedule.status_pengerjaan === 'selesai') {
        return isAdmin ? 'edit' : 'view';
    }

    return 'edit';
};

// ==========================================
// STATE & LOGIC UNTUK ALOKASI PRODUKSI
// ==========================================
const isAlokasiModalOpen = ref(false);
const selectedOrderAlokasi = ref(null);

const alokasiForm = useForm({
    alokasi: []
});

const openAlokasiModal = (pesanan) => {
    selectedOrderAlokasi.value = pesanan;
    alokasiForm.alokasi = pesanan.pesanan_item.map(item => ({
        id_pesanan_item: item.id,
        nama_produk: item.nama_produk_snapshot,
        total_qty: item.jumlah,
        is_desain: item.id_sku?.startsWith('PRD-0002') || false,
        skema: [
            { tipe_pengerjaan: 'sendiri', id_vendor: null, qty_dikerjakan: item.jumlah, instruksi_pengerjaan: '' }
        ]
    }));
    isAlokasiModalOpen.value = true;
};

const closeAlokasiModal = () => {
    isAlokasiModalOpen.value = false;
    selectedOrderAlokasi.value = null;
    alokasiForm.reset();
};

const addSkema = (itemIndex) => {
    alokasiForm.alokasi[itemIndex].skema.push({
        tipe_pengerjaan: 'vendor', id_vendor: null, qty_dikerjakan: 1, instruksi_pengerjaan: ''
    });
};

const removeSkema = (itemIndex, skemaIndex) => {
    alokasiForm.alokasi[itemIndex].skema.splice(skemaIndex, 1);
};

const submitAlokasi = () => {
    for (const item of alokasiForm.alokasi) {
        const totalInput = item.skema.reduce((acc, curr) => acc + Number(curr.qty_dikerjakan), 0);
        if (totalInput !== item.total_qty) {
            alertStore.show(`Total alokasi Qty untuk ${cleanProductName(item.nama_produk)} (${totalInput}) tidak sama dengan pesanan (${item.total_qty})!`, 'warning');
            return;
        }
    }

    alokasiForm.post(route('produksi.alokasi', selectedOrderAlokasi.value.id_pesan), {
        onSuccess: () => {
            closeAlokasiModal();
            alertStore.show('Alokasi berhasil disimpan!', 'success');
        },
        onError: () => alertStore.show('Terjadi kesalahan saat memproses alokasi.', 'error')
    });
};

// ==========================================
// STATE & LOGIC UNTUK UPDATE PROGRESS
// ==========================================
const isUpdateModalOpen = ref(false);
const selectedSchedule = ref(null);
const selectedItemUpdate = ref(null);
const isViewOnly = ref(false);

const updateForm = useForm({
    deskripsi_pengerjaan: '',
    total_tagihan_vendor: null,
    file_nota: null,
    hasil_desain: null,
});

const fileNotaObj = ref({ tipe_file: 'upload', file: null, link_file: '' });
const fileHasilObj = ref({ tipe_file: 'upload', file: null, link_file: '' });

const openUpdateModal = (schedule, item) => {
    const access = checkAccess(schedule);

    if (access === 'none') {
        alertStore.show('Akses Ditolak! Anda tidak memiliki izin untuk mengelola item ini.', 'error');
        return;
    }

    isViewOnly.value = access === 'view';
    selectedSchedule.value = schedule;
    selectedItemUpdate.value = item;

    updateForm.deskripsi_pengerjaan = schedule.deskripsi_pengerjaan || '';
    updateForm.total_tagihan_vendor = schedule.total_tagihan_vendor || null;

    updateForm.file_nota = null;
    updateForm.hasil_desain = null;

    fileNotaObj.value = { tipe_file: 'upload', file: schedule.file_nota || null, link_file: '' };
    fileHasilObj.value = { tipe_file: 'upload', file: schedule.file_revisi || null, link_file: '' };

    isUpdateModalOpen.value = true;
};

const closeUpdateModal = () => {
    isUpdateModalOpen.value = false;
    selectedSchedule.value = null;
    selectedItemUpdate.value = null;
    isViewOnly.value = false;
    updateForm.reset();
};

const submitUpdate = () => {
    updateForm.post(route('produksi.selesaikan', selectedSchedule.value.id), {
        forceFormData: true,
        onSuccess: () => {
            closeUpdateModal();
            alertStore.show('Progress item berhasil diperbarui!', 'success');
        },
        onError: () => alertStore.show('Gagal memperbarui progress. Periksa file Anda.', 'error')
    });
};

// ==========================================
// LOGIC KONTROL PENGIRIMAN & UPDATE ONGKIR
// ==========================================
const isReadyToShip = (pesanan) => {
    if (!pesanan.pesanan_item || pesanan.pesanan_item.length === 0) return false;
    let hasSchedule = false;
    let allCompleted = true;

    pesanan.pesanan_item.forEach(item => {
        if (!item.pesanan_item_produksi || item.pesanan_item_produksi.length === 0) {
            allCompleted = false;
        } else {
            hasSchedule = true;
            item.pesanan_item_produksi.forEach(sch => {
                if (sch.status_pengerjaan !== 'selesai') allCompleted = false;
            });
        }
    });

    return hasSchedule && allCompleted;
};

// --- ALUR LAMA (REGULER / BUKAN PRD-0001-SKU-001) ---
const isConfirmKirimOpen = ref(false);
const selectedKirimId = ref(null);

const formKirim = useForm({
    nomor_resi: ''
});

const executeKirimPesanan = () => {
    if (!selectedKirimId.value) return;

    formKirim.post(route('produksi.kirim', selectedKirimId.value), {
        onSuccess: () => {
            alertStore.show('Pesanan masuk ke status Pengantaran!', 'success');
            isConfirmKirimOpen.value = false;
            selectedKirimId.value = null;
            formKirim.reset();
        },
        onError: () => alertStore.show('Gagal mengubah status pesanan.', 'error')
    });
};

const closeKirimModal = () => {
    isConfirmKirimOpen.value = false;
    selectedKirimId.value = null;
    formKirim.reset();
};

// ==========================================
// FORM 1: MODAL UPDATE BERAT (KHUSUS CUSTOM)
// ==========================================
const isModalBeratOpen = ref(false);
const selectedPengantaran = ref(null);

const formBerat = useForm({
    items: []
});

const openModalBerat = (pesanan) => {
    selectedPengantaran.value = pesanan;
    const customItems = pesanan.pesanan_item.filter(i => i.id_sku === 'PRD-0001-SKU-001');

    formBerat.items = customItems.map(i => ({
        id_pesanan_item: i.id,
        nama_produk: cleanProductName(i.nama_produk_snapshot),
        berat: Number(i.total_berat_snapshot) || 0
    }));

    isModalBeratOpen.value = true;
};

const closeModalBerat = () => {
    isModalBeratOpen.value = false;
    formBerat.reset();
};

const submitBerat = () => {
    formBerat.put(route('produksi.update_berat', selectedPengantaran.value.id_pesan), {
        preserveScroll: true,
        onSuccess: () => {
            closeModalBerat();
            alertStore.show('Berat berhasil disimpan ke database!', 'success');
            openPengantaranModal(selectedPengantaran.value);
        },
        onError: () => alertStore.show('Gagal menyimpan berat.', 'error')
    });
};

// ==========================================
// FORM 2: MODAL PENGIRIMAN & ONGKIR
// ==========================================
const isPengantaranModalOpen = ref(false);
const layananOptions = ref([]);
const isLoadingOngkir = ref(false);

const formPengantaran = useForm({
    id_alamat: '',
    ekspedisi_nama: '',
    ekspedisi_layanan: '',
    harga_ongkir: 0,
    ekspedisi_estimasi: '',
    nomor_resi: '' // Tambahan field resi
});

const ekspedisiOptions = [
    { id: 'Ambil di Toko', nama: 'Ambil di Toko (Rp 0)' },
    { id: 'Kurir Toko', nama: 'Kurir Lokal / Instan' },
    { id: 'jne', nama: 'JNE' },
    { id: 'sicepat', nama: 'SiCepat' },
    { id: 'jnt', nama: 'J&T' },
    { id: 'pos', nama: 'POS Indonesia' },
];

const manualLayananOptions = [
    { id: 'Gojek / Grab - Bayar Langsung', nama: 'Gojek / Grab - Bayar Langsung' },
    { id: 'Gojek / Grab - COD (Bayar di Tempat)', nama: 'Gojek / Grab - COD (Bayar di Tempat)' },
    { id: 'Lalamove / Deliveree - Bayar Langsung', nama: 'Lalamove / Deliveree - Bayar Langsung' },
    { id: 'Lalamove / Deliveree - COD (Bayar di Tempat)', nama: 'Lalamove / Deliveree - COD (Bayar di Tempat)' },
    { id: 'Kurir Toko - Bayar Langsung', nama: 'Kurir Toko - Bayar Langsung' },
    { id: 'Kurir Toko - COD (Bayar di Tempat)', nama: 'Kurir Toko - COD (Bayar di Tempat)' },
    { id: 'J&T Cargo - Bayar Langsung', nama: 'J&T Cargo - Bayar Langsung' },
    { id: 'J&T Cargo - COD (Bayar di Tempat)', nama: 'J&T Cargo - COD (Bayar di Tempat)' },
];

const isManualEkspedisi = computed(() => ['Ambil di Toko', 'Kurir Toko'].includes(formPengantaran.ekspedisi_nama));

const openPengantaranModal = (pesanan) => {
    formPengantaran.id_alamat = pesanan.id_alamat || '';

    let ekspNameRaw = (pesanan.ekspedisi_nama || '').toLowerCase();
    let matchedCode = 'Kurir Toko';

    if (ekspNameRaw.includes('ambil di toko')) matchedCode = 'Ambil di Toko';
    else if (ekspNameRaw.includes('kurir')) matchedCode = 'Kurir Toko';
    else if (ekspNameRaw.includes('jne')) matchedCode = 'jne';
    else if (ekspNameRaw.includes('sicepat')) matchedCode = 'sicepat';
    else if (ekspNameRaw.includes('j&t') || ekspNameRaw.includes('jnt')) matchedCode = 'jnt';
    else if (ekspNameRaw.includes('pos')) matchedCode = 'pos';

    formPengantaran.ekspedisi_nama = matchedCode;
    formPengantaran.ekspedisi_layanan = pesanan.ekspedisi_layanan || '';
    formPengantaran.harga_ongkir = pesanan.harga_ongkir || 0;
    formPengantaran.nomor_resi = pesanan.nomor_resi || ''; // Set nilai resi jika sudah ada

    layananOptions.value = [];
    isPengantaranModalOpen.value = true;
};

const closePengantaranModal = () => {
    isPengantaranModalOpen.value = false;
    selectedPengantaran.value = null;
    formPengantaran.reset();
};

const fetchOngkir = async () => {
    if (!formPengantaran.id_alamat || isManualEkspedisi.value) return;

    isLoadingOngkir.value = true;
    layananOptions.value = [];
    formPengantaran.ekspedisi_layanan = '';
    formPengantaran.harga_ongkir = 0;

    try {
        const itemsPayload = selectedPengantaran.value.pesanan_item.map(item => ({
            id_sku: item.id_sku,
            jumlah: item.jumlah,
            finishings: item.pesanan_item_finishing,
            total_berat: item.total_berat_snapshot
        }));

        const response = await axios.post('/ongkir/calculate', {
            id_alamat: formPengantaran.id_alamat,
            courier: formPengantaran.ekspedisi_nama,
            items: itemsPayload
        });

        const data = response.data;
        let costs = [];

        if (data?.data && Array.isArray(data.data)) {
            costs = data.data.map(i => ({
                id: i.service,
                nama: `${i.service} (${i.etd || '-'} Hari) - Rp ${Number(i.cost).toLocaleString('id-ID')}`,
                cost: i.cost,
                etd: i.etd || ''
            }));
        } else if (data?.rajaongkir?.results?.[0]?.costs) {
            costs = data.rajaongkir.results[0].costs.map(s => ({
                id: s.service,
                nama: `${s.service} (${s.cost[0]?.etd || '-'} Hari) - Rp ${Number(s.cost[0]?.value).toLocaleString('id-ID')}`,
                cost: s.cost[0]?.value,
                etd: s.cost[0]?.etd || ''
            }));
        }

        if (costs.length > 0) {
            layananOptions.value = costs;
        } else {
            alertStore.show('Layanan kurir tidak tersedia untuk rute tersebut.', 'error');
            formPengantaran.ekspedisi_nama = 'Kurir Toko';
        }
    } catch (error) {
        alertStore.show('Gagal menghubungi server ongkir.', 'error');
        formPengantaran.ekspedisi_nama = 'Kurir Toko';
    } finally {
        isLoadingOngkir.value = false;
    }
};

watch(() => formPengantaran.ekspedisi_nama, async (newCourier, oldCourier) => {
    if (!oldCourier) return;

    if (newCourier === 'Ambil di Toko') {
        layananOptions.value = [];
        formPengantaran.ekspedisi_layanan = 'Ambil Sendiri';
        formPengantaran.harga_ongkir = 0;
    } else if (newCourier === 'Kurir Toko') {
        layananOptions.value = [];
        formPengantaran.ekspedisi_layanan = '';
        formPengantaran.harga_ongkir = 0;
    } else {
        if (!formPengantaran.id_alamat) return;
        await fetchOngkir();
    }
});

watch(() => formPengantaran.ekspedisi_layanan, (newLayanan) => {
    if (!isManualEkspedisi.value && newLayanan) {
        const selected = layananOptions.value.find(l => l.id === newLayanan);
        if (selected) {
            formPengantaran.harga_ongkir = selected.cost;
            formPengantaran.ekspedisi_estimasi = selected.etd;
        }
    }
});

const submitPengantaran = () => {
    if (formPengantaran.ekspedisi_nama !== 'Ambil di Toko' && !formPengantaran.ekspedisi_layanan) {
        alertStore.show('Pilih Layanan Pengiriman terlebih dahulu!', 'error');
        return;
    }

    const namaEkspedisiAsli = ekspedisiOptions.find(e => e.id === formPengantaran.ekspedisi_nama)?.nama || formPengantaran.ekspedisi_nama;
    const finalEkspedisiNama = isManualEkspedisi.value ? formPengantaran.ekspedisi_nama : namaEkspedisiAsli.toUpperCase();

    formPengantaran.transform((data) => ({
        ...data,
        ekspedisi_nama: finalEkspedisiNama,
        ekspedisi_layanan: data.ekspedisi_nama === 'Ambil di Toko' ? 'Ambil Sendiri' : data.ekspedisi_layanan,
        harga_ongkir: data.ekspedisi_nama === 'Ambil di Toko' ? 0 : data.harga_ongkir,
        ekspedisi_estimasi: data.ekspedisi_nama === 'Ambil di Toko' ? '0' : data.ekspedisi_estimasi,
    })).post(route('produksi.pengantaran.proses', selectedPengantaran.value.id_pesan), {
        onSuccess: () => {
            closePengantaranModal();
            alertStore.show('Pesanan berhasil masuk ke Pengantaran!', 'success');
        },
        onError: () => alertStore.show('Gagal memproses pesanan.', 'error')
    });
};

// --- CONTROLLER JALUR PENGANTARAN (PENGATUR CABANG) ---
const handleProsesPengantaran = (pesanan) => {
    const hasCustomItem = pesanan.pesanan_item.some(item => item.id_sku === 'PRD-0001-SKU-001');

    if (hasCustomItem) {
        openModalBerat(pesanan);
    } else {
        selectedKirimId.value = pesanan.id_pesan;
        formKirim.nomor_resi = pesanan.nomor_resi || '';
        isConfirmKirimOpen.value = true;
    }
};
</script>

<template>
    <Head title="Dashboard Produksi" />
    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Produksi & Alokasi
                    </h2>
                    <p class="mt-1 text-sm text-base-content/60">Pantau antrean, pecah tugas ke vendor, dan perbarui progres.</p>
                </div>

                <CustomButton type="link" :href="route('produksi.histori')">
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </template>
                    Histori Produksi
                </CustomButton>
            </div>
        </template>

        <div class="px-4 py-8 mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">

            <div v-if="pesananProduksi.length === 0" class="flex flex-col items-center justify-center py-24 text-center border rounded-lg border-base-200 bg-base-100">
                <Inbox class="w-12 h-12 mb-4 text-base-content/20" />
                <h3 class="text-base font-semibold text-base-content">Antrean Kosong</h3>
                <p class="mt-1 text-sm text-base-content/50">Belum ada pesanan yang perlu diproses produksi.</p>
            </div>

            <div v-for="pesanan in pesananProduksi" :key="pesanan.id_pesan" class="overflow-hidden border shadow-sm rounded-xl border-base-200 bg-base-100">
                <div class="flex flex-col items-start justify-between gap-4 p-5 border-b sm:flex-row sm:items-center border-base-200 bg-base-50/30">
                    <div class="flex items-center gap-4">
                        <div v-if="currentUser?.role !== 'vendor'" class="px-3 py-1.5 border rounded-md border-base-300 bg-base-100 flex flex-col items-center justify-center">
                            <span class="text-[10px] font-medium text-base-content/50 uppercase">ID Pesan</span>
                            <span class="text-sm font-bold text-base-content">{{ pesanan.id_pesan }}</span>
                        </div>
                        <div>
                            <h3 v-if="currentUser?.role !== 'vendor'" class="text-base font-semibold text-base-content">{{ pesanan.customer?.user?.name }}</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="flex items-center gap-1.5 text-xs font-medium px-2 py-0.5 rounded-full border"
                                    :class="pesanan.status_operasional === 'menunggu_diproses' ? 'border-base-300 text-base-content/70' : 'border-blue-200 text-blue-600'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="pesanan.status_operasional === 'menunggu_diproses' ? 'bg-base-content/40' : 'bg-blue-600'"></span>
                                    {{ pesanan.status_operasional === 'menunggu_diproses' ? 'Menunggu Alokasi' : 'Sedang Diproses' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 sm:items-end">
                        <div class="flex items-center gap-2 text-sm">
                            <Clock class="w-4 h-4 text-base-content/40" />
                            <span class="text-base-content/60">Deadline:</span>
                            <span class="font-semibold" :class="isDeadlinePassed(pesanan.waktu_deadline) ? 'text-red-600' : 'text-base-content'">
                                {{ formatTanggal(pesanan.waktu_deadline) }}
                            </span>
                        </div>

                        <!-- Tombol Cetak Label & Nota (Disembunyikan dari Vendor) -->
                        <div class="flex items-center gap-2 mt-1" v-if="currentUser?.role !== 'vendor'">
                            <a :href="route('pesan.cetakLabel', pesanan.id_pesan)" target="_blank" class="font-medium btn btn-xs btn-outline hover:bg-base-200 hover:text-base-content hover:border-base-300 border-base-300 text-base-content/70">
                                <Printer class="w-3 h-3" /> Label
                            </a>
                            <a :href="route('pesan.cetakNota', pesanan.id_pesan)" target="_blank" class="font-medium btn btn-xs btn-outline hover:bg-base-200 hover:text-base-content hover:border-base-300 border-base-300 text-base-content/70">
                                <Printer class="w-3 h-3" /> Nota
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-5">
                    <div v-if="pesanan.status_operasional === 'menunggu_diproses'">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs border-b-2 text-base-content/50 border-base-300">
                                <tr>
                                    <th class="w-1/3 pb-3 font-medium">Item Produk</th>
                                    <th class="pb-3 font-medium">Spesifikasi / Catatan</th>
                                    <th class="w-24 pb-3 font-medium text-right">Kuantitas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-base-100">
                                <tr v-for="item in pesanan.pesanan_item" :key="item.id" class="group">
                                    <td class="py-4 font-medium align-top">
                                        <span class="font-semibold capitalize">{{ cleanProductName(item.nama_produk_snapshot) }}</span>

                                        <div v-if="getValidAttributes(item.atribut_custom_snapshot).length > 0" class="mt-1 text-[10px] font-bold text-primary leading-relaxed flex flex-wrap gap-1">
                                            <span v-for="(attr, idx) in getValidAttributes(item.atribut_custom_snapshot)" :key="attr.key">
                                                <span v-if="idx > 0" class="mx-1 opacity-40 text-base-content">|</span>
                                                <span class="opacity-70">{{ attr.key }}:</span> {{ attr.value }}
                                            </span>
                                        </div>

                                        <div v-if="item.id_sku?.startsWith('PRD-0002')" class="inline-flex mt-1.5 items-center gap-1 text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">
                                            Auto In-House
                                        </div>

                                        <div v-if="item.pesanan_item_finishing?.length" class="flex flex-col gap-0.5 mt-2 mb-2">
                                            <div v-for="(fin, fIdx) in item.pesanan_item_finishing" :key="'fin'+fIdx" class="flex items-start gap-1">
                                                <span class="mt-px opacity-50">▸</span>
                                                <span class="font-medium text-base-content">{{ fin.nama_finishing_snapshot }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 text-xs align-top text-base-content/70">
                                        <div v-if="getFileDisplay(item)" class="mb-2">
                                            <template v-if="getFileDisplay(item).tipe === 'upload'">
                                                <a v-if="item.file_desain" :href="'/storage/' + getFileDisplay(item).nilai" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 hover:underline bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                                                    📁 Download File
                                                </a>
                                                <span v-else class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 max-w-40 truncate" :title="getFileDisplay(item).nilai">
                                                    📁 {{ getFileDisplay(item).nilai }}
                                                </span>
                                            </template>
                                            <template v-else-if="getFileDisplay(item).tipe === 'link'">
                                                <a :href="getFileDisplay(item).nilai.startsWith('http') ? getFileDisplay(item).nilai : 'https://' + getFileDisplay(item).nilai" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-purple-600 hover:underline bg-purple-50 px-2 py-0.5 rounded border border-purple-100 max-w-40 truncate">
                                                    🔗 GDrive Link
                                                </a>
                                            </template>
                                            <template v-else-if="getFileDisplay(item).tipe === 'email'">
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-base-content/60 bg-base-200 px-2 py-0.5 rounded border border-base-300">
                                                    📧 Tunggu Email Customer
                                                </span>
                                            </template>
                                        </div>
                                        <div v-else class="mb-2 inline-flex items-center gap-1 text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-100">
                                            ❌ File Belum Ada
                                        </div>
                                        <p class="text-[11px] italic opacity-80 leading-tight border-l-2 border-base-300 pl-2 mt-1">"{{ item.catatan ?? "Tidak ada Catatan" }}"</p>
                                    </td>
                                    <td class="py-4 font-semibold text-right align-top">{{ item.jumlah }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="flex justify-end mt-5" v-if="currentUser?.role !== 'vendor'">
                            <button v-if="$can('produksi', 'ubah')" @click="openAlokasiModal(pesanan)" class="px-6 font-medium btn btn-sm btn-neutral">
                                Alokasikan Pengerjaan
                            </button>
                        </div>
                    </div>

                    <div v-if="pesanan.status_operasional === 'proses_pengerjaan'" class="space-y-6">
                        <div v-for="item in pesanan.pesanan_item" :key="item.id" class="overflow-hidden border rounded-lg shadow-sm border-base-200">
                            <div class="flex flex-col gap-4 p-4 border-b bg-base-50/50 border-base-200 sm:flex-row">
                                <div class="sm:w-1/3">
                                    <span class="text-[10px] font-bold text-base-content/50 uppercase tracking-widest block mb-1.5">Item Produk</span>

                                    <h4 class="text-sm font-semibold capitalize text-base-content">{{ cleanProductName(item.nama_produk_snapshot) }}</h4>

                                    <div v-if="getValidAttributes(item.atribut_custom_snapshot).length > 0" class="mt-1 text-[10px] font-bold text-primary leading-relaxed flex flex-wrap gap-1 mb-2">
                                        <span v-for="(attr, idx) in getValidAttributes(item.atribut_custom_snapshot)" :key="attr.key">
                                            <span v-if="idx > 0" class="mx-1 opacity-40 text-base-content">|</span>
                                            <span class="opacity-70">{{ attr.key }}:</span> {{ attr.value }}
                                        </span>
                                    </div>

                                    <div v-if="item.pesanan_item_finishing?.length" class="flex flex-col gap-0.5 mb-2 mt-1">
                                        <div v-for="(fin, fIdx) in item.pesanan_item_finishing" :key="'fin'+fIdx" class="flex items-start gap-1">
                                            <span class="mt-px text-xs opacity-50">▸</span>
                                            <span class="text-xs font-medium text-base-content">{{ fin.nama_finishing_snapshot }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="sm:flex-1">
                                    <span class="text-[10px] font-bold text-base-content/50 uppercase tracking-widest block mb-1.5">Spesifikasi / Catatan</span>
                                    <div v-if="getFileDisplay(item)" class="mb-2">
                                        <template v-if="getFileDisplay(item).tipe === 'upload'">
                                            <a v-if="item.file_desain" :href="'/storage/' + getFileDisplay(item).nilai" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 hover:underline bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                                                📁 Download File
                                            </a>
                                            <span v-else class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 max-w-40 truncate" :title="getFileDisplay(item).nilai">
                                                📁 {{ getFileDisplay(item).nilai }}
                                            </span>
                                        </template>
                                        <template v-else-if="getFileDisplay(item).tipe === 'link'">
                                            <a :href="getFileDisplay(item).nilai.startsWith('http') ? getFileDisplay(item).nilai : 'https://' + getFileDisplay(item).nilai" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-purple-600 hover:underline bg-purple-50 px-2 py-0.5 rounded border border-purple-100 max-w-40 truncate">
                                                🔗 GDrive Link
                                            </a>
                                        </template>
                                        <template v-else-if="getFileDisplay(item).tipe === 'email'">
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-base-content/60 bg-base-200 px-2 py-0.5 rounded border border-base-300">
                                                📧 Tunggu Email Customer
                                            </span>
                                        </template>
                                    </div>
                                    <div v-else class="mb-2 inline-flex items-center gap-1 text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-100">
                                        ❌ File Belum Ada
                                    </div>
                                    <p class="text-[11px] italic opacity-80 leading-tight border-l-2 border-base-300 pl-2 mt-1">"{{ item.catatan ?? "Tidak ada Catatan" }}"</p>
                                </div>
                                <div class="sm:w-24 sm:text-right">
                                    <span class="text-[10px] font-bold text-base-content/50 uppercase tracking-widest block mb-1.5">Kuantitas</span>
                                    <span class="font-semibold text-base-content">{{ item.jumlah }}</span>
                                </div>
                            </div>

                            <CustomTable :headers="headersProses" class="border-none shadow-none">
                                <tr v-for="schedule in item.pesanan_item_produksi" :key="schedule.id" class="transition-colors border-b hover:bg-base-200/30 border-base-200/50">
                                    <td class="px-4 py-3 text-xs font-medium">
                                        {{ schedule.tipe_pengerjaan === 'sendiri' ? 'In-House' : (schedule.vendor?.nama_vendor || 'Vendor Eksternal') }}
                                        <div v-if="schedule.file_revisi" class="mt-1">
                                            <a :href="'/storage/' + schedule.file_revisi" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 hover:underline bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">
                                                ✅ Hasil File
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-base-content/70">
                                        {{ schedule.instruksi_pengerjaan || '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs font-semibold text-center">
                                        {{ schedule.qty_dikerjakan }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button v-if="schedule.status_pengerjaan === 'selesai'"
                                            @click="openUpdateModal(schedule, item)"
                                            class="inline-flex justify-center items-center gap-1.5 text-xs font-medium transition-all text-green-600 hover:text-green-700 hover:underline cursor-pointer"
                                            title="Lihat / Edit Laporan"
                                        >
                                            <CheckCircle class="w-3.5 h-3.5" /> Selesai
                                        </button>
                                        <span v-else class="inline-flex justify-center items-center gap-1.5 text-xs font-medium text-base-content/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-base-content/30"></span> Proses
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button v-if="schedule.status_pengerjaan !== 'selesai'"
                                            @click="openUpdateModal(schedule, item)"
                                            class="text-xs font-medium transition-colors"
                                            :class="checkAccess(schedule) === 'edit' ? 'text-blue-600 hover:underline' : 'text-base-content/30 cursor-not-allowed'"
                                            :title="checkAccess(schedule) === 'edit' ? 'Proses pesanan' : 'Akses dibatasi'"
                                            :disabled="checkAccess(schedule) !== 'edit'"
                                        >
                                            Update
                                        </button>
                                    </td>
                                </tr>
                            </CustomTable>
                        </div>

                        <!-- Panel Pengantaran yang sudah DIKONDISIKAN -->
                        <div class="flex items-center justify-between pt-5 mt-6 border-t border-base-200" v-if="currentUser?.role !== 'vendor'">
                            <p class="text-xs text-base-content/50">
                                Penyelesaian order baru dapat dilakukan setelah semua item berstatus selesai.
                            </p>
                            <button
                                v-if="$can('produksi', 'ubah')"
                                @click="handleProsesPengantaran(pesanan)"
                                :disabled="!isReadyToShip(pesanan)"
                                class="px-6 font-medium btn btn-sm"
                                :class="isReadyToShip(pesanan) ? 'btn-neutral' : 'btn-disabled bg-base-200 text-base-content/40'"
                            >
                                <Truck class="w-4 h-4 mr-1.5" />
                                Proses Pengantaran
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StafLayout>

    <!-- MODAL ALOKASI -->
    <dialog class="modal" :class="{'modal-open': isAlokasiModalOpen}">
        <div class="max-w-4xl p-0 modal-box rounded-xl">
            <div class="flex items-center justify-between p-5 border-b border-base-200">
                <div>
                    <h3 class="text-base font-semibold">Alokasi Pengerjaan</h3>
                    <p class="text-sm text-base-content/50 mt-0.5">Tentukan pelaksana (In-house/Vendor) untuk pesanan {{ selectedOrderAlokasi?.id_pesan }}</p>
                </div>
                <button @click="closeAlokasiModal" class="text-base-content/40 hover:text-base-content">✕</button>
            </div>
            <div class="p-5 max-h-[70vh] overflow-y-auto">
                <div v-if="alokasiForm.alokasi.filter(i => !i.is_desain).length === 0" class="py-6 text-sm text-center text-base-content/60">
                    Semua item dalam pesanan ini akan otomatis dialokasikan ke pengerjaan In-House (Desain/Custom).<br>Silakan klik "Simpan Alokasi" untuk melanjutkan.
                </div>
                <form @submit.prevent="submitAlokasi" class="space-y-8">
                    <template v-for="(item, itemIndex) in alokasiForm.alokasi" :key="item.id_pesanan_item">
                        <div v-show="!item.is_desain">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-medium capitalize">{{ cleanProductName(item.nama_produk) }}</h4>
                                <span class="text-xs text-base-content/60">Target: <span class="font-semibold text-base-content">{{ item.total_qty }}</span></span>
                            </div>
                            <div class="space-y-3">
                                <div v-for="(skema, skemaIndex) in item.skema" :key="skemaIndex" class="flex flex-col items-start gap-3 sm:flex-row sm:items-end">
                                    <div class="w-full sm:w-1/4">
                                        <label class="block mb-1 text-xs font-medium text-base-content/70">Pelaksana</label>
                                        <select v-model="skema.tipe_pengerjaan" class="w-full font-medium select select-sm select-bordered">
                                            <option value="sendiri">In-House</option>
                                            <option value="vendor">Vendor</option>
                                        </select>
                                    </div>
                                    <div v-if="skema.tipe_pengerjaan === 'vendor'" class="w-full sm:w-1/4">
                                        <label class="block mb-1 text-xs font-medium text-base-content/70">Pilih Vendor</label>
                                        <select v-model="skema.id_vendor" required class="w-full font-medium select select-sm select-bordered">
                                            <option :value="null" disabled>Pilih...</option>
                                            <option v-for="v in vendors" :key="v.id_vendor" :value="v.id_vendor">{{ v.nama_vendor }}</option>
                                        </select>
                                    </div>
                                    <div class="w-full sm:w-24">
                                        <label class="block mb-1 text-xs font-medium text-base-content/70">Qty</label>
                                        <input type="number" v-model="skema.qty_dikerjakan" required min="1" class="w-full input input-sm input-bordered" />
                                    </div>
                                    <div class="flex-1 w-full">
                                        <label class="block mb-1 text-xs font-medium text-base-content/70">Instruksi</label>
                                        <input type="text" v-model="skema.instruksi_pengerjaan" placeholder="Opsional..." class="w-full input input-sm input-bordered" />
                                    </div>
                                    <div class="pb-0.5" v-if="item.skema.length > 1">
                                        <button type="button" @click="removeSkema(itemIndex, skemaIndex)" class="text-red-500 btn btn-sm btn-square btn-ghost hover:bg-red-50">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" @click="addSkema(itemIndex)" class="flex items-center gap-1 mt-3 text-xs font-medium text-base-content/60 hover:text-base-content">
                                <Plus class="w-3.5 h-3.5" /> Tambah Pelaksana
                            </button>
                            <div v-if="itemIndex !== alokasiForm.alokasi.length - 1" class="mt-6 border-b border-base-200"></div>
                        </div>
                    </template>
                </form>
            </div>
            <div class="flex justify-end gap-3 p-5 border-t border-base-200 bg-base-50/50 rounded-b-xl">
                <button type="button" @click="closeAlokasiModal" class="font-medium btn btn-sm btn-ghost">Batal</button>
                <button type="button" @click="submitAlokasi" :disabled="alokasiForm.processing" class="px-6 font-medium btn btn-sm btn-neutral">Simpan Alokasi</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop bg-base-content/20"><button @click="closeAlokasiModal">close</button></form>
    </dialog>

    <!-- MODAL UPDATE PROGRESS & LIHAT DATA -->
    <dialog class="modal" :class="{'modal-open': isUpdateModalOpen}">
        <div class="max-w-lg p-6 modal-box rounded-xl">
            <h3 class="mb-1 text-base font-semibold">
                {{ isViewOnly ? 'Rincian Laporan Pengerjaan' : (selectedSchedule?.status_pengerjaan === 'selesai' ? 'Edit Laporan Pengerjaan' : 'Perbarui Status Pengerjaan') }}
            </h3>
            <p v-if="!isViewOnly" class="mb-6 text-sm text-base-content/50">Tandai tugas ini sebagai selesai dan isi laporan pengerjaan.</p>
            <p v-else class="mb-6 text-sm text-base-content/50">Berikut adalah hasil laporan untuk item ini.</p>
            <form @submit.prevent="submitUpdate" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-base-content mb-1.5">Laporan Pengerjaan <span v-if="!isViewOnly" class="text-red-500">*</span></label>
                    <textarea v-model="updateForm.deskripsi_pengerjaan" :disabled="isViewOnly" :required="!isViewOnly" class="w-full h-24 textarea textarea-bordered disabled:bg-base-200 disabled:text-base-content/70 disabled:cursor-not-allowed" placeholder="Tulis rincian hasil pengerjaan..."></textarea>
                </div>
                <div v-if="selectedSchedule?.tipe_pengerjaan === 'sendiri' && selectedItemUpdate?.id_sku?.startsWith('PRD-0002')" class="p-4 space-y-4 border rounded-lg border-base-200 bg-base-50/50">
                    <div class="flex items-center gap-2 text-sm font-medium text-base-content">
                        <UploadCloud class="w-4 h-4 text-base-content/50" /> File Hasil Produksi / Desain
                    </div>
                    <div>
                        <div v-if="isViewOnly">
                            <a v-if="selectedSchedule?.file_revisi" :href="'/storage/' + selectedSchedule.file_revisi" target="_blank" class="flex justify-center w-full gap-2 font-medium btn btn-sm btn-outline border-emerald-200 text-emerald-700 hover:bg-emerald-50 hover:border-emerald-300">
                                <span>✅</span> Download File Hasil Desain
                            </a>
                            <p v-else class="py-2 text-xs italic text-center text-base-content/50">
                                Tidak ada file hasil yang dilampirkan.
                            </p>
                        </div>
                        <CustomInputFile
                            v-else
                            v-model="fileHasilObj"
                            @update:modelValue="updateForm.hasil_desain = $event.file"
                            :error="updateForm.errors?.hasil_desain"
                            :disabled="isViewOnly"
                            label="Upload File Siap Cetak / Mockup (Opsional)"
                            :show-tipe-file="false"
                        />
                    </div>
                </div>
                <div v-if="selectedSchedule?.tipe_pengerjaan === 'vendor'" class="p-4 space-y-4 border rounded-lg border-base-200 bg-base-50/50">
                    <div class="flex items-center gap-2 text-sm font-medium text-base-content">
                        <Paperclip class="w-4 h-4 text-base-content/50" /> Detail Penagihan Vendor
                    </div>
                    <CustomInput v-model="updateForm.total_tagihan_vendor" type="number" label="Nominal Tagihan (Rp)" placeholder="0" :disabled="isViewOnly" />
                    <div>
                        <div v-if="isViewOnly">
                            <a v-if="selectedSchedule?.file_nota" :href="'/storage/' + selectedSchedule.file_nota" target="_blank" class="flex justify-center w-full gap-2 mt-1 font-medium text-blue-700 border-blue-200 btn btn-sm btn-outline hover:bg-blue-50 hover:border-blue-300">
                                <span>📁</span> Download Nota Vendor
                            </a>
                            <p v-else class="py-2 mt-1 text-xs italic text-center text-base-content/50">
                                Tidak ada nota yang dilampirkan.
                            </p>
                        </div>
                        <CustomInputFile
                            v-else
                            v-model="fileNotaObj"
                            @update:modelValue="updateForm.file_nota = $event.file"
                            :error="updateForm.errors?.file_nota"
                            :disabled="isViewOnly"
                            label="File Nota Tagihan"
                            :show-tipe-file="false"
                        />
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="closeUpdateModal" class="font-medium btn btn-sm btn-ghost">
                        {{ isViewOnly ? 'Tutup' : 'Batal' }}
                    </button>
                    <button v-if="!isViewOnly" type="submit" :disabled="updateForm.processing" class="px-6 font-medium btn btn-sm btn-neutral">
                        {{ selectedSchedule?.status_pengerjaan === 'selesai' ? 'Simpan Perubahan' : 'Tandai Selesai' }}
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop bg-base-content/20"><button @click="closeUpdateModal">close</button></form>
    </dialog>


    <!-- FORM 1: MODAL UPDATE BERAT (KHUSUS ADA PRD-0001-SKU-001) -->
    <dialog class="modal" :class="{'modal-open': isModalBeratOpen}">
        <div class="flex flex-col max-w-lg p-0 overflow-hidden modal-box rounded-xl">
            <div class="flex items-center justify-between p-5 border-b border-base-200">
                <div>
                    <h3 class="text-base font-semibold">Tentukan Berat Item Custom</h3>
                    <p class="text-sm text-base-content/50 mt-0.5">Berat harus disimpan sebelum cek ongkir.</p>
                </div>
                <button @click="closeModalBerat" class="text-base-content/40 hover:text-base-content">✕</button>
            </div>

            <div class="p-5 max-h-[70vh] overflow-y-auto space-y-4 bg-base-50/50">
                <div v-if="selectedPengantaran?.ekspedisi_nama" class="flex items-start gap-3 p-4 mb-2 border border-blue-100 rounded-lg shadow-sm bg-blue-50">
                    <Truck class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" />
                    <div>
                        <p class="text-[10px] font-bold text-blue-500 uppercase tracking-widest mb-1">Ekspedisi Pilihan Customer</p>
                        <p class="text-sm font-bold text-blue-900">
                            {{ selectedPengantaran.ekspedisi_nama }}
                            <span v-if="selectedPengantaran.ekspedisi_layanan">- {{ selectedPengantaran.ekspedisi_layanan }}</span>
                        </p>
                        <p v-if="selectedPengantaran.harga_ongkir" class="text-xs text-blue-700 font-medium mt-0.5">
                            Tarif Ongkir Awal: Rp {{ Number(selectedPengantaran.harga_ongkir).toLocaleString('id-ID') }}
                        </p>
                    </div>
                </div>
                <form @submit.prevent="submitBerat" class="space-y-4">
                    <div v-for="(item, index) in formBerat.items" :key="index" class="p-4 border rounded-lg shadow-sm bg-base-100 border-base-200">
                        <div class="mb-2">
                            <h4 class="text-sm font-black capitalize">{{ item.nama_produk }}</h4>
                        </div>
                        <div class="w-full form-control">
                            <label class="pb-1 label">
                                <span class="text-xs font-bold uppercase label-text opacity-60">Total Berat (Gram)</span>
                            </label>
                            <div class="relative flex items-center">
                                <input type="number" v-model="formBerat.items[index].berat" required min="1" class="w-full pr-10 font-bold input input-bordered" placeholder="Contoh: 150000" />
                                <span class="absolute text-xs font-black pointer-events-none right-4 text-base-content/40">g</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="flex justify-end gap-3 p-5 border-t border-base-200">
                <button type="button" @click="closeModalBerat" class="font-medium btn btn-sm btn-ghost">Batal</button>
                <button type="button" @click="submitBerat" :disabled="formBerat.processing" class="px-6 font-black btn btn-sm btn-primary">
                    <span v-if="formBerat.processing" class="loading loading-spinner loading-xs"></span>
                    Simpan Berat & Lanjut
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop bg-base-content/20"><button @click="closeModalBerat">close</button></form>
    </dialog>


    <!-- FORM 2: MODAL PENGANTARAN & CEK ONGKIR -->
    <dialog class="modal" :class="{'modal-open': isPengantaranModalOpen}">
        <div class="flex flex-col max-w-xl p-0 overflow-hidden modal-box rounded-xl">
            <div class="flex items-center justify-between p-5 border-b border-base-200">
                <div>
                    <h3 class="text-base font-semibold">Proses Pengantaran</h3>
                    <p class="text-sm text-base-content/50 mt-0.5">Pilih ekspedisi dan hitung ongkir pesanan {{ selectedPengantaran?.id_pesan }}</p>
                </div>
                <button @click="closePengantaranModal" class="text-base-content/40 hover:text-base-content">✕</button>
            </div>

            <div class="p-5 max-h-[70vh] overflow-y-auto space-y-6">
                <div class="grid grid-cols-1 gap-4 p-4 border sm:grid-cols-2 bg-base-200/30 rounded-xl border-base-200">
                    <div class="col-span-1 sm:col-span-2">
                        <CustomSelect v-model="formPengantaran.ekspedisi_nama" label="Kurir / Ekspedisi" :options="ekspedisiOptions" valueKey="id" labelKey="nama" />
                    </div>

                    <div v-if="formPengantaran.ekspedisi_nama !== 'Ambil di Toko'" class="col-span-1 space-y-4 sm:col-span-2">
                        <template v-if="isManualEkspedisi">
                            <CustomSelect v-model="formPengantaran.ekspedisi_layanan" label="Layanan Lokal" :options="manualLayananOptions" valueKey="id" labelKey="nama" placeholder="Pilih Instan/Lokal..." />
                        </template>

                        <template v-else>
                            <div v-if="isLoadingOngkir" class="flex items-center justify-center gap-2 h-11 border border-base-300 bg-base-100 rounded-xl text-[11px] font-bold text-primary animate-pulse">
                                <span class="loading loading-spinner loading-xs"></span> Mengkalkulasi Tarif dari Database...
                            </div>
                            <div v-else class="flex flex-col gap-2">
                                <CustomSelect v-model="formPengantaran.ekspedisi_layanan" label="Layanan Ongkir" :options="layananOptions" valueKey="id" labelKey="nama" placeholder="Pilih Layanan Ekspedisi..." />
                                <button type="button" @click="fetchOngkir" class="text-[10px] text-blue-500 font-bold hover:underline self-end">
                                    ↻ Hitung Ulang Tarif
                                </button>
                            </div>
                        </template>

                        <div>
                            <label class="text-[10px] font-bold uppercase opacity-70 block mb-1">Total Biaya Ongkir</label>
                            <div class="relative flex items-center">
                                <span class="absolute text-xs font-black left-4 text-base-content/50">Rp</span>
                                <input
                                    type="number"
                                    v-model="formPengantaran.harga_ongkir"
                                    class="w-full pl-10 pr-4 text-sm font-black transition-all border outline-none h-11 bg-base-100 rounded-xl border-base-300 focus:border-primary"
                                    placeholder="0"
                                    :readonly="!isManualEkspedisi"
                                />
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center col-span-1 p-4 border border-dashed sm:col-span-2 rounded-xl border-base-300 opacity-60 bg-base-100">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-center leading-relaxed">Pesanan akan diambil<br>langsung di Toko</span>
                    </div>

                    <!-- Input Nomor Resi untuk Alur Custom -->
                    <div class="col-span-1 pt-2 mt-2 border-t sm:col-span-2 border-base-200">
                        <CustomInput
                            v-model="formPengantaran.nomor_resi"
                            label="Nomor Resi / Kurir (Opsional)"
                            placeholder="Contoh: JX1234567890 / Budi Gojek"
                        />
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 p-5 border-t border-base-200 bg-base-50/50">
                <button type="button" @click="closePengantaranModal" class="font-medium btn btn-sm btn-ghost">Batal</button>
                <button type="button" @click="submitPengantaran" :disabled="formPengantaran.processing || isLoadingOngkir" class="px-6 font-medium btn btn-sm btn-neutral">
                    <span v-if="formPengantaran.processing" class="loading loading-spinner loading-xs"></span>
                    Simpan & Proses Pengantaran
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop bg-base-content/20"><button @click="closePengantaranModal">close</button></form>
    </dialog>

    <!-- FORM 3: MODAL KIRIM REGULER (LANGSUNG ISI RESI) -->
    <dialog class="modal" :class="{'modal-open': isConfirmKirimOpen}">
        <div class="max-w-sm p-6 modal-box rounded-xl">
            <h3 class="mb-1 text-base font-semibold">Proses Pengantaran</h3>
            <p class="mb-6 text-sm text-base-content/50">Pastikan semua item telah selesai. Silakan masukkan nomor resi atau nama kurir jika sudah ada.</p>

            <form @submit.prevent="executeKirimPesanan" class="space-y-4">
                <CustomInput
                    v-model="formKirim.nomor_resi"
                    label="Nomor Resi / Kurir (Opsional)"
                    placeholder="Contoh: JX1234567890 / Budi Gojek"
                />

                <div class="flex justify-end gap-3 pt-4 mt-6 border-t border-base-200">
                    <button type="button" @click="closeKirimModal" class="font-medium btn btn-sm btn-ghost">Batal</button>
                    <button type="submit" :disabled="formKirim.processing" class="px-6 font-medium btn btn-sm btn-primary">
                        <span v-if="formKirim.processing" class="loading loading-spinner loading-xs"></span>
                        Ya, Lanjutkan
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop bg-base-content/20"><button @click="closeKirimModal">close</button></form>
    </dialog>

</template>
