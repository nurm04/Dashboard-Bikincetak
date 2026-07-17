<script setup>
const props = defineProps({
    total_tagihan: Number,
    harga_ongkir: Number,
    diskon_voucher_nominal: Number,
    kode_voucher: String,
    kode_unik: Number,
    total_transfer: Number,
    total_dibayar: Number,
    sisa_tagihan: Number
});

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka || 0);
};
</script>

<template>
    <div class="p-5 space-y-4 border shadow-sm bg-base-100 border-base-300 rounded-2xl">
        <h3 class="pb-2 text-[10px] font-black tracking-widest uppercase border-b opacity-50 border-base-200">Rincian Tagihan</h3>

        <div class="space-y-2.5">
            <div class="flex justify-between items-center text-[11px]">
                <span class="font-bold opacity-70">Total Produk</span>
                <span class="font-mono font-bold">{{ formatRupiah(total_tagihan) }}</span>
            </div>
            <div class="flex justify-between items-center text-[11px]">
                <span class="font-bold opacity-70">Ongkos Kirim</span>
                <span class="font-mono font-bold" :class="harga_ongkir > 0 ? 'text-success' : ''">+ {{ formatRupiah(harga_ongkir || 0) }}</span>
            </div>

            <div v-if="diskon_voucher_nominal > 0" class="flex justify-between items-center text-[11px]">
                <span class="font-bold truncate opacity-70 max-w-30">Voucher ({{ kode_voucher || 'Promo' }})</span>
                <span class="font-mono font-bold text-error">- {{ formatRupiah(diskon_voucher_nominal) }}</span>
            </div>

            <div v-if="kode_unik" class="flex justify-between items-center text-[11px]">
                <span class="font-bold opacity-70">Kode Unik</span>
                <span class="font-mono font-bold text-warning">+ {{ formatRupiah(kode_unik) }}</span>
            </div>
        </div>

        <div class="pt-3 border-t-2 border-dashed border-base-300">
            <div class="flex flex-col items-end">
                <span class="text-[9px] font-black uppercase tracking-widest opacity-40 mb-1">Grand Total</span>
                <span class="text-xl font-black leading-none tracking-tighter text-primary">{{ formatRupiah(total_transfer) }}</span>
            </div>
        </div>

        <div class="pt-3 mt-1 space-y-2 border-t border-base-200">
            <div class="flex justify-between items-center text-[11px]">
                <span class="font-bold opacity-70">Telah Dibayar (DP)</span>
                <span class="font-mono font-bold text-success">- {{ formatRupiah(total_dibayar) }}</span>
            </div>
            <div class="flex items-center justify-between p-2 mt-2 border rounded-lg bg-error/10 border-error/20">
                <span class="font-black text-error text-[11px]">Sisa Tagihan</span>
                <span class="font-mono text-xs font-black text-error">{{ formatRupiah(sisa_tagihan) }}</span>
            </div>
        </div>
    </div>
</template>
