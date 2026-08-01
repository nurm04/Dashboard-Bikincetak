<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    pembayaran: Object,
});

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka);
};

const getStatusClass = (status) => {
    switch (status) {
        case 'lunas': return 'bg-success/20 text-success border-success/30';
        case 'dibayar_sebagian': return 'bg-warning/20 text-warning border-warning/30';
        case 'belum_lunas': return 'bg-error/20 text-error border-error/30';
        default: return 'bg-base-200 text-base-content';
    }
};

const formatStatus = (status) => {
    if (!status) return 'Menunggu Data';
    return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const formatTanggalBayar = (id) => {
    if (!id) return '-';

    const parts = id.split('-');
    if (parts.length >= 2 && parts[1].length === 6) {
        const dateStr = parts[1];

        const year = '20' + dateStr.substring(0, 2);
        const month = parseInt(dateStr.substring(2, 4)) - 1;
        const day = dateStr.substring(4, 6);

        const dateObj = new Date(year, month, day);

        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        }).format(dateObj);
    }

    return '-';
};
</script>

<template>
    <Head :title="`Detail Pembayaran - ${pembayaran?.id_pembayaran}`" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('pembayaran.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Detail Pembayaran
                    </h2>
                </div>
            </div>
        </template>

        <div class="min-h-screen px-4 py-6 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto space-y-6">

                <div class="p-6 border rounded-xl bg-base-100 border-base-300">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-base-content/60">ID Pembayaran</p>
                            <h3 class="font-mono text-2xl font-bold text-primary">{{ pembayaran?.id_pembayaran }}</h3>
                            <p class="mt-1 text-sm text-base-content/60">ID Pesanan: <span class="font-mono font-semibold">{{ pembayaran?.id_pesan }}</span></p>
                        </div>
                        <div class="text-left md:text-right">
                            <span :class="`px-4 py-2 border rounded-full text-sm font-bold tracking-wide uppercase ${getStatusClass(pembayaran?.pesan?.status_pembayaran)}`">
                                {{ formatStatus(pembayaran?.pesan?.status_pembayaran) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="p-6 border rounded-xl bg-base-100 border-base-300">
                        <h4 class="pb-2 mb-4 text-lg font-bold border-b">Informasi Pelanggan</h4>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs font-semibold uppercase text-base-content/60">Nama Pelanggan</p>
                                <p class="font-bold">{{ pembayaran?.pesan?.customer?.user?.name || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-base-content/60">Email</p>
                                <p class="font-medium">{{ pembayaran?.pesan?.customer?.user?.email || '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border rounded-xl bg-base-100 border-base-300">
                        <h4 class="pb-2 mb-4 text-lg font-bold border-b">Informasi Staf / Kasir</h4>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs font-semibold uppercase text-base-content/60">Nama Staf</p>
                                <p class="font-bold">{{ pembayaran?.staf?.user?.name || 'Belum ditangani' }}</p>
                            </div>
                            <div v-if="pembayaran?.staf">
                                <p class="text-xs font-semibold uppercase text-base-content/60">No HP Staf</p>
                                <p class="font-medium">{{ pembayaran?.staf?.no_hp || '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 border rounded-xl bg-base-100 border-base-300">
                    <h4 class="pb-2 mb-4 text-lg font-bold border-b">Rincian Transaksi</h4>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs font-semibold uppercase text-base-content/60">Metode Pembayaran</p>
                                <p class="font-bold uppercase">{{ pembayaran?.metode_pembayaran || '-' }} <span v-if="pembayaran?.payment_type_detail" class="text-sm font-normal normal-case">({{ pembayaran.payment_type_detail }})</span></p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase text-base-content/60">Tanggal Pembayaran</p>
                                <p class="font-bold">{{ formatTanggalBayar(pembayaran?.id_pembayaran) }}</p>
                            </div>
                        </div>

                        <div class="p-4 space-y-2 rounded-lg bg-base-200/50">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-base-content/70">Total Tagihan Pesanan:</span>
                                <span class="font-semibold">{{ formatRupiah(pembayaran?.total_tagihan) }}</span>
                            </div>
                            <div class="flex items-center justify-between pb-2 text-sm border-b border-base-300">
                                <span class="text-base-content/70">Kode Unik:</span>
                                <span class="font-semibold text-warning">+ {{ pembayaran?.kode_unik }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-2">
                                <span class="font-bold">Total Yang Harus Ditransfer:</span>
                                <span class="text-lg font-black text-success">{{ formatRupiah(pembayaran?.total_transfer) }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-2 mt-2 border-t border-base-300">
                                <span class="font-bold text-base-content/70">Nominal Dibayar:</span>
                                <span class="text-lg font-black text-primary">{{ formatRupiah(pembayaran?.nominal_bayar) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 mt-6 border-t border-base-300" v-if="pembayaran?.catatan">
                        <p class="mb-1 text-xs font-semibold uppercase text-base-content/60">Catatan</p>
                        <p class="p-3 text-sm italic rounded-md bg-base-200">{{ pembayaran.catatan }}</p>
                    </div>
                </div>

            </div>
        </div>
    </StafLayout>
</template>
