<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Printer, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    penawaran: Object,
});

// ==========================================
// UTILITIES FORMATTING
// ==========================================
const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(angka || 0).replace(/Rp/g, '').trim(); // Format angka doang tanpa Rp di depannya biar rapi di tabel
};

const formatDateSBY = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');

    return `Surabaya, ${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
};

const safeJSONParse = (data) => {
    if (!data) return null;
    try {
        return typeof data === 'string' ? JSON.parse(data) : data;
    } catch (e) {
        return null;
    }
};

// ==========================================
// PENGGABUNGAN NAMA BARANG (Sesuai Gambar)
// ==========================================
const getFullItemName = (item) => {
    let name = item.nama_produk_snapshot;

    // Gabung atribut (Bahan, Ukuran, dll)
    const attrs = safeJSONParse(item.atribut_custom_snapshot);
    if (attrs) {
        Object.values(attrs).forEach(val => {
            name += `_${val}`;
        });
    }

    // Gabung Finishing
    if (item.penawaran_item_finishing && item.penawaran_item_finishing.length > 0) {
        item.penawaran_item_finishing.forEach(fin => {
            // Bersihin prefix kategori kayak "Laminasi: Doff" jadi "Doff" aja kalau perlu
            let finName = fin.nama_finishing_snapshot.split(':').pop().trim();
            name += `_${finName}`;
        });
    }

    return name;
};

// ==========================================
// KALKULASI TOTAL
// ==========================================
const hitungTotalItem = (item) => {
    const qty = Number(item.jumlah) || 1;
    const hargaSatuan = Number(item.harga_satuan_snapshot) || 0;
    const hargaPengerjaan = Number(item.harga_pengerjaan_snapshot) || 0;

    let totalFinishing = 0;
    if (item.penawaran_item_finishing && item.penawaran_item_finishing.length > 0) {
        item.penawaran_item_finishing.forEach(f => {
            totalFinishing += (Number(f.harga_finishing_snapshot) || 0) * qty;
        });
    }

    return (hargaSatuan * qty) + totalFinishing + hargaPengerjaan;
};

const hitungHargaSatuanBersih = (item) => {
    // Harga satuan + harga finishing satuan
    const hargaSatuan = Number(item.harga_satuan_snapshot) || 0;
    let totalFinishing = 0;
    if (item.penawaran_item_finishing && item.penawaran_item_finishing.length > 0) {
        item.penawaran_item_finishing.forEach(f => {
            totalFinishing += Number(f.harga_finishing_snapshot) || 0;
        });
    }
    return hargaSatuan + totalFinishing;
}

const printDocument = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Surat Penawaran - ${penawaran.id_penawaran}`" />

    <!-- ACTION BAR (Sembunyi saat di-print) -->
    <div class="fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-6 py-3 bg-white border-b shadow-sm print:hidden border-base-300">
        <div class="flex items-center gap-4">
            <Link :href="route('penawaran.detail', penawaran.id_penawaran)" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                <ArrowLeft class="w-4 h-4" />
            </Link>
            <div class="font-bold text-base-content">Kembali ke Detail</div>
        </div>
        <button @click="printDocument" class="btn btn-primary btn-sm rounded-xl">
            <Printer class="w-4 h-4 mr-1" /> Cetak Sekarang (Ctrl+P)
        </button>
    </div>

    <!-- KERTAS A4 CONTAINER -->
    <div class="bg-gray-100 print:bg-transparent min-h-screen pt-20 pb-10 print:p-0 font-sans text-[13px] leading-relaxed text-black">

        <div class="w-[210mm] min-h-[297mm] mx-auto bg-white p-[15mm] shadow-lg print:shadow-none print:m-0 print:p-0">

            <!-- 1. NOMOR PENAWARAN -->
            <div class="flex justify-end mb-6 font-medium">
                <div class="flex w-64">
                    <span class="w-20">Nomor</span>
                    <span>: {{ penawaran.id_penawaran }}</span>
                </div>
            </div>

            <!-- 2. HEADER KOP SURAT -->
            <div class="flex items-end justify-between pb-3 border-b-2 border-black">
                <!-- Logo Kiri -->
                <div>
                    <h1 class="text-4xl font-black tracking-tight uppercase">
                        bikin cetak
                    </h1>
                    <p class="text-[10px] italic font-medium -mt-1">cetak online semakin mudah dan cepat</p>
                    <p class="text-[11px] italic font-semibold mt-1 text-gray-700">Digital Printing, Offset, Merhcandise</p>
                </div>
                <!-- Alamat Kanan -->
                <div class="text-right text-[11px] font-medium leading-tight">
                    <p>Jl Barata Jaya XVII No 3 Kec. Gubeng, Kota SBY, Jawa Timur 60284</p>
                    <p>Telpon : 0830831862770 | Email : bikinkancetak@gmail.com</p>
                </div>
            </div>

            <!-- 3. KEPADA & TANGGAL -->
            <div class="flex justify-between mt-6 mb-10">
                <div class="font-medium">
                    <p>Kepada Yth,</p>
                    <p class="font-bold">{{ penawaran.customer?.user?.name || '-' }}</p>
                    <p class="w-64 mt-1 text-xs">{{ penawaran.alamat?.alamat_lengkap }}</p>
                </div>
                <div class="italic text-[11px]">
                    {{ formatDateSBY(penawaran.tanggal_penawaran) }}
                </div>
            </div>

            <!-- 4. PEMBUKAAN -->
            <div class="mb-6 font-medium text-justify">
                <p class="mb-4">Dengan Hormat,</p>
                <p>Bersama surat ini kami mengajukan Penawaran harga jasa percetakan, sesuai dengan permintaan spesifikasi yang kami terima untuk mengerjakan pencetakan sebagai berikut :</p>
            </div>

            <!-- 5. TABEL ITEM -->
            <table class="w-full mb-8 text-left border-collapse">
                <thead>
                    <tr class="border-t border-b border-black border-y-2">
                        <th class="py-2.5 px-2 font-bold w-12 text-center">No</th>
                        <th class="py-2.5 px-2 font-bold">Nama Barang</th>
                        <th class="py-2.5 px-2 font-bold text-center w-20">Qty</th>
                        <th class="py-2.5 px-2 font-bold text-right w-28">Satuan Rp.</th>
                        <th class="py-2.5 px-2 font-bold text-right w-36">Jumlah Harga Rp.</th>
                    </tr>
                </thead>
                <tbody class="font-medium">
                    <tr v-for="(item, index) in penawaran.penawaran_item" :key="item.id" class="align-top">
                        <td class="px-2 py-2 text-center">{{ index + 1 }}</td>
                        <td class="px-2 py-2 pr-4 leading-snug">{{ getFullItemName(item) }}</td>
                        <td class="px-2 py-2 text-center">{{ item.jumlah }}</td>
                        <td class="px-2 py-2 text-right">{{ formatRupiah(hitungHargaSatuanBersih(item)) }}</td>
                        <td class="px-2 py-2 text-right">{{ formatRupiah(hitungTotalItem(item)) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- 6. DELIVERY -->
            <div class="py-2 mb-8 border-t border-b border-black">
                <div class="flex items-center justify-between px-2 font-medium">
                    <div class="flex items-center">
                        <div class="w-32 font-bold">Delivery</div>
                        <div>
                            {{ penawaran.ekspedisi_nama && penawaran.ekspedisi_nama !== 'Belum Termasuk Biaya Kirim' ? penawaran.ekspedisi_nama : 'Belum Termasuk Biaya Kirim' }}
                            <!-- Tampilkan Layanan jika ada (contoh: JNE - REG) -->
                            <span v-if="penawaran.ekspedisi_layanan && penawaran.ekspedisi_nama !== 'Ambil di Toko'">
                                - {{ penawaran.ekspedisi_layanan }}
                            </span>
                        </div>
                    </div>
                    <!-- Tampilkan Harga Ongkir jika lebih dari 0, dan sejajarkan ke kanan -->
                    <div v-if="penawaran.harga_ongkir > 0" class="text-right w-36">
                        {{ formatRupiah(penawaran.harga_ongkir) }}
                    </div>
                </div>
            </div>

            <!-- 7. PENUTUP -->
            <div class="mb-8 font-medium text-justify">
                <p>Apabila terdapat pertanyaan dan membutuhkan info lebih lanjut, Anda dapat menghubungi kami. Demikian surat penawaran ini saya sampaikan atas perhatiannya kami ucapkan terima kasih.</p>
            </div>

            <!-- 8. CATATAN & REKENING -->
            <div class="mb-10 text-xs font-medium">
                <p class="mb-1">Catatan :</p>
                <div class="pl-1 leading-relaxed whitespace-pre-wrap">{{ penawaran.catatan_penawaran }}</div>

                <p class="mt-4 italic font-bold">
                    Rekening Pembayaran BCA 1930566086 | Mandiri 9000043545889 a/n MOH CHAIRUL ANAM
                </p>
            </div>

            <!-- 9. TTD -->
            <div class="flex justify-end mt-16 text-center">
                <div class="w-64">
                    <h2 class="mb-2 text-2xl font-black tracking-tight uppercase">
                        bikincetak
                    </h2>
                    <p class="mb-20 text-[11px] font-medium -mt-3 italic">cetak online semakin mudah dan cepat</p>

                    <p class="mb-24 font-medium">Hormat Kami,</p>

                    <p class="text-sm font-bold">MOHAMMAD CHAIRUL ANAM</p>
                    <p class="text-xs italic font-medium">Marketing Manager</p>
                </div>
            </div>

        </div>
    </div>
</template>

<style>
/* Styling khusus Print aja */
@media print {
    @page { margin: 0; size: A4; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    html, body { background: white !important; }
}
</style>
