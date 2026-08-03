<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle, History as HistoryIcon } from 'lucide-vue-next';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomInputSearch from '@/Components/Form/CustomInputSearch.vue';

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

const headersProses = computed(() => {
    const baseHeaders = ['Pelaksana', 'Instruksi / Keterangan', 'Qty', 'Catatan Laporan', 'Status'];
    if (props.currentVendorId) {
        baseHeaders.push('Pembayaran');
    }
    return baseHeaders;
});
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

            <!-- DESAIN EMPTY STATE KONSISTEN -->
            <div v-if="pesananHistori.data.length === 0" class="flex flex-col items-center justify-center py-20 mt-4 duration-500 border bg-base-200/20 border-base-300 rounded-3xl animate-in fade-in zoom-in-95">
                <HistoryIcon class="w-12 h-12 mb-3 opacity-30 text-base-content" stroke-width="1.5" />
                <h3 class="text-sm font-bold opacity-80 text-base-content">Belum Ada Histori</h3>
                <p class="mt-1 text-xs opacity-50 text-base-content">Belum ada pesanan yang masuk ke riwayat penyelesaian produksi.</p>
            </div>

            <!-- KARTU HISTORI PESANAN -->
            <div v-for="pesanan in pesananHistori.data" :key="pesanan.id_pesan" class="overflow-hidden border shadow-sm rounded-xl border-base-200 bg-base-100 opacity-95 animate-in fade-in slide-in-from-bottom-2">

                <!-- HEADER PESANAN RESPONSIVE -->
                <div class="flex flex-col items-start justify-between gap-4 p-4 border-b sm:p-5 sm:flex-row sm:items-center border-base-200 bg-base-50/30">
                    <div class="flex items-start w-full gap-3 sm:items-center sm:w-auto">
                        <!-- shrink-0 agar kotak ID tidak gepeng -->
                        <div class="shrink-0 px-3 py-1.5 border rounded-lg border-base-300 bg-base-100 flex flex-col items-center justify-center">
                            <span class="text-[9px] sm:text-[10px] font-black text-base-content/50 uppercase tracking-widest">ID Pesan</span>
                            <span class="text-xs font-black sm:text-sm text-base-content">{{ pesanan.id_pesan }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold truncate sm:text-base text-base-content">{{ pesanan.customer?.user?.name }}</h3>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <span class="flex items-center gap-1.5 text-[10px] sm:text-xs font-bold px-2.5 py-1 rounded-full border border-green-200 text-green-700 bg-green-50">
                                    <CheckCircle class="w-3.5 h-3.5" />
                                    Produksi Selesai
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian Kanan Header (Tanggal Diperbarui) -->
                    <div class="flex flex-col w-full gap-1 pt-3 border-t sm:border-t-0 sm:pt-0 border-base-200 sm:w-auto sm:items-end shrink-0">
                        <span class="text-[9px] sm:text-[10px] font-black text-base-content/50 uppercase tracking-widest">Diperbarui pada</span>
                        <span class="text-xs font-black tracking-tight sm:text-sm text-base-content">{{ formatTanggal(pesanan.updated_at) }}</span>
                    </div>
                </div>

                <!-- CARD BODY -->
                <div class="p-4 space-y-6 sm:p-5">
                    <div v-for="item in pesanan.pesanan_item" :key="item.id" class="overflow-hidden border shadow-sm rounded-xl border-base-200">

                        <!-- Info Produk Responsif -->
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

                        <!-- TABEL RIWAYAT PENGERJAAN (Bisa Scroll Horizontal) -->
                        <div class="p-0 overflow-x-auto sm:p-2 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:bg-base-300 [&::-webkit-scrollbar-thumb]:rounded-full pb-2">
                            <div class="min-w-175">
                                <CustomTable :headers="headersProses" class="bg-transparent border-none shadow-none">
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

            <!-- PAGINATION -->
            <div class="flex justify-center pb-8 mt-8" v-if="pesananHistori.links && pesananHistori.links.length > 3">
                <div class="join">
                    <Link v-for="(link, i) in pesananHistori.links" :key="i"
                        :href="link.url || '#'"
                        class="font-medium join-item btn btn-sm"
                        :class="[
                            link.active ? 'btn-active btn-neutral' : 'bg-base-100',
                            !link.url ? 'btn-disabled text-base-content/30' : ''
                        ]"
                        v-html="link.label"
                    ></Link>
                </div>
            </div>

        </div>
    </StafLayout>
</template>
