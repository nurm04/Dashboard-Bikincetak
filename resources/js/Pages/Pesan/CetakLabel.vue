<script setup>
import { onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    pesanan: Object,
});

onMounted(() => {
    setTimeout(() => {
        window.print();
    }, 500);
});

const isAmbilDiToko = computed(() => {
    return !props.pesanan.ekspedisi_nama || props.pesanan.ekspedisi_nama === 'Ambil di Toko';
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

const totalBerat = computed(() => {
    if (!props.pesanan || !props.pesanan.pesanan_item) return 0;
    return props.pesanan.pesanan_item.reduce((sum, item) => sum + (Number(item.total_berat_snapshot) || 0), 0);
});
</script>

<template>
    <Head :title="`Label Pengiriman - ${pesanan.kode_transaksi}`" />

    <div class="flex justify-center min-h-screen p-4 font-sans text-black bg-gray-100 print:p-0 print:bg-white">

        <!-- Standar ukuran resi logistik biasanya A6 (sekitar 105mm x 148mm) -->
        <div class="w-full max-w-[105mm] bg-white print:w-full print:max-w-none mx-auto shadow-lg print:shadow-none print:m-0 overflow-hidden border-2 border-black print:border-none">

            <div class="p-4 print:p-2">

                <!-- HEADER LOGO & JENIS NOTA -->
                <div class="flex items-center justify-between pb-3 mb-3 border-b-4 border-black">
                    <div>
                        <h1 class="text-2xl font-black tracking-tighter uppercase leading-none">BIKIN CETAK</h1>
                        <p class="text-[8px] font-black tracking-widest uppercase mt-0.5">Platform Cetak Digital</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-2 py-1 text-[10px] font-black tracking-widest text-white uppercase bg-black">
                            {{ isAmbilDiToko ? 'AMBIL DI TOKO' : 'PENGIRIMAN' }}
                        </span>
                        <p class="mt-1 text-[9px] font-bold uppercase tracking-wider">TGL: {{ formatTanggal(pesanan.tanggal_pesan) }}</p>
                    </div>
                </div>

                <!-- RESI / KODE TRANSAKSI SANGAT BESAR -->
                <div class="mb-4 text-center">
                    <p class="text-[10px] font-black uppercase tracking-widest mb-0.5">
                        {{ pesanan.nomor_resi ? 'NOMOR RESI EKPEDISI' : 'KODE TRANSAKSI' }}
                    </p>
                    <p class="text-3xl font-black uppercase tracking-widest leading-none">
                        {{ pesanan.nomor_resi || pesanan.kode_transaksi }}
                    </p>
                    <!-- Jika ada resi, kode transaksi jadi referensi kecil di bawahnya -->
                    <p v-if="pesanan.nomor_resi" class="mt-1 text-[9px] font-bold uppercase tracking-widest">
                        REF: {{ pesanan.kode_transaksi }}
                    </p>
                </div>

                <!-- KURIR & BERAT (Dibuat mencolok) -->
                <div v-if="!isAmbilDiToko" class="flex border-y-4 border-black">
                    <div class="flex flex-col justify-center w-3/4 p-2 border-r-4 border-black">
                        <p class="text-[9px] font-black uppercase tracking-widest mb-0.5">Kurir & Layanan:</p>
                        <p class="text-xl font-black uppercase leading-tight">{{ pesanan.ekspedisi_nama }} - {{ pesanan.ekspedisi_layanan }}</p>
                    </div>
                    <div class="flex flex-col items-center justify-center w-1/4 p-2 text-center bg-black text-white">
                        <p class="text-[9px] font-bold uppercase tracking-widest mb-0.5">Berat</p>
                        <p class="text-lg font-black leading-none">{{ totalBerat }}</p>
                        <p class="text-[9px] font-bold mt-0.5 uppercase">Gram</p>
                    </div>
                </div>

                <!-- ALAMAT PENERIMA & PENGIRIM -->
                <div class="flex flex-col border-b-4 border-black">
                    <!-- PENERIMA -->
                    <div class="p-3">
                        <p class="text-[10px] font-black uppercase tracking-widest mb-1 text-black bg-gray-200 inline-block px-1">KEPADA / PENERIMA:</p>
                        <p class="text-sm font-black uppercase">{{ pesanan.alamat?.nama_penerima || pesanan.customer?.user?.name || 'Walk-in / Umum' }}</p>
                        <p class="text-xs font-black">{{ pesanan.alamat?.no_hp || '-' }}</p>
                        <p class="text-[11px] font-bold leading-snug mt-1.5 uppercase">
                            {{ pesanan.alamat?.alamat_lengkap || 'Ambil Langsung di Toko' }}
                            <template v-if="pesanan.alamat">
                                <br>{{ pesanan.alamat.kecamatan }}, {{ pesanan.alamat.kota }}
                                <br>{{ pesanan.alamat.provinsi }} {{ pesanan.alamat.kode_pos }}
                            </template>
                        </p>
                    </div>
                </div>

                <!-- DAFTAR ISI PAKET -->
                <div class="pt-3">
                    <p class="text-[10px] font-black uppercase tracking-widest border-b-2 border-black pb-1 mb-2">
                        Isi Paket ({{ pesanan.pesanan_item?.length || 0 }} Item):
                    </p>
                    <ul class="space-y-2">
                        <li v-for="(item, index) in pesanan.pesanan_item" :key="item.id" class="text-xs">
                            <div class="flex items-start justify-between font-black uppercase leading-tight">
                                <span class="w-[85%]">{{ index + 1 }}. {{ cleanProductName(item.nama_produk_snapshot) }}</span>
                                <span class="w-[15%] text-right whitespace-nowrap">{{ item.jumlah }} PCS</span>
                            </div>
                            <div v-if="item.pesanan_item_finishing?.length" class="pl-4 mt-0.5 text-[9px] font-bold uppercase opacity-80 flex flex-wrap gap-x-2">
                                <span v-for="fin in item.pesanan_item_finishing" :key="fin.id">
                                    ▸ {{ fin.nama_finishing_snapshot }}
                                </span>
                            </div>
                            <div v-if="item.catatan" class="pl-4 mt-0.5 text-[9px] font-bold italic">
                                Note: "{{ item.catatan }}"
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@page {
    /* Auto menyesuaikan dengan kertas di printer (bisa thermal A6 atau print A4 biasa) */
    size: auto;
    margin: 2mm;
}

@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
