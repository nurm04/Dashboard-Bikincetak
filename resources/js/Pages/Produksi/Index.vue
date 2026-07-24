<script setup>
import { ref } from 'vue';
import { alertStore } from '@/Utils/alertStore';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { Clock, CheckCircle, Truck, Inbox, Plus, Trash2, Paperclip } from 'lucide-vue-next';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomInputFile from '@/Components/Form/CustomInputFile.vue'
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';

const page = usePage();
const currentUser = page.props.auth?.user;

const props = defineProps({
    pesananProduksi: Array,
    vendors: Array,
    currentVendorId: String,
});

const formatDateTime = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
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

const headersProses = ['Pelaksana', 'Instruksi / Keterangan', 'Qty', 'Status', 'Aksi'];

// ==========================================
// LOGIC AUTORISASI EDIT
// ==========================================
const checkAccess = (schedule) => {
    const role = currentUser?.role;
    const isAdmin = role === 'admin' || role === 'administrator';

    if (isAdmin) return 'edit';

    // Jika yang login adalah Vendor
    if (role === 'vendor') {
        // Cek ID vendornya dengan ID dari tabel (menggunakan prop currentVendorId)
        if (schedule.tipe_pengerjaan === 'vendor' && schedule.id_vendor === props.currentVendorId) {
            return 'edit';
        }
        return 'none';
    }

    // Jika yang login Staf / Tim Produksi
    if (schedule.tipe_pengerjaan === 'sendiri') {
        return schedule.status_pengerjaan === 'selesai' ? 'view' : 'edit';
    }

    if (schedule.tipe_pengerjaan === 'vendor') {
        return schedule.status_pengerjaan === 'selesai' ? 'view' : 'none';
    }

    return 'none';
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
            alertStore.show(`Total alokasi Qty untuk ${item.nama_produk} (${totalInput}) tidak sama dengan pesanan (${item.total_qty})!`, 'warning');
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
const isViewOnly = ref(false);

const updateForm = useForm({
    deskripsi_pengerjaan: '',
    total_tagihan_vendor: null,
    file_nota: null,
});

const fileNotaObj = ref({ tipe_file: 'upload', file: null, link_file: '' });

const openUpdateModal = (schedule) => {
    const access = checkAccess(schedule);

    if (access === 'none') {
        alertStore.show('Akses Ditolak! Anda tidak memiliki izin untuk mengelola item ini.', 'error');
        return;
    }

    isViewOnly.value = access === 'view';
    selectedSchedule.value = schedule;
    updateForm.deskripsi_pengerjaan = schedule.deskripsi_pengerjaan || '';
    updateForm.total_tagihan_vendor = schedule.total_tagihan_vendor || null;

    updateForm.file_nota = null;
    fileNotaObj.value = { tipe_file: 'upload', file: schedule.file_nota || null, link_file: '' };

    isUpdateModalOpen.value = true;
};

const closeUpdateModal = () => {
    isUpdateModalOpen.value = false;
    selectedSchedule.value = null;
    isViewOnly.value = false;
    updateForm.reset();
};

const submitUpdate = () => {
    updateForm.post(route('produksi.selesaikan', selectedSchedule.value.id), {
        onSuccess: () => {
            closeUpdateModal();
            alertStore.show('Progress item berhasil diperbarui!', 'success');
        },
        onError: () => alertStore.show('Gagal memperbarui progress.', 'error')
    });
};

// ==========================================
// LOGIC KONTROL PENGIRIMAN
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

const isConfirmKirimOpen = ref(false);
const selectedKirimId = ref(null);
const isKirimLoading = ref(false);

const kirimPesanan = (id_pesan) => {
    selectedKirimId.value = id_pesan;
    isConfirmKirimOpen.value = true;
};

const executeKirimPesanan = () => {
    if (!selectedKirimId.value) return;

    isKirimLoading.value = true;
    router.post(route('produksi.kirim', selectedKirimId.value), {}, {
        onSuccess: () => {
            alertStore.show('Pesanan masuk ke status Pengantaran!', 'success');
            isConfirmKirimOpen.value = false;
            selectedKirimId.value = null;
        },
        onError: () => {
            alertStore.show('Gagal mengubah status pesanan.', 'error');
        },
        onFinish: () => {
            isKirimLoading.value = false;
        }
    });
};
</script>

<template>
    <CustomAlertConfirm
        :show="isConfirmKirimOpen"
        type="warning"
        title="Proses Pengantaran?"
        message="Pastikan semua item telah selesai dan siap dikirim/diambil. Lanjutkan?"
        confirmText="Ya, Lanjutkan"
        cancelText="Batal"
        :loading="isKirimLoading"
        @close="isConfirmKirimOpen = false"
        @confirm="executeKirimPesanan"
    />

    <Head title="Dashboard Produksi" />
    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Produksi & Alokasi
                    </h2>
                    <p class="text-sm text-base-content/60 mt-1">Pantau antrean, pecah tugas ke vendor, dan perbarui progres.</p>
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

        <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

            <!-- EMPTY STATE -->
            <div v-if="pesananProduksi.length === 0" class="flex flex-col items-center justify-center py-24 text-center border rounded-lg border-base-200 bg-base-100">
                <Inbox class="w-12 h-12 text-base-content/20 mb-4" />
                <h3 class="text-base font-semibold text-base-content">Antrean Kosong</h3>
                <p class="text-sm text-base-content/50 mt-1">Belum ada pesanan yang perlu diproses produksi.</p>
            </div>

            <!-- ORDER CARDS -->
            <div v-for="pesanan in pesananProduksi" :key="pesanan.id_pesan" class="border rounded-xl border-base-200 bg-base-100 shadow-sm overflow-hidden">

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-5 border-b border-base-200 gap-4 bg-base-50/30">
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

                    <div class="flex items-center gap-2 text-sm">
                        <Clock class="w-4 h-4 text-base-content/40" />
                        <span class="text-base-content/60">Deadline:</span>
                        <span class="font-semibold" :class="isDeadlinePassed(pesanan.waktu_deadline) ? 'text-red-600' : 'text-base-content'">
                            {{ formatDateTime(pesanan.waktu_deadline) }}
                        </span>
                    </div>
                </div>

                <div class="p-5">

                    <!-- KONDISI 1: MENUNGGU ALOKASI -->
                    <div v-if="pesanan.status_operasional === 'menunggu_diproses'">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-base-content/50 border-b-2 border-base-300">
                                <tr>
                                    <th class="pb-3 font-medium w-1/3">Item Produk</th>
                                    <th class="pb-3 font-medium">Spesifikasi / Catatan</th>
                                    <th class="pb-3 font-medium text-right w-24">Kuantitas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-base-100">
                                <tr v-for="item in pesanan.pesanan_item" :key="item.id" class="group">
                                    <td class="py-4 font-medium align-top">
                                        {{ item.nama_produk_snapshot }}
                                        <div v-if="item.pesanan_item_finishing?.length" class="flex flex-col gap-0.5 mb-2">
                                            <div v-for="(fin, fIdx) in item.pesanan_item_finishing" :key="'fin'+fIdx" class="flex items-start gap-1">
                                                <span class="opacity-50 mt-px">▸</span>
                                                <span class="font-medium text-base-content">{{ fin.nama_finishing_snapshot }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 text-base-content/70 text-xs align-top">
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

                        <!-- Hanya tampilkan ini jika BUKAN VENDOR -->
                        <div class="mt-5 flex justify-end" v-if="currentUser?.role !== 'vendor'">
                            <button v-if="$can('produksi', 'ubah')" @click="openAlokasiModal(pesanan)" class="btn btn-sm btn-neutral font-medium px-6">
                                Alokasikan Pengerjaan
                            </button>
                        </div>
                    </div>

                    <!-- KONDISI 2: PROSES PENGERJAAN -->
                    <div v-if="pesanan.status_operasional === 'proses_pengerjaan'" class="space-y-6">
                        <div v-for="item in pesanan.pesanan_item" :key="item.id" class="border rounded-lg border-base-200 overflow-hidden shadow-sm">

                            <div class="p-4 bg-base-50/50 border-b border-base-200 flex flex-col sm:flex-row gap-4">
                                <div class="sm:w-1/3">
                                    <span class="text-[10px] font-bold text-base-content/50 uppercase tracking-widest block mb-1.5">Item Produk</span>
                                    <h4 class="font-medium text-sm text-base-content">{{ item.nama_produk_snapshot }}</h4>
                                    <div v-if="item.pesanan_item_finishing?.length" class="flex flex-col gap-0.5 mb-2">
                                        <div v-for="(fin, fIdx) in item.pesanan_item_finishing" :key="'fin'+fIdx" class="flex items-start gap-1">
                                            <span class="opacity-50 mt-px text-xs">▸</span>
                                            <span class="font-medium text-xs text-base-content">{{ fin.nama_finishing_snapshot }}</span>
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

                                    <td class="px-4 py-3 font-medium text-xs">
                                        {{ schedule.tipe_pengerjaan === 'sendiri' ? 'In-House' : (schedule.vendor?.nama_vendor || 'Vendor Eksternal') }}
                                    </td>

                                    <td class="px-4 py-3 text-xs text-base-content/70">
                                        {{ schedule.instruksi_pengerjaan || '-' }}
                                    </td>

                                    <td class="px-4 py-3 font-semibold text-center text-xs">
                                        {{ schedule.qty_dikerjakan }}
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <button v-if="schedule.status_pengerjaan === 'selesai'"
                                            @click="openUpdateModal(schedule)"
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
                                            @click="openUpdateModal(schedule)"
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

                        <!-- Panel Selesai & Kirim (Sembunyikan dari vendor) -->
                        <div class="flex items-center justify-between mt-6 pt-5 border-t border-base-200" v-if="currentUser?.role !== 'vendor'">
                            <p class="text-xs text-base-content/50">
                                Penyelesaian order baru dapat dilakukan setelah semua item berstatus selesai.
                            </p>
                            <button
                                v-if="$can('produksi', 'ubah')"
                                @click="kirimPesanan(pesanan.id_pesan)"
                                :disabled="!isReadyToShip(pesanan)"
                                class="btn btn-sm px-6 font-medium"
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

    <!-- MODAL ALOKASI (Tetap) -->
    <dialog class="modal" :class="{'modal-open': isAlokasiModalOpen}">
        <div class="modal-box max-w-4xl p-0 rounded-xl">
            <!-- Isi modal alokasi tetap ada, meski tombolnya sudah kita sembunyikan untuk vendor -->
            <div class="flex items-center justify-between p-5 border-b border-base-200">
                <div>
                    <h3 class="text-base font-semibold">Alokasi Pengerjaan</h3>
                    <p class="text-sm text-base-content/50 mt-0.5">Tentukan pelaksana (In-house/Vendor) untuk pesanan {{ selectedOrderAlokasi?.id_pesan }}</p>
                </div>
                <button @click="closeAlokasiModal" class="text-base-content/40 hover:text-base-content">✕</button>
            </div>

            <div class="p-5 max-h-[70vh] overflow-y-auto">
                <form @submit.prevent="submitAlokasi" class="space-y-8">
                    <div v-for="(item, itemIndex) in alokasiForm.alokasi" :key="item.id_pesanan_item">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-sm">{{ item.nama_produk }}</h4>
                            <span class="text-xs text-base-content/60">Target: <span class="font-semibold text-base-content">{{ item.total_qty }}</span></span>
                        </div>
                        <div class="space-y-3">
                            <div v-for="(skema, skemaIndex) in item.skema" :key="skemaIndex" class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                                <div class="w-full sm:w-1/4">
                                    <label class="block text-xs font-medium text-base-content/70 mb-1">Pelaksana</label>
                                    <select v-model="skema.tipe_pengerjaan" class="select select-sm select-bordered w-full font-medium">
                                        <option value="sendiri">In-House</option>
                                        <option value="vendor">Vendor</option>
                                    </select>
                                </div>
                                <div v-if="skema.tipe_pengerjaan === 'vendor'" class="w-full sm:w-1/4">
                                    <label class="block text-xs font-medium text-base-content/70 mb-1">Pilih Vendor</label>
                                    <select v-model="skema.id_vendor" required class="select select-sm select-bordered w-full font-medium">
                                        <option :value="null" disabled>Pilih...</option>
                                        <option v-for="v in vendors" :key="v.id_vendor" :value="v.id_vendor">{{ v.nama_vendor }}</option>
                                    </select>
                                </div>
                                <div class="w-full sm:w-24">
                                    <label class="block text-xs font-medium text-base-content/70 mb-1">Qty</label>
                                    <input type="number" v-model="skema.qty_dikerjakan" required min="1" class="input input-sm input-bordered w-full" />
                                </div>
                                <div class="w-full flex-1">
                                    <label class="block text-xs font-medium text-base-content/70 mb-1">Instruksi</label>
                                    <input type="text" v-model="skema.instruksi_pengerjaan" placeholder="Opsional..." class="input input-sm input-bordered w-full" />
                                </div>
                                <div class="pb-0.5" v-if="item.skema.length > 1">
                                    <button type="button" @click="removeSkema(itemIndex, skemaIndex)" class="btn btn-sm btn-square btn-ghost text-red-500 hover:bg-red-50">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" @click="addSkema(itemIndex)" class="mt-3 text-xs font-medium text-base-content/60 hover:text-base-content flex items-center gap-1">
                            <Plus class="w-3.5 h-3.5" /> Tambah Pelaksana
                        </button>
                        <div v-if="itemIndex !== alokasiForm.alokasi.length - 1" class="border-b border-base-200 mt-6"></div>
                    </div>
                </form>
            </div>
            <div class="p-5 border-t border-base-200 flex justify-end gap-3 bg-base-50/50 rounded-b-xl">
                <button type="button" @click="closeAlokasiModal" class="btn btn-sm btn-ghost font-medium">Batal</button>
                <button type="button" @click="submitAlokasi" :disabled="alokasiForm.processing" class="btn btn-sm btn-neutral font-medium px-6">Simpan Alokasi</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop bg-base-content/20"><button @click="closeAlokasiModal">close</button></form>
    </dialog>

    <!-- MODAL UPDATE PROGRESS & LIHAT DATA -->
    <dialog class="modal" :class="{'modal-open': isUpdateModalOpen}">
        <div class="modal-box p-6 rounded-xl max-w-lg">
            <h3 class="text-base font-semibold mb-1">
                {{ isViewOnly ? 'Rincian Laporan Pengerjaan' : (selectedSchedule?.status_pengerjaan === 'selesai' ? 'Edit Laporan Pengerjaan' : 'Perbarui Status Pengerjaan') }}
            </h3>
            <p v-if="!isViewOnly" class="text-sm text-base-content/50 mb-6">Tandai tugas ini sebagai selesai dan isi laporan pengerjaan.</p>
            <p v-else class="text-sm text-base-content/50 mb-6">Berikut adalah hasil laporan untuk item ini.</p>

            <form @submit.prevent="submitUpdate" class="space-y-5">

                <div>
                    <label class="block text-sm font-medium text-base-content mb-1.5">Laporan Pengerjaan <span v-if="!isViewOnly" class="text-red-500">*</span></label>
                    <textarea v-model="updateForm.deskripsi_pengerjaan" :disabled="isViewOnly" :required="!isViewOnly" class="textarea textarea-bordered w-full h-24 disabled:bg-base-200 disabled:text-base-content/70 disabled:cursor-not-allowed" placeholder="Tulis rincian hasil pengerjaan..."></textarea>
                </div>

                <div v-if="selectedSchedule?.tipe_pengerjaan === 'vendor'" class="p-4 border rounded-lg border-base-200 bg-base-50/50 space-y-4">
                    <div class="flex items-center gap-2 text-sm font-medium text-base-content">
                        <Paperclip class="w-4 h-4 text-base-content/50" /> Detail Penagihan Vendor
                    </div>

                    <CustomInput
                        v-model="updateForm.total_tagihan_vendor"
                        type="number"
                        label="Nominal Tagihan (Rp)"
                        placeholder="0"
                        :disabled="isViewOnly"
                    />

                    <div>
                        <CustomInputFile
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
                    <button type="button" @click="closeUpdateModal" class="btn btn-sm btn-ghost font-medium">
                        {{ isViewOnly ? 'Tutup' : 'Batal' }}
                    </button>
                    <button v-if="!isViewOnly" type="submit" :disabled="updateForm.processing" class="btn btn-sm btn-neutral font-medium px-6">
                        {{ selectedSchedule?.status_pengerjaan === 'selesai' ? 'Simpan Perubahan' : 'Tandai Selesai' }}
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop bg-base-content/20">
            <button @click="closeUpdateModal">close</button>
        </form>
    </dialog>
</template>
