<script setup>
import { computed } from 'vue';
import StafLayout from '@/Layouts/StafLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    pembayaran: Object,
});

// ==========================================
// KALKULASI TOTAL TAGIHAN AKURAT (VUE) - METERAN FIX
// ==========================================
const getTagihanMurni = (pesan) => {
    if (!pesan) return 0;
    let totalMurniProduk = 0;
    let totalPengerjaan = 0;

    if (pesan.pesanan_item && Array.isArray(pesan.pesanan_item)) {
        pesan.pesanan_item.forEach(item => {
            let hargaAwal = Number(item.harga_satuan_snapshot) || 0;
            let atribut = {};
            const finishings = item.pesanan_item_finishing || item.finishing || [];

            if (item.atribut_custom_snapshot) {
                if (typeof item.atribut_custom_snapshot === 'string') {
                    try { atribut = JSON.parse(item.atribut_custom_snapshot); } catch (e) {}
                } else {
                    atribut = item.atribut_custom_snapshot;
                }
            }

            // 1. LOGIC BUKU
            if (atribut && atribut['Jumlah Halaman'] !== undefined) {
                let sisi = 1;
                finishings.forEach(f => {
                    const namaFin = (f.nama_finishing_snapshot || '').toLowerCase();
                    if (namaFin.includes('dua sisi') || namaFin.includes('2 sisi') || namaFin.includes('bolak')) {
                        sisi = 2;
                    }
                });
                let hal = parseInt(String(atribut['Jumlah Halaman']), 10);
                if (isNaN(hal) || hal < 1) hal = 1;
                hargaAwal += (Math.max(0, hal - 1) * sisi * 1500);
            }
            // 2. LOGIC METERAN
            else if (atribut && atribut['Luas Dihargai (m2)'] !== undefined) {
                let luas = parseFloat(String(atribut['Luas Dihargai (m2)'])) || 1;
                if (luas < 1) luas = 1;
                hargaAwal = hargaAwal * luas; // Kalikan dengan Luas Bahan
            }

            // 3. Kalikan Harga Dasar dengan QTY
            let subtotalItem = hargaAwal * (Number(item.jumlah) || 1);

            // 4. Hitung Tambahan Finishing
            finishings.forEach(f => {
                const isKaliQty = Boolean(f.sku_finishing?.kali_jumlah_pesan) || Boolean(f.kali_jumlah_pesan);
                let val = f.tipe === 'persen'
                    ? (hargaAwal * (Number(f.harga_finishing_snapshot) / 100))
                    : (Number(f.harga_finishing_snapshot) || 0);

                subtotalItem += (isKaliQty ? val * (Number(item.jumlah) || 1) : val);
            });

            totalMurniProduk += subtotalItem;
            totalPengerjaan += Number(item.harga_pengerjaan_snapshot) || 0;
        });
    }

    const ongkir = Number(pesan.harga_ongkir || 0);
    const diskon = Number(pesan.diskon_voucher_nominal || 0);

    return totalMurniProduk + totalPengerjaan + ongkir - diskon;
};

// Hitung Tagihan secara Real-Time
const computedTotalTagihan = computed(() => getTagihanMurni(props.pembayaran?.pesan));

// Hitung Transfer = Tagihan + Kode Unik
const computedTotalTransfer = computed(() => computedTotalTagihan.value + Number(props.pembayaran?.kode_unik || 0));
// ==========================================


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
                                <!-- PAKAI computedTotalTagihan DI SINI -->
                                <span class="font-semibold">{{ formatRupiah(computedTotalTagihan) }}</span>
                            </div>
                            <div class="flex items-center justify-between pb-2 text-sm border-b border-base-300">
                                <span class="text-base-content/70">Kode Unik:</span>
                                <span class="font-semibold text-warning">+ {{ pembayaran?.kode_unik }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-2">
                                <span class="font-bold">Total Yang Harus Ditransfer:</span>
                                <!-- PAKAI computedTotalTransfer DI SINI -->
                                <span class="text-lg font-black text-success">{{ formatRupiah(computedTotalTransfer) }}</span>
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
