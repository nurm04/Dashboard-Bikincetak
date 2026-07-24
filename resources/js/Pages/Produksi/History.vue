<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle, History as HistoryIcon } from 'lucide-vue-next';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomTable from '@/Components/CustomTable.vue';

const props = defineProps({
    pesananHistori: Object,
    currentVendorId: String,
});

const formatDateTime = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

// UBAH: Jadikan computed supaya kolom 'Pembayaran' cuma muncul untuk Vendor
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
                        <p class="text-sm mt-1 text-base-content/60">Daftar pesanan yang telah selesai dikerjakan.</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="px-4 py-8 mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">

            <!-- KONDISI KOSONG -->
            <div v-if="pesananHistori.data.length === 0" class="flex flex-col items-center justify-center py-24 text-center border rounded-lg border-base-200 bg-base-100">
                <HistoryIcon class="w-12 h-12 mb-4 text-base-content/20" />
                <h3 class="text-base font-semibold text-base-content">Belum Ada Histori</h3>
                <p class="text-sm mt-1 text-base-content/50">Belum ada pesanan yang masuk ke riwayat penyelesaian produksi.</p>
            </div>

            <!-- KARTU HISTORI PESANAN -->
            <div v-for="pesanan in pesananHistori.data" :key="pesanan.id_pesan" class="overflow-hidden border shadow-sm rounded-xl border-base-200 bg-base-100 opacity-95">

                <!-- Card Header -->
                <div class="flex flex-col items-start justify-between gap-4 p-5 border-b sm:flex-row sm:items-center border-base-200 bg-base-50/50">
                    <div class="flex items-center gap-4">
                        <div class="px-3 py-1.5 border rounded-md border-base-300 bg-base-100 flex flex-col items-center justify-center">
                            <span class="text-[10px] font-medium text-base-content/50 uppercase">ID Pesan</span>
                            <span class="text-sm font-bold text-base-content">{{ pesanan.id_pesan }}</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-base-content">{{ pesanan.customer?.user?.name }}</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="flex items-center gap-1.5 text-xs font-medium px-2 py-0.5 rounded-full border border-green-200 text-green-700 bg-green-50">
                                    <CheckCircle class="w-3 h-3" />
                                    Produksi Selesai
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-sm text-right text-base-content/60">
                        Diperbarui pada:<br>
                        <span class="font-semibold text-base-content">{{ formatDateTime(pesanan.updated_at) }}</span>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-5 space-y-6">
                    <div v-for="item in pesanan.pesanan_item" :key="item.id" class="overflow-hidden border shadow-sm rounded-lg border-base-200">

                        <!-- Info Produk -->
                        <div class="p-4 border-b bg-base-50/50 border-base-200">
                            <h4 class="text-sm font-medium text-base-content">{{ item.nama_produk_snapshot }}</h4>
                            <p class="mt-1 text-xs text-base-content/60">Total Kuantitas: <span class="font-bold text-base-content">{{ item.jumlah }}</span></p>
                        </div>

                        <!-- Tabel Riwayat Pengerjaan -->
                        <CustomTable :headers="headersProses" class="border-none shadow-none">
                            <tr v-for="schedule in item.pesanan_item_produksi" :key="schedule.id" class="transition-colors border-b hover:bg-base-200/30 border-base-200/50">

                                <td class="px-4 py-3 text-xs font-medium align-top">
                                    {{ schedule.tipe_pengerjaan === 'sendiri' ? 'In-House' : (schedule.vendor?.nama_vendor || 'Vendor Eksternal') }}
                                </td>

                                <td class="px-4 py-3 text-xs align-top text-base-content/70">
                                    {{ schedule.instruksi_pengerjaan || '-' }}
                                </td>

                                <td class="px-4 py-3 text-xs font-semibold text-center align-top">
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

                                <td class="px-4 py-3 text-center align-top">
                                    <span class="inline-flex justify-center items-center gap-1.5 text-xs font-bold text-green-600">
                                        Selesai
                                    </span>
                                </td>

                                <td v-if="currentVendorId" class="px-3 py-3 align-top min-w-40">
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

                                        <div class="w-full border-t border-dashed border-base-300 my-1"></div>

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
