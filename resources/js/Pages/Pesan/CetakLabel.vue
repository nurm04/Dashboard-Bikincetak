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
    return String(tgl).replace('T', ' ').substring(0, 16);
};

const totalBerat = computed(() => {
    if (!props.pesanan || !props.pesanan.pesanan_item) return 0;
    return props.pesanan.pesanan_item.reduce((sum, item) => sum + (Number(item.total_berat_snapshot) || 0), 0);
});
</script>

<template>
    <Head :title="`Cetak Label - ${pesanan.id_pesan}`" />

    <!-- Wrapper paling luar: Dibuat flex untuk menengahkan konten -->
    <div class="flex justify-center min-h-screen p-4 font-sans text-black bg-white print:p-0 print:bg-white">

        <!-- Wrapper dalam: Di-set ukurannya spesifik untuk print (18cm) agar pas di tengah kertas A4 -->
        <div class="w-full max-w-2xl print:w-[18cm] mx-auto print:mt-12 print:mb-12">

            <!-- Kotak panduan potong -->
            <div class="p-6 border-2 border-gray-400 border-dashed print:border-none print:p-0">

                <!-- HEADER LOGO & RESI -->
                <div class="flex items-start justify-between pb-4 mb-4 border-b-2 border-black">
                    <div>
                        <h1 class="text-3xl font-black tracking-tighter uppercase">BIKIN CETAK</h1>
                        <p class="mt-1 text-xs font-bold">Platform Cetak Digital Terpercaya</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold uppercase">{{ isAmbilDiToko ? 'NOTA AMBIL DI TOKO' : 'LABEL PENGIRIMAN' }}</p>
                        <p class="mt-1 font-mono text-2xl font-black">{{ pesanan.nomor_resi || pesanan.id_pesan }}</p>
                        <p class="text-[10px] font-bold">Tgl Order: {{ formatTanggal(pesanan.tanggal_pesan) }}</p>
                    </div>
                </div>

                <!-- INFO PENGIRIM & PENERIMA -->
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- PENGIRIM -->
                    <div class="space-y-1">
                        <p class="text-[10px] uppercase font-bold text-gray-500">PENGIRIM:</p>
                        <p class="text-sm font-black uppercase">BIKIN CETAK</p>
                        <p class="text-xs font-semibold">0857-8506-1834</p> <!-- Sesuai di screenshot lu -->
                        <p class="text-xs">Surabaya, Jawa Timur</p>
                    </div>

                    <!-- PENERIMA -->
                    <div class="space-y-1">
                        <p class="text-[10px] uppercase font-bold text-gray-500">PENERIMA:</p>
                        <p class="text-base font-black uppercase">{{ pesanan.alamat?.nama_penerima || pesanan.customer?.user?.name || 'UMUM' }}</p>
                        <p class="text-xs font-black">{{ pesanan.alamat?.no_hp || '-' }}</p>
                        <p class="text-xs max-w-62.5 leading-relaxed">
                            {{ pesanan.alamat?.alamat_lengkap || 'Ambil di Toko' }} <br>
                            <template v-if="pesanan.alamat">
                                {{ pesanan.alamat.kecamatan }}, {{ pesanan.alamat.kota }} <br>
                                {{ pesanan.alamat.provinsi }} {{ pesanan.alamat.kode_pos }}
                            </template>
                        </p>
                    </div>
                </div>

                <!-- INFO EKSPEDISI (Jika bukan ambil di toko) -->
                <div v-if="!isAmbilDiToko" class="flex items-center justify-between p-3 mb-6 border-2 border-black">
                    <div>
                        <p class="text-[10px] uppercase font-bold">KURIR & LAYANAN</p>
                        <p class="text-lg font-black uppercase">{{ pesanan.ekspedisi_nama }} - {{ pesanan.ekspedisi_layanan }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase font-bold">BERAT</p>
                        <p class="text-lg font-black">{{ totalBerat }} <span class="text-sm">gr</span></p>
                    </div>
                </div>

                <!-- RINCIAN BARANG -->
                <div class="mt-4">
                    <p class="text-[10px] uppercase font-bold text-gray-500 mb-2 border-b border-gray-300 pb-1">ISI PAKET ({{ pesanan.pesanan_item?.length || 0 }} ITEM):</p>
                    <ul class="space-y-3">
                        <li v-for="(item, index) in pesanan.pesanan_item" :key="item.id" class="text-xs">
                            <div class="flex justify-between font-bold">
                                <span>{{ index + 1 }}. {{ item.nama_produk_snapshot }}</span>
                                <span>{{ item.jumlah }} pcs</span>
                            </div>
                            <div v-if="item.pesanan_item_finishing?.length" class="pl-3 text-[10px] text-gray-600 mt-0.5">
                                <span v-for="fin in item.pesanan_item_finishing" :key="fin.id" class="mr-2">
                                    + {{ fin.nama_finishing_snapshot }}
                                </span>
                            </div>
                            <div v-if="item.catatan" class="pl-3 text-[10px] font-bold mt-0.5">
                                Note: "{{ item.catatan }}"
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- FOOTER -->
                <div class="mt-8 pt-4 border-t border-gray-300 text-center text-[9px] font-bold text-gray-500">
                    Dokumen ini dicetak otomatis oleh Sistem BikinCetak.<br>
                    Terima kasih atas pesanan Anda.
                </div>

                <!-- Tombol Tutup -->
                <div class="mt-8 text-center print:hidden">
                    <button @click="() => window.close()" class="px-6 py-2 text-sm font-bold text-black bg-gray-200 border rounded-xl hover:bg-gray-300">
                        Tutup Halaman Ini
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>

<style>
@page {
    size: auto;
    margin: 0mm;
}
</style>
