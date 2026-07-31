<script setup>
import { onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    item: Object,
});

onMounted(() => {
    setTimeout(() => {
        window.print();
    }, 500);
});

const isAmbilDiToko = computed(() => {
    return !props.item?.pesan?.ekspedisi_nama || props.item?.pesan?.ekspedisi_nama === 'Ambil di Toko';
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
const cleanProductName = (name) => {
    if (!name) return '';
    return name.replace(/^[A-Za-z]+-\d+-/, '').replace(/-/g, ' ');
};
</script>

<template>
    <Head :title="`Label SPK - ${cleanProductName(item?.nama_produk_snapshot)}`" />

    <!-- Print styling dioptimalkan khusus printer thermal (78mm - 80mm) -->
    <div class="flex justify-center min-h-screen p-4 font-sans text-black bg-gray-100 print:p-0 print:bg-white">

        <!-- Lebar standar Thermal Printer 80mm adalah sekitar 302px (80mm) -->
        <div class="w-full max-w-75.5 bg-white print:w-full print:max-w-none print:m-0 shadow-lg print:shadow-none mx-auto overflow-hidden">

            <div class="p-3 print:p-1">

                <!-- HEADER LOGO & INFO ORDER -->
                <div class="pb-2 mb-2 text-center border-b-2 border-black border-dashed">
                    <h1 class="text-2xl font-black tracking-tighter uppercase">SPK PRODUKSI</h1>
                    <p class="mt-0.5 text-[9px] font-bold tracking-widest uppercase">BIKIN CETAK - PLATFORM DIGITAL</p>

                    <div class="flex flex-col items-center justify-center mt-3">
                        <span class="text-[8px] font-bold uppercase mb-0.5 tracking-wider">Kode Transaksi:</span>
                        <!-- Kode Transaksi Ditebalkan -->
                        <span class="font-mono text-lg font-black tracking-widest text-white bg-black px-2 py-0.5">
                            {{ item?.pesan?.kode_transaksi }}
                        </span>
                    </div>
                </div>

                <!-- INFO PRODUK UTAMA -->
                <div class="mb-3 text-center">
                    <p class="text-[9px] uppercase font-bold text-black mb-1 tracking-wider border-b border-black inline-block pb-0.5">DETAIL ITEM</p>
                    <h2 class="text-sm font-black leading-tight uppercase px-1">{{ cleanProductName(item?.nama_produk_snapshot) }}</h2>

                    <!-- QTY Sangat Besar agar terlihat jelas oleh operator -->
                    <div class="flex items-baseline justify-center gap-1 mt-1">
                        <span class="font-mono text-4xl font-black">{{ item?.jumlah }}</span>
                        <span class="text-sm font-black uppercase">PCS</span>
                    </div>

                    <div v-if="(item?.harga_pengerjaan_snapshot || 0) > 0" class="inline-block px-3 py-1 mt-1 text-[10px] font-black tracking-widest text-black uppercase border-2 border-black">
                        SLA: {{ item?.estimasi_pengerjaan_snapshot }}
                    </div>
                </div>

                <!-- RINCIAN FINISHING & CATATAN -->
                <div class="p-2 mb-3 border-2 border-black">
                    <p class="text-[9px] uppercase font-black tracking-widest border-b border-black pb-1 mb-1">PANDUAN FINISHING:</p>

                    <ul v-if="item?.pesanan_item_finishing?.length" class="pl-4 mb-2 text-xs font-black uppercase list-disc list-outside">
                        <li v-for="fin in item.pesanan_item_finishing" :key="fin.id" class="leading-snug mb-0.5">
                            {{ fin.nama_finishing_snapshot }}
                        </li>
                    </ul>
                    <p v-else class="text-[10px] font-bold italic mb-2 text-center">- STANDAR (Tanpa Tambahan) -</p>

                    <div v-if="item?.catatan" class="pt-1 mt-1 border-t border-black border-dashed">
                        <p class="text-[9px] font-black uppercase mb-0.5 tracking-widest">Catatan Customer:</p>
                        <p class="text-[11px] font-bold leading-tight italic">"{{ item?.catatan }}"</p>
                    </div>
                </div>

                <!-- INFO PENGIRIMAN FULL WIDTH -->
                <div class="pt-2 pb-2 mb-2 text-xs border-b-2 border-black border-dashed">

                    <div class="mb-2">
                        <span class="font-black uppercase text-[9px] block mb-0.5">Customer / Penerima:</span>
                        <span class="font-black text-[13px] uppercase block leading-tight">
                            {{ item?.pesan?.alamat?.nama_penerima || item?.pesan?.customer?.user?.name || 'Walk-in / Umum' }}
                        </span>
                        <!-- DETAIL ALAMAT DITAMBAHKAN DI SINI -->
                        <span class="block text-[10px] font-bold leading-tight mt-1">
                            {{ item?.pesan?.alamat?.no_hp || '-' }}<br>
                            {{ item?.pesan?.alamat?.alamat_lengkap || 'Ambil Langsung di Toko' }}<br>
                            <template v-if="item?.pesan?.alamat?.kota">
                                {{ item?.pesan?.alamat?.kecamatan }}, {{ item?.pesan?.alamat?.kota }}
                            </template>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mt-2 pt-2 border-t border-black border-dashed">
                        <div>
                            <span class="font-black uppercase text-[9px] block mb-0.5">Berat Item:</span>
                            <span class="font-bold text-[11px]">{{ item?.total_berat_snapshot || 0 }} gram</span>
                        </div>
                        <div>
                            <span class="font-black uppercase text-[9px] block mb-0.5">Tanggal Order:</span>
                            <span class="font-bold text-[11px]">{{ formatTanggal(item?.pesan?.tanggal_pesan) }}</span>
                        </div>
                    </div>

                    <div class="mt-2 p-1.5 border border-black bg-black text-white">
                        <span class="font-bold uppercase text-[9px] block mb-0.5 text-center tracking-widest">Kurir Pengiriman:</span>
                        <span class="font-black text-xs uppercase block leading-tight text-center">
                            {{ isAmbilDiToko ? 'AMBIL DI TOKO' : `${item?.pesan?.ekspedisi_nama} - ${item?.pesan?.ekspedisi_layanan}` }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@page {
    /* Mengamankan margin print untuk printer label roll 80mm */
    size: 80mm auto;
    margin: 2mm;
}

/* Memastikan background hitam/warna ikut tercetak di browser Webkit (Chrome/Edge) */
@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
