<script setup>
import { Head } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';

const props = defineProps({
    kpi: Object,
    urgentProduksi: Array,
    stokMenipis: Array,
});
const formatTanggal = (tgl) => {
    if (!tgl) return '-';
    return new Date(tgl).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Dashboard Produksi" />

    <StafLayout>
        <template #header>
            <div class="flex flex-col items-start justify-between w-full gap-4 md:flex-row md:items-center">
                <div>
                    <h2 class="text-2xl font-black leading-tight text-base-content">
                        Dashboard Produksi
                    </h2>
                    <p class="text-xs font-bold opacity-60 mt-0.5">Pantau antrean pengerjaan dan stok bahan baku</p>
                </div>
                <div class="flex gap-2">
                    <CustomButton type="link" href="/produksi" size="md">
                        🛠️ Kelola Produksi
                    </CustomButton>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-[95%] sm:px-6 lg:px-8 space-y-8">
                <!-- KPI PRODUKSI -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-center gap-4 p-5 transition-colors border shadow-sm bg-base-100 border-base-300 rounded-2xl hover:border-warning/50">
                        <div class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl bg-warning/10 text-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 0015 0m-15 0a7.5 7.5 0 1115 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077l1.41-.513m14.095-5.13l1.41-.513M5.106 17.785l1.15-.964m11.49-9.642l1.149-.964M7.501 19.79l.867-1.321m7.264-11.058l.867-1.321m-10.198 1.321l-1.321.867m11.058 7.264l-1.321.867m-9.642 1.149l-.964 1.15m9.642-11.49l-.964 1.15m5.13 14.095l-.513 1.41m-5.13-14.095l-.513 1.41" /></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black tracking-widest uppercase opacity-60 mb-0.5">Antrean Sedang Diproses</p>
                            <p class="text-2xl font-black text-warning">{{ kpi?.antrean_produksi || 0 }} <span class="text-xs font-medium text-base-content/50">Pesanan</span></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-5 transition-colors border shadow-sm bg-base-100 border-base-300 rounded-2xl hover:border-info/50">
                        <div class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl bg-info/10 text-info">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black tracking-widest uppercase opacity-60 mb-0.5">Selesai & Siap Kirim</p>
                            <p class="text-2xl font-black text-info">{{ kpi?.siap_kirim || 0 }} <span class="text-xs font-medium text-base-content/50">Paket</span></p>
                        </div>
                    </div>
                </div>

                <!-- TABEL DATA PRODUKSI -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Tabel Antrean -->
                    <div class="border shadow-sm card bg-base-100 border-base-300">
                        <div class="p-5 card-body">
                            <h3 class="flex justify-between pb-3 text-xs font-black tracking-widest uppercase border-b opacity-60 border-base-200">
                                <span>⚙️ Prioritas Antrean Mesin</span>
                            </h3>
                            <div class="mt-2 overflow-x-auto">
                                <table class="table w-full table-xs">
                                    <tbody>
                                        <tr v-if="urgentProduksi.length === 0"><td class="py-4 text-center opacity-50">Antrean kosong, mesin istirahat.</td></tr>
                                        <tr v-for="psn in urgentProduksi" :key="psn.id_pesan" class="hover text-[11px]">
                                            <td class="font-mono font-bold">{{ psn.id_pesan }}</td>
                                            <td>{{ psn.customer?.user?.name || 'Umum' }}</td>
                                            <td class="text-right">
                                                <span class="text-[9px] bg-warning/20 text-warning px-2 py-0.5 rounded font-bold uppercase">{{ formatTanggal(psn.waktu_deadline) }}</span>
                                            </td>
                                            <td class="text-right w-16">
                                                <CustomButton type="link" href="/produksi" size="sm" variant="secondary" class="px-2! py-1! text-[9px]!">Buka</CustomButton>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Peringatan Stok -->
                    <div class="border shadow-sm card bg-error/5 border-error/20">
                        <div class="p-5 card-body">
                            <h3 class="flex justify-between pb-3 text-xs font-black tracking-widest uppercase border-b text-error opacity-80 border-error/20">
                                <span>⚠️ Peringatan Stok Menipis</span>
                            </h3>
                            <ul class="mt-3 space-y-2">
                                <li v-if="stokMenipis.length === 0" class="py-4 text-xs text-center opacity-50">Ketersediaan bahan baku aman.</li>
                                <li v-for="bahan in stokMenipis" :key="bahan.id_bahan_baku" class="flex items-center justify-between p-3 border rounded-lg border-error/10 bg-white/50 dark:bg-black/20">
                                    <span class="text-xs font-bold opacity-80">{{ bahan.nama_bahan_baku }}</span>
                                    <span class="text-[11px] font-black text-error animate-pulse bg-error/10 px-2 py-0.5 rounded">
                                        Sisa: {{ bahan.stok_sekarang }} {{ bahan.satuan }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
