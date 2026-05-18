<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';

const props = defineProps({
    pesanan: Object
});

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka);
};

const formatTanggal = (tgl) => {
    if (!tgl) return '-';
    return String(tgl).replace('T', ' ').substring(0, 16);
};

// Menghitung akumulasi total belanja nota bray
const totalBelanjaItems = computed(() => {
    const items = props.pesanan.pesanan_item || [];
    return items.reduce((total, item) => {
        const costBarang = parseFloat(item.harga_satuan_snapshot || 0) * parseInt(item.jumlah || 0);
        const costSlaFlat = parseFloat(item.harga_pengerjaan_snapshot || 0);
        return total + costBarang + costSlaFlat;
    }, 0);
});

// Penanda warna badge operasional
const statusOperasionalClass = (status) => {
    switch (status) {
        case 'menunggu_diproses': return 'bg-info/10 text-info border-info/20';
        case 'proses_pengerjaan': return 'bg-warning/10 text-warning border-warning/20';
        case 'proses_pengantaran': return 'bg-accent/10 text-accent border-accent/20';
        case 'selesai': return 'bg-success/10 text-success border-success/20';
        case 'batal': return 'opacity-40 bg-error/10 text-error border-error/20 line-through';
        default: return 'bg-base-300 text-base-content';
    }
};
</script>

<template>
    <Head :title="`Detail Pesanan #${pesanan.id_pesan}`" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Detail Pesanan: {{ pesanan.id_pesan }}
            </h2>
        </template>

        <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <div class="lg:col-span-8 space-y-4">
                    <div class="p-6 border rounded-3xl shadow-sm bg-base-100 border-base-300">
                        <h3 class="text-xs font-black uppercase tracking-widest opacity-50 mb-4 border-b pb-2">Daftar Item Cetak</h3>

                        <div class="divide-y divide-base-200">
                            <div
                                v-for="(item, index) in pesanan.pesanan_item"
                                :key="item.id"
                                class="py-5 first:pt-0 last:pb-0 flex flex-col md:flex-row justify-between gap-4"
                            >
                                <div class="space-y-1.5 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-[9px] font-mono font-black uppercase bg-base-200 px-2 py-0.5 rounded text-base-content/70">
                                            Item #{{ index + 1 }}
                                        </span>
                                        <span class="text-[10px] font-black uppercase text-warning tracking-wider">
                                            ⏳ SLA: {{ item.estimasi_pengerjaan_snapshot }}
                                        </span>
                                    </div>

                                    <h4 class="font-black text-base text-base-content">{{ item.nama_produk_snapshot }}</h4>

                                    <div v-if="item.pesanan_item_finishing?.length" class="pl-3 border-l-2 border-base-300 space-y-0.5 text-xs font-bold opacity-70">
                                        <p v-for="fin in item.pesanan_item_finishing" :key="fin.id">
                                            + {{ fin.nama_finishing_snapshot }} <span class="text-[10px] opacity-60">({{ formatRupiah(fin.harga_finishing_snapshot) }})</span>
                                        </p>
                                    </div>

                                    <div v-if="item.catatan" class="p-3 bg-base-200/50 rounded-xl text-xs font-medium text-base-content/80 max-w-xl">
                                        <span class="font-bold block text-[10px] uppercase opacity-50 mb-0.5">Catatan Kasir/Desainer:</span>
                                        "{{ item.catatan }}"
                                    </div>
                                </div>

                                <div class="flex flex-col justify-between items-end shrink-0 gap-3 text-right">
                                    <div>
                                        <span class="text-xs font-bold opacity-50 block">{{ item.jumlah }} pcs × {{ formatRupiah(item.harga_satuan_snapshot) }}</span>
                                        <span v-if="item.harga_pengerjaan_snapshot > 0" class="text-[10px] font-bold text-warning block">+ Flat Fee SLA: {{ formatRupiah(item.harga_pengerjaan_snapshot) }}</span>
                                        <span class="font-black text-base text-base-content block mt-1">
                                            {{ formatRupiah((item.jumlah * item.harga_satuan_snapshot) + parseFloat(item.harga_pengerjaan_snapshot || 0)) }}
                                        </span>
                                    </div>

                                    <div class="w-full md:w-auto">
                                        <a
                                            v-if="item.file_desain"
                                            :href="`/storage/${item.file_desain}`"
                                            target="_blank"
                                            class="btn btn-xs btn-primary font-black rounded-lg uppercase tracking-wider px-3"
                                        >
                                            📁 Unduh Berkas Desain
                                        </a>
                                        <span v-else class="text-[10px] font-black uppercase bg-base-200 text-base-content/40 px-2.5 py-1 rounded-md tracking-wider">
                                            ❌ Tanpa File Upload
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-4">
                    <div class="p-6 border rounded-3xl shadow-sm bg-base-100 border-base-300 space-y-5">
                        <h3 class="text-xs font-black uppercase tracking-widest opacity-50 border-b pb-2">Ringkasan Nota</h3>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase tracking-widest opacity-40 block">Pelanggan</label>
                            <span class="text-sm font-black text-base-content block">{{ pesanan.customer?.user?.name || 'Walk-in / Umum' }}</span>
                            <span class="text-xs font-bold font-mono opacity-50 block">{{ pesanan.id_customer }}</span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase tracking-widest opacity-40 block">Alamat Pengiriman / Nota</label>
                            <p class="text-xs font-bold text-base-content leading-relaxed">{{ pesanan.id_alamat || '-' }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-2 border-t border-base-200">
                            <div>
                                <label class="text-[9px] font-black uppercase tracking-widest opacity-40 block">Tgl Masuk</label>
                                <span class="text-xs font-bold font-mono text-base-content">{{ formatTanggal(pesanan.tanggal_pesan) }}</span>
                            </div>
                            <div>
                                <label class="text-[9px] font-black uppercase tracking-widest opacity-40 block">Tgl Selesai</label>
                                <span class="text-xs font-bold font-mono text-base-content">{{ formatTanggal(pesanan.tanggal_selesai) }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-2 border-t border-base-200">
                            <div>
                                <label class="text-[9px] font-black uppercase tracking-widest opacity-40 block mb-1">Status Pembayaran</label>
                                <span
                                    class="px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-wider border block text-center"
                                    :class="pesanan.status_pembayaran === 'lunas' ? 'bg-success/10 text-success border-success/20' : 'bg-error/10 text-error border-error/20'"
                                >
                                    {{ pesanan.status_pembayaran }}
                                </span>
                            </div>
                            <div>
                                <label class="text-[9px] font-black uppercase tracking-widest opacity-40 block mb-1">Status Operasional</label>
                                <span
                                    class="px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-wider border block text-center"
                                    :class="statusOperasionalClass(pesanan.status_operasional)"
                                >
                                    {{ pesanan.status_operasional.replace('_', ' ') }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-4 border-t-2 border-dashed border-base-300 flex justify-between items-center">
                            <span class="text-xs font-black uppercase tracking-widest opacity-60">Total Tagihan</span>
                            <span class="text-xl font-black text-primary">{{ formatRupiah(totalBelanjaItems) }}</span>
                        </div>
                    </div>

                    <Link :href="route('pesan.index')" class="btn btn-sm btn-block btn-outline rounded-xl font-black tracking-wide uppercase text-xs">
                        Kembali ke List Nota
                    </Link>
                </div>

            </div>
        </div>
    </StafLayout>
</template>

<style scoped>
</style>
