<script setup>
import { computed } from 'vue';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomTable from '@/Components/CustomTable.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    customer: Object,
    statistik: Object,
    riwayat_bulan_ini: Array,
    pesanan_piutang: Array,
});

// Helper format Rupiah
const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(angka || 0);
};

const formatTanggal = (tgl) => {
    if (!tgl) return '-';
    const date = new Date(tgl);
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
};

// Cek apakah tembus 10 juta
const isEligibleForUpgrade = computed(() => {
    return props.statistik.total_belanja_bulan_ini >= 10000000;
});

const headerPiutang = ['ID Pesanan', 'Tanggal Pesan', 'Total Tagihan', 'Sisa Tagihan (Piutang)', 'Aksi'];
const headerRiwayat = ['ID Pesanan', 'Tanggal Pesan', 'Status Operasional', 'Total Tagihan'];
</script>

<template>
    <Head :title="`Rekap Customer - ${customer.user?.name}`" />
    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('customer.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Rekap Pesanan Customer
                    </h2>
                </div>
            </div>
        </template>

        <div class="min-h-screen px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">

                <!-- IDENTITAS CUSTOMER -->
                <div class="p-6 mb-6 bg-base-100 rounded-2xl shadow-sm border border-base-content/5 flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-primary">{{ customer.user?.name }}</h3>
                        <div class="flex items-center gap-4 mt-2 text-sm font-medium text-base-content/60">
                            <span class="font-mono">{{ customer.id_customer }}</span>
                            <span>&bull;</span>
                            <span>{{ customer.no_hp }}</span>
                            <span>&bull;</span>
                            <span>{{ customer.user?.email }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-bold tracking-widest text-base-content/40 uppercase mb-1">Level Saat Ini</div>
                        <span class="px-3 py-1 rounded-lg bg-success/10 text-success text-sm font-black uppercase border border-success/20">
                            {{ customer.role_customer?.role || customer.role_customer_id }}
                        </span>
                    </div>
                </div>

                <!-- SMART ALERT: UPGRADE ROLE -->
                <div v-if="isEligibleForUpgrade" class="p-4 mb-6 rounded-xl bg-warning/10 border border-warning/30 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="p-2 bg-warning/20 text-warning rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-warning-content">Eligible untuk Upgrade Level!</h4>
                            <p class="text-sm font-medium opacity-80 text-warning-content">
                                Total belanja bulan ini sudah mencapai {{ formatRupiah(statistik.total_belanja_bulan_ini) }}.
                            </p>
                        </div>
                    </div>
                    <Link :href="route('customer.edit', customer.id_customer)" class="px-4 py-2 text-sm font-bold bg-warning text-warning-content rounded-lg hover:bg-warning/80 transition-colors">
                        Update Role Sekarang
                    </Link>
                </div>

                <!-- WIDGET STATISTIK -->
                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">
                    <!-- Widget 1: Order Bulan Ini -->
                    <div class="p-6 bg-base-100 rounded-2xl shadow-sm border border-base-content/5">
                        <div class="text-sm font-bold text-base-content/50 uppercase tracking-wider mb-2">Total Order Bulan Ini</div>
                        <div class="text-3xl font-black text-base-content">
                            {{ statistik.total_order_bulan_ini }} <span class="text-base font-bold text-base-content/40">Kali</span>
                        </div>
                    </div>

                    <!-- Widget 2: Belanja Bulan Ini -->
                    <div class="p-6 bg-base-100 rounded-2xl shadow-sm border border-base-content/5">
                        <div class="text-sm font-bold text-base-content/50 uppercase tracking-wider mb-2">Total Belanja Bulan Ini</div>
                        <div class="text-3xl font-black text-primary">
                            {{ formatRupiah(statistik.total_belanja_bulan_ini) }}
                        </div>
                    </div>

                    <!-- Widget 3: Piutang Berjalan -->
                    <div class="p-6 bg-error/5 rounded-2xl shadow-sm border border-error/20 relative overflow-hidden">
                        <div class="text-sm font-bold text-error/70 uppercase tracking-wider mb-2">Total Piutang Berjalan</div>
                        <div class="text-3xl font-black text-error">
                            {{ formatRupiah(statistik.total_piutang) }}
                        </div>
                        <svg class="absolute -bottom-4 -right-4 w-24 h-24 opacity-10 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>

                <!-- TABEL DATA -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    <!-- Kiri: Piutang -->
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-2 h-6 bg-error rounded-full"></div>
                            <h3 class="text-lg font-bold text-base-content">Daftar Tagihan Belum Lunas</h3>
                        </div>
                        <CustomTable :headers="headerPiutang">
                            <tr v-for="piutang in pesanan_piutang" :key="piutang.id_pesan" class="transition-colors hover:bg-base-200/50">
                                <td class="px-4 py-3 font-mono text-xs font-bold text-primary">{{ piutang.id_pesan }}</td>
                                <td class="px-4 py-3 text-xs font-medium">{{ formatTanggal(piutang.tanggal) }}</td>
                                <td class="px-4 py-3 text-xs font-medium">{{ formatRupiah(piutang.total_tagihan) }}</td>
                                <td class="px-4 py-3 text-xs font-bold text-error">{{ formatRupiah(piutang.sisa_tagihan) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <!-- Arahkan ke halaman detail/pembayaran pesanan -->
                                    <Link :href="route('pesan.detail', piutang.id_pesan)" class="text-[10px] px-3 py-1.5 bg-primary/10 text-primary hover:bg-primary hover:text-primary-content transition-colors rounded-lg font-bold">
                                        Bayar
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="pesanan_piutang.length === 0">
                                <td colspan="5" class="px-6 py-10 text-center text-sm font-bold text-base-content/40">
                                    Mantap! Tidak ada tagihan yang tertunggak.
                                </td>
                            </tr>
                        </CustomTable>
                    </div>

                    <!-- Kanan: Riwayat Bulan Ini -->
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-2 h-6 bg-primary rounded-full"></div>
                            <h3 class="text-lg font-bold text-base-content">Riwayat Order (Bulan Ini)</h3>
                        </div>
                        <CustomTable :headers="headerRiwayat">
                            <tr v-for="riwayat in riwayat_bulan_ini" :key="riwayat.id_pesan" class="transition-colors hover:bg-base-200/50">
                                <td class="px-4 py-3 font-mono text-xs font-bold text-primary">
                                    <Link :href="route('pesan.detail', riwayat.id_pesan)" class="hover:underline">
                                        {{ riwayat.id_pesan }}
                                    </Link>
                                </td>
                                <td class="px-4 py-3 text-xs font-medium">{{ formatTanggal(riwayat.tanggal) }}</td>
                                <td class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider">
                                    {{ riwayat.status_operasional.replace('_', ' ') }}
                                </td>
                                <td class="px-4 py-3 text-xs font-bold">{{ formatRupiah(riwayat.total_tagihan) }}</td>
                            </tr>
                            <tr v-if="riwayat_bulan_ini.length === 0">
                                <td colspan="4" class="px-6 py-10 text-center text-sm font-bold text-base-content/40">
                                    Belum ada pesanan di bulan ini.
                                </td>
                            </tr>
                        </CustomTable>
                    </div>

                </div>

            </div>
        </div>
    </StafLayout>
</template>
