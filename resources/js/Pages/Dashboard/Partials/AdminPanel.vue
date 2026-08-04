<script setup>
import { onMounted, ref, watch, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import Chart from 'chart.js/auto';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';

const props = defineProps({
    grafikBEP: Object,
    bulanTahun: String,
    filters: Object,
    kpi: Object,
    pesananBaru: Array,
    urgentProduksi: Array,
    topProduk: Array,
    stokMenipis: Array,
});

const chartCanvas = ref(null);
let chartInstance = null;

const filterForm = ref({
    start_month: props.filters?.start_month || '',
    end_month: props.filters?.end_month || '',
});

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka || 0);
};

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

const applyFilter = () => {
    router.get(route('dashboard'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['grafikBEP', 'bulanTahun', 'filters'],
    });
};

const renderChart = () => {
    if (!props.grafikBEP || !props.grafikBEP.labels || !chartCanvas.value) return;
    if (chartInstance) chartInstance.destroy();

    const dataLabaRugi = props.grafikBEP.labels.map((_, index) => {
        return props.grafikBEP.pemasukan[index] - props.grafikBEP.pengeluaran[index];
    });

    chartInstance = new Chart(chartCanvas.value, {
        type: 'line',
        data: {
            labels: props.grafikBEP.labels,
            datasets: [
                {
                    type: 'line',
                    label: 'Pemasukan (Kumulatif)',
                    data: props.grafikBEP.pemasukan,
                    borderColor: '#10b981',
                    backgroundColor: '#10b981',
                    borderWidth: 3,
                    tension: 0.3,
                },
                {
                    type: 'line',
                    label: 'Pengeluaran (Kumulatif)',
                    data: props.grafikBEP.pengeluaran,
                    borderColor: '#ef4444',
                    backgroundColor: '#ef4444',
                    borderWidth: 3,
                    borderDash: [5, 5],
                    tension: 0.3,
                },
                {
                    type: 'bar',
                    label: 'Posisi Laba / Rugi',
                    data: dataLabaRugi,
                    backgroundColor: dataLabaRugi.map(val => val >= 0 ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)'),
                    borderColor: dataLabaRugi.map(val => val >= 0 ? 'rgba(16, 185, 129, 0.5)' : 'rgba(239, 68, 68, 0.5)'),
                    borderWidth: 1,
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                title: { display: true, text: `Grafik Break-Even Point (BEP) Keuangan - ${props.bulanTahun}`, font: { size: 16 } },
                tooltip: { callbacks: { label: function(context) { return (context.dataset.label || '') + ': ' + formatRupiah(context.parsed.y); } } }
            },
            scales: { y: { beginAtZero: true, ticks: { callback: function(value) { return 'Rp ' + (value / 1000000) + ' Jt'; } } } }
        }
    });
};

onMounted(() => renderChart());
watch(() => props.grafikBEP, () => renderChart(), { deep: true });
onBeforeUnmount(() => { if (chartInstance) chartInstance.destroy(); });
</script>

<template>
    <Head title="Dashboard Admin" />

    <StafLayout>
        <template #header>
            <div class="flex flex-col items-start justify-between w-full gap-4 md:flex-row md:items-center">
                <div>
                    <h2 class="text-2xl font-black leading-tight text-base-content">
                        Dashboard Utama (Admin)
                    </h2>
                    <p class="text-xs font-bold opacity-60 mt-0.5">Ringkasan operasional dan keuangan bisnis</p>
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
                <!-- KPI GRID ADMIN -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-2">
                    <div class="flex items-center gap-4 p-5 transition-colors border shadow-sm bg-base-100 border-base-300 rounded-2xl hover:border-primary/50">
                        <div class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl bg-primary/10 text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black tracking-widest uppercase opacity-60 mb-0.5">Order Masuk</p>
                            <p class="text-2xl font-black leading-none text-primary">{{ kpi?.pesanan_hari_ini || 0 }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-5 transition-colors border shadow-sm bg-base-100 border-base-300 rounded-2xl hover:border-success/50">
                        <div class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl bg-success/10 text-success">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-[10px] font-black tracking-widest uppercase opacity-60 mb-0.5">Omzet (Hari Ini)</p>
                            <p class="text-xl font-black leading-none truncate text-success xl:text-2xl">{{ formatRupiah(kpi?.omzet_hari_ini) }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-5 transition-colors border shadow-sm bg-base-100 border-base-300 rounded-2xl hover:border-warning/50">
                        <div class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl bg-warning/10 text-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 0015 0m-15 0a7.5 7.5 0 1115 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077l1.41-.513m14.095-5.13l1.41-.513M5.106 17.785l1.15-.964m11.49-9.642l1.149-.964M7.501 19.79l.867-1.321m7.264-11.058l.867-1.321m-10.198 1.321l-1.321.867m11.058 7.264l-1.321.867m-9.642 1.149l-.964 1.15m9.642-11.49l-.964 1.15m5.13 14.095l-.513 1.41m-5.13-14.095l-.513 1.41" /></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black tracking-widest uppercase opacity-60 mb-0.5">Antrean Produksi</p>
                            <div class="flex items-end gap-2">
                                <p class="text-2xl font-black leading-none text-warning">{{ kpi?.antrean_produksi || 0 }}</p>
                                <p class="text-[9px] font-bold opacity-50 uppercase pb-0.5 hidden 2xl:block">Diproses</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-5 transition-colors border shadow-sm bg-base-100 border-base-300 rounded-2xl hover:border-info/50">
                        <div class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl bg-info/10 text-info">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black tracking-widest uppercase opacity-60 mb-0.5">Siap Kirim</p>
                            <div class="flex items-end gap-2">
                                <p class="text-2xl font-black leading-none text-info">{{ kpi?.siap_kirim || 0 }}</p>
                                <p class="text-[9px] font-bold opacity-50 uppercase pb-0.5 hidden 2xl:block">Paket</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHART ADMIN -->
                <div class="overflow-hidden border shadow-sm bg-base-100 sm:rounded-2xl border-base-300">
                    <div class="p-6">
                        <div class="flex flex-col justify-between gap-4 pb-4 mb-6 border-b md:flex-row md:items-center border-base-200">
                            <h3 class="flex items-center gap-2 text-sm font-black tracking-widest uppercase opacity-50">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 131.25V15h11.25M15 15l-4.5-4.5-4.5 4.5M3 15l4.5-4.5 4.5 4.5" /></svg>
                                Analisis BEP Keuangan
                            </h3>
                            <div class="flex flex-wrap items-end gap-3">
                                <div class="w-36">
                                    <CustomInput label="Mulai" type="month" v-model="filterForm.start_month" />
                                </div>
                                <div class="w-36">
                                    <CustomInput label="Sampai" type="month" v-model="filterForm.end_month" />
                                </div>
                                <div class="flex gap-2 pb-1">
                                    <CustomButton @click="applyFilter" size="sm">Terapkan</CustomButton>
                                    <CustomButton type="link" :href="`/buku-besar?start_month=${filterForm.start_month}&end_month=${filterForm.end_month}`" variant="secondary" size="sm">
                                        📄 Ledger
                                    </CustomButton>
                                </div>
                            </div>
                        </div>
                        <div class="relative w-full h-87.5">
                            <canvas ref="chartCanvas"></canvas>
                        </div>
                    </div>
                </div>

                <!-- TABEL DATA ADMIN -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <div class="space-y-8">
                        <div class="border shadow-sm card bg-base-100 border-base-300">
                            <div class="p-5 card-body">
                                <h3 class="flex justify-between pb-3 text-xs font-black tracking-widest uppercase border-b opacity-60 border-base-200">
                                    <span>🚨 Menunggu Diproses (Baru)</span>
                                    <span class="text-white badge badge-error badge-sm">{{ pesananBaru.length }}</span>
                                </h3>
                                <div class="mt-2 overflow-x-auto">
                                    <table class="table w-full table-xs">
                                        <tbody>
                                            <!-- REVISI: Tambah colspan="4" biar gak error nyempil -->
                                            <tr v-if="pesananBaru.length === 0"><td colspan="4" class="py-4 text-center opacity-50">Belum ada pesanan baru.</td></tr>
                                            <tr v-for="psn in pesananBaru" :key="psn.id_pesan" class="hover text-[11px]">
                                                <td class="font-mono font-bold">{{ psn.id_pesan }}</td>
                                                <td>{{ psn.customer?.user?.name || psn.alamat?.nama_penerima || 'Umum' }}</td>
                                                <td class="font-black text-right text-primary">{{ formatRupiah(psn.total_tagihan) }}</td>
                                                <td class="text-right">
                                                    <!-- REVISI: Standarisasi important tailwind jadi !px-2 dll -->
                                                    <CustomButton type="link" :href="`/pesan/${psn.id_pesan}/detail`" size="sm" variant="secondary" class="px-2! py-0.5! text-[9px]!">Detail</CustomButton>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="border shadow-sm card bg-base-100 border-base-300">
                            <div class="p-5 card-body">
                                <h3 class="pb-3 text-xs font-black tracking-widest uppercase border-b opacity-60 border-base-200">
                                    ⚙️ Urgent: Antrean Produksi
                                </h3>
                                <div class="mt-2 overflow-x-auto">
                                    <table class="table w-full table-xs">
                                        <tbody>
                                            <!-- REVISI: Tambah colspan="3" -->
                                            <tr v-if="urgentProduksi.length === 0"><td colspan="3" class="py-4 text-center opacity-50">Mesin lagi nganggur nih bos.</td></tr>
                                            <tr v-for="psn in urgentProduksi" :key="psn.id_pesan" class="hover text-[11px]">
                                                <td class="font-mono">{{ psn.id_pesan }}</td>
                                                <td>{{ psn.customer?.user?.name || 'Umum' }}</td>
                                                <td class="text-right">
                                                    <span class="text-[9px] bg-warning/20 text-warning px-2 py-0.5 rounded font-bold uppercase">{{ formatTanggal(psn.waktu_deadline) }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div class="border shadow-sm card bg-base-100 border-base-300">
                            <div class="p-5 card-body">
                                <h3 class="pb-3 text-xs font-black tracking-widest uppercase border-b opacity-60 border-base-200">
                                    🔥 Top 5 Produk Terlaris (Bulan Ini)
                                </h3>
                                <ul class="mt-3 space-y-3">
                                    <li v-if="topProduk.length === 0" class="py-4 text-xs text-center opacity-50">Belum ada penjualan bulan ini.</li>
                                    <li v-for="(prod, idx) in topProduk" :key="idx" class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="flex items-center justify-center w-6 h-6 text-xs font-black rounded-full bg-primary/10 text-primary">{{ idx + 1 }}</span>
                                            <span class="text-xs font-bold uppercase">{{ prod.nama_produk_snapshot }}</span>
                                        </div>
                                        <span class="font-mono text-xs font-black text-success">{{ prod.total_terjual }} Pcs</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="border shadow-sm card bg-error/5 border-error/20">
                            <div class="p-5 card-body">
                                <h3 class="flex justify-between pb-3 text-xs font-black tracking-widest uppercase border-b text-error opacity-80 border-error/20">
                                    <span>⚠️ Peringatan Stok Menipis</span>
                                    <CustomButton type="link" href="/bahan-baku" size="sm" variant="error" class="px-2! py-0.5! text-[9px]!">Restock</CustomButton>
                                </h3>
                                <ul class="mt-3 space-y-2">
                                    <li v-if="stokMenipis.length === 0" class="py-4 text-xs text-center opacity-50">Semua stok aman.</li>
                                    <li v-for="bahan in stokMenipis" :key="bahan.id_bahan_baku" class="flex items-center justify-between p-2 rounded-lg bg-white/50 dark:bg-black/20">
                                        <span class="text-xs font-bold opacity-80">{{ bahan.nama_bahan_baku }}</span>
                                        <span class="text-[10px] font-black text-error animate-pulse">
                                            Sisa: {{ bahan.stok_sekarang }} {{ bahan.satuan }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
