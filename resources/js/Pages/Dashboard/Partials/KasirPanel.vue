<script setup>
import { Head } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';

const props = defineProps({
    kpi: Object,
    pesananBaru: Array,
    tagihanVendorPending: Array,
});

const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka || 0);
</script>

<template>
    <Head title="Dashboard Kasir" />

    <StafLayout>
        <template #header>
            <div class="flex flex-col items-start justify-between w-full gap-4 md:flex-row md:items-center">
                <div>
                    <h2 class="text-2xl font-black leading-tight text-base-content">
                        Dashboard Kasir
                    </h2>
                    <p class="text-xs font-bold opacity-60 mt-0.5">Pantau transaksi dan tagihan vendor</p>
                </div>
                <CustomButton type="link" :href="route('pesan.pos-kasir')" variant="primary" size="md">
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" /></svg>
                    </template>
                    Buka POS Kasir
                </CustomButton>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-[95%] sm:px-6 lg:px-8 space-y-8">
                <!-- KPI KASIR -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex items-center gap-4 p-5 transition-colors border shadow-sm bg-base-100 border-base-300 rounded-2xl hover:border-primary/50">
                        <div class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl bg-primary/10 text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black tracking-widest uppercase opacity-60 mb-0.5">Order Masuk (Hari Ini)</p>
                            <p class="text-2xl font-black text-primary">{{ kpi.pesanan_hari_ini }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-5 transition-colors border shadow-sm bg-base-100 border-base-300 rounded-2xl hover:border-success/50">
                        <div class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl bg-success/10 text-success">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-[10px] font-black tracking-widest uppercase opacity-60 mb-0.5">Omzet (Hari Ini)</p>
                            <p class="text-2xl font-black truncate text-success">{{ formatRupiah(kpi.omzet_hari_ini) }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-5 transition-colors border shadow-sm bg-base-100 border-base-300 rounded-2xl hover:border-error/50">
                        <div class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl bg-error/10 text-error">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black tracking-widest uppercase opacity-60 mb-0.5">Hutang Vendor (Pending)</p>
                            <p class="text-2xl font-black text-error">{{ kpi.tagihan_pending }} <span class="text-xs font-medium text-base-content/50">Nota</span></p>
                        </div>
                    </div>
                </div>

                <!-- TABEL KASIR -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Pesanan Menunggu Pembayaran -->
                    <div class="border shadow-sm card bg-base-100 border-base-300">
                        <div class="p-5 card-body">
                            <h3 class="flex justify-between pb-3 text-xs font-black tracking-widest uppercase border-b opacity-60 border-base-200">
                                <span>💳 Pesanan Baru (Belum Lunas)</span>
                            </h3>
                            <div class="mt-2 overflow-x-auto">
                                <table class="table w-full table-xs">
                                    <tbody>
                                        <tr v-if="pesananBaru.length === 0"><td class="py-4 text-center opacity-50">Belum ada pesanan baru.</td></tr>
                                        <tr v-for="psn in pesananBaru" :key="psn.id_pesan" class="hover text-[11px]">
                                            <td class="font-mono font-bold">{{ psn.id_pesan }}</td>
                                            <td>{{ psn.customer?.user?.name || psn.alamat?.nama_penerima || 'Umum' }}</td>
                                            <td class="font-black text-right text-primary">{{ formatRupiah(psn.total_tagihan) }}</td>
                                            <td class="text-right">
                                                <CustomButton type="link" :href="`/pesan/${psn.id_pesan}/detail`" size="sm" variant="secondary" class="px-2! py-1! text-[9px]!">Detail</CustomButton>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tagihan Vendor -->
                    <div class="border shadow-sm card bg-error/5 border-error/20">
                        <div class="p-5 card-body">
                            <h3 class="flex justify-between pb-3 text-xs font-black tracking-widest uppercase border-b text-error opacity-80 border-error/20">
                                <span>🧾 Tagihan Vendor Jatuh Tempo</span>
                                <a :href="route('tagihan-vendor.index')" class="text-[9px] text-error hover:underline flex items-center">Buka Tagihan &rarr;</a>
                            </h3>
                            <ul class="mt-3 space-y-2">
                                <li v-if="tagihanVendorPending.length === 0" class="py-4 text-xs text-center opacity-50">Tidak ada tagihan tertunggak.</li>
                                <li v-for="tagihan in tagihanVendorPending" :key="tagihan.id" class="flex justify-between p-3 border rounded-lg border-error/10 bg-white/50 dark:bg-black/20">
                                    <div>
                                        <p class="text-xs font-bold">{{ tagihan.vendor?.nama_vendor }}</p>
                                        <p class="text-[10px] opacity-60 font-mono">Inv: #{{ tagihan.id }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-black text-error">{{ formatRupiah(tagihan.total_tagihan_vendor) }}</p>
                                        <span class="text-[9px] bg-error/20 text-error px-1.5 py-0.5 rounded font-bold">Belum Dibayar</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
