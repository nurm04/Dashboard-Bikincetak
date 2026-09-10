<style scoped>
.custom-scrollbar::-webkit-scrollbar { display: none; }
.custom-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle, History as HistoryIcon, Printer } from 'lucide-vue-next';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInputSearch from '@/Components/Form/CustomInputSearch.vue';

// 👇 IMPORT KOMPONEN TABEL 👇
import CustomTable from '@/Components/CustomTable.vue';
import CustomTableAction from '@/Components/CustomTableAction.vue';

const props = defineProps({
    pesananHistori: Object,
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

const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const search = ref(props.filters?.search || '');

watch(
    search,
    debounce((newSearch) => {
        router.get('/produksi/histori', {
            search: newSearch
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300)
);

// ==========================================
// KONFIGURASI KOLOM TABEL
// ==========================================
const headers = ['ID Pesanan', 'Customer', 'Diperbarui Pada', 'Status', 'Aksi'];

const headersProses = computed(() => {
    const baseHeaders = ['Pelaksana', 'Instruksi / Keterangan', 'Qty', 'Catatan Laporan', 'Status'];
    if (props.currentVendorId) {
        baseHeaders.push('Pembayaran');
    }
    return baseHeaders;
});

// ==========================================
// LOGIC MODAL DETAIL & HISTORI
// ==========================================
const isDetailModalOpen = ref(false);
const selectedPesananDetail = ref(null);

const openDetailModal = (pesanan) => {
    selectedPesananDetail.value = pesanan;
    isDetailModalOpen.value = true;
};
const closeDetailModal = () => {
    isDetailModalOpen.value = false;
    selectedPesananDetail.value = null;
};
</script>

<template>
    <Head title="Histori Produksi" />
    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('produksi.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h2 class="text-xl font-semibold leading-tight text-base-content">
                            Histori Produksi
                        </h2>
                        <p class="mt-1 text-sm text-base-content/60">Daftar pesanan yang telah selesai dikerjakan.</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="px-4 py-8 mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">

            <!-- FITUR PENCARIAN -->
            <div class="flex items-center justify-start w-full mb-2">
                <CustomInputSearch
                    v-model="search"
                    class="w-full sm:w-80"
                    placeholder="Cari ID / Nama / Produk..."
                />
            </div>

            <!-- 👇 TABEL HISTORI PESANAN (MENGGUNAKAN PAGINATION OTOMATIS) 👇 -->
            <CustomTable :headers="headers" :pagination="pesananHistori">

                <!-- LOOPING DATA DI DALAM pesananHistori.data -->
                <tr v-for="pesanan in pesananHistori.data" :key="pesanan.id_pesan" class="transition-colors border-b hover:bg-base-200/50 border-base-200/50">

                    <!-- 1. ID Pesanan -->
                    <td class="px-4 py-4 font-mono text-xs font-bold whitespace-nowrap text-primary">
                        {{ pesanan.id_pesan }}
                    </td>

                    <!-- 2. Customer -->
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="font-bold text-base-content">{{ pesanan.customer?.user?.name || 'Walk-in / Umum' }}</div>
                        <div class="text-[10px] text-base-content/50">{{ pesanan.customer?.id_customer || '-' }}</div>
                    </td>

                    <!-- 3. Diperbarui Pada -->
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-1.5">
                            <Clock class="w-3.5 h-3.5 opacity-50" />
                            <span class="font-black tracking-tight text-base-content">
                                {{ formatTanggal(pesanan.updated_at) }}
                            </span>
                        </div>
                    </td>

                    <!-- 4. Status -->
                    <td class="px-4 py-4 whitespace-nowrap">
                        <span class="flex items-center gap-1.5 text-[10px] sm:text-xs font-bold px-2.5 py-1 rounded-full border border-green-200 text-green-700 bg-green-50 w-fit">
                            <CheckCircle class="w-3.5 h-3.5" /> Produksi Selesai
                        </span>
                    </td>

                    <!-- 5. Aksi Pop-up -->
                    <td class="px-4 py-4 text-center whitespace-nowrap">
                        <CustomTableAction v-slot="{ close }">
                            <div class="px-4 py-2 text-[10px] font-black text-base-content/40 uppercase tracking-widest border-b border-base-300/50 mb-1 text-left">
                                Menu Histori
                            </div>

                            <!-- Aksi: Detail & Histori -->
                            <button @click="openDetailModal(pesanan); close()" class="flex items-center w-full text-left whitespace-nowrap px-4 py-2.5 text-sm font-bold text-base-content hover:bg-base-200 transition-colors">
                                <svg class="w-4 h-4 mr-3 shrink-0 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Detail & Histori Pengerjaan
                            </button>

                            <!-- Aksi: Cetak Label -->
                            <a :href="route('pesan.cetakLabel', pesanan.id_pesan)" target="_blank" @click="close()" class="flex items-center w-full text-left whitespace-nowrap px-4 py-2.5 text-sm font-bold text-info hover:bg-info/10 transition-colors">
                                <Printer class="w-4 h-4 mr-3 shrink-0" /> Cetak Label
                            </a>

                            <!-- Aksi: Cetak Nota -->
                            <a :href="route('pesan.cetakNota', pesanan.id_pesan)" target="_blank" @click="close()" class="flex items-center w-full text-left whitespace-nowrap px-4 py-2.5 text-sm font-bold text-warning hover:bg-warning/10 transition-colors">
                                <Printer class="w-4 h-4 mr-3 shrink-0" /> Cetak Nota
                            </a>
                        </CustomTableAction>
                    </td>
                </tr>

                <!-- Jika Data Kosong -->
                <tr v-if="pesananHistori.data.length === 0">
                    <td colspan="5" class="px-6 py-20 text-center">
                        <div class="flex flex-col items-center justify-center opacity-30">
                            <HistoryIcon class="w-12 h-12 mb-4" stroke-width="1.5" />
                            <h3 class="text-base font-semibold text-base-content">Belum Ada Histori</h3>
                            <p class="mt-1 text-sm text-base-content/50">Belum ada pesanan yang masuk ke riwayat penyelesaian produksi.</p>
                        </div>
                    </td>
                </tr>
            </CustomTable>

        </div>


        <!-- 👇 MODAL DETAIL PRODUK & HISTORI 👇 -->
        <dialog class="modal" :class="{'modal-open': isDetailModalOpen}">
            <div class="max-w-5xl p-0 modal-box rounded-2xl z-100">
                <div class="flex items-center justify-between p-4 border-b sm:p-5 border-base-200 bg-base-50">
                    <div>
                        <h3 class="text-base font-bold text-base-content">Detail & Histori Pengerjaan</h3>
                        <p class="text-[11px] sm:text-sm font-medium text-base-content/50 mt-0.5">ID Transaksi: <span class="font-bold text-primary">{{ selectedPesananDetail?.id_pesan }}</span></p>
                    </div>
                    <button @click="closeDetailModal" class="btn btn-sm btn-circle btn-ghost text-base-content/40 hover:text-error">✕</button>
                </div>

                <div class="p-4 sm:p-5 max-h-[70vh] overflow-y-auto custom-scrollbar space-y-6">
                    <div v-for="item in selectedPesananDetail?.pesanan_item" :key="item.id" class="overflow-hidden border shadow-sm rounded-xl border-base-200 bg-base-100">

                        <!-- Info Ringkas Item (Tanpa File) -->
                        <div class="flex flex-col gap-2 p-4 border-b sm:flex-row sm:items-center sm:justify-between bg-base-50/30 border-base-200">
                            <div>
                                <span class="text-[9px] sm:text-[10px] font-black text-base-content/50 uppercase tracking-widest block mb-1">Item Produk</span>
                                <h4 class="text-sm font-black capitalize sm:text-base text-base-content">{{ item.nama_produk_snapshot }}</h4>
                            </div>
                            <div class="pt-2 mt-2 border-t sm:mt-0 sm:pt-0 sm:border-none border-base-200 sm:text-right">
                                <span class="text-[9px] sm:text-[10px] font-black text-base-content/50 uppercase tracking-widest block mb-1">Total Qty</span>
                                <span class="text-sm font-black sm:text-base text-base-content">{{ item.jumlah }}</span>
                            </div>
                        </div>

                        <!-- TABEL RIWAYAT PENGERJAAN -->
                        <div class="p-0 overflow-x-auto sm:p-2 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:bg-base-300 [&::-webkit-scrollbar-thumb]:rounded-full pb-2">
                            <div class="min-w-175">
                                <CustomTable :headers="headersProses" class="bg-transparent border-none shadow-none" :pagination="false">
                                    <tr v-for="schedule in item.pesanan_item_produksi" :key="schedule.id" class="transition-colors border-b hover:bg-base-200/30 border-base-200/50">

                                        <td class="px-4 py-3 text-xs font-medium align-top whitespace-nowrap">
                                            {{ schedule.tipe_pengerjaan === 'sendiri' ? 'In-House' : (schedule.vendor?.nama_vendor || 'Vendor Eksternal') }}
                                        </td>

                                        <td class="px-4 py-3 text-xs align-top text-base-content/70 whitespace-nowrap">
                                            {{ schedule.instruksi_pengerjaan || '-' }}
                                        </td>

                                        <td class="px-4 py-3 text-xs font-semibold text-center align-top whitespace-nowrap">
                                            {{ schedule.qty_dikerjakan }}
                                        </td>

                                        <td class="max-w-xs px-4 py-3 text-xs truncate align-top text-base-content/70" :title="schedule.deskripsi_pengerjaan">
                                            <span v-if="schedule.deskripsi_pengerjaan" class="italic">"{{ schedule.deskripsi_pengerjaan }}"</span>
                                            <span v-else class="italic opacity-50">Tidak ada catatan laporan.</span>

                                            <!-- Indikator Nota Produksi -->
                                            <div v-if="schedule.file_nota" class="mt-1">
                                                <a :href="'/storage/' + schedule.file_nota" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 hover:underline bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100">
                                                    📁 Lihat Nota
                                                </a>
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 text-center align-top whitespace-nowrap">
                                            <span class="inline-flex justify-center items-center gap-1.5 text-xs font-bold text-green-600">
                                                Selesai
                                            </span>
                                        </td>

                                        <!-- Kolom Pembayaran Khusus Vendor -->
                                        <td v-if="currentVendorId" class="px-3 py-2 align-top min-w-40">
                                            <div v-if="schedule.tagihan_vendor" class="flex flex-col p-2.5 border border-base-300 rounded-xl bg-base-50/50 shadow-sm transition-all hover:border-base-400">
                                                <div class="flex items-center justify-between gap-2 mb-2">
                                                    <span class="inline-flex items-center gap-1 text-[9px] font-black px-2 py-0.5 rounded-md bg-success/15 text-success uppercase tracking-widest">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                                        Lunas
                                                    </span>
                                                    <span class="text-[10px] font-mono font-black text-base-content/70 bg-base-200 px-1.5 py-0.5 rounded border border-base-300">
                                                        {{ schedule.tagihan_vendor.kode_tagihan || 'NO-KODE' }}
                                                    </span>
                                                </div>
                                                <div class="w-full my-1 border-t border-dashed border-base-300"></div>
                                                <div class="flex items-center justify-between mt-1">
                                                    <span class="text-[9px] font-medium text-base-content/50">
                                                        {{ new Date(schedule.tagihan_vendor.tanggal_bayar).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                                                    </span>
                                                    <a v-if="schedule.tagihan_vendor.bukti_bayar" :href="'/storage/' + schedule.tagihan_vendor.bukti_bayar" target="_blank" class="inline-flex items-center gap-1 text-[9px] font-bold text-blue-600 hover:text-white bg-blue-50 hover:bg-blue-600 px-2 py-1 rounded-md transition-colors border border-blue-100 hover:border-blue-600">
                                                        Lihat TF
                                                    </a>
                                                </div>
                                            </div>
                                            <div v-else class="flex justify-center p-2">
                                                <span class="inline-flex justify-center items-center gap-1.5 text-[9px] font-bold px-3 py-1.5 rounded-lg bg-base-200 text-base-content/50 border border-base-300 uppercase tracking-widest">
                                                    <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    Belum Ditagih
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </CustomTable>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop bg-base-content/50 z-90"><button @click="closeDetailModal">close</button></form>
        </dialog>

    </StafLayout>
</template>
