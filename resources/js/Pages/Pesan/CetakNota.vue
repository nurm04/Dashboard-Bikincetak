<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, computed } from 'vue';

const props = defineProps({
    pesanan: Object
});

const formatRupiah = (angka) => {
    if (!angka && angka !== 0) return '0';
    return Number(angka).toLocaleString('id-ID');
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
};

const formatSimpleDate = (dateStr) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
};

// ==========================================
// 1. LOGIKA PARSING ATRIBUT & SISI (Mirip OrderItemsTable.vue)
// ==========================================
const getCustomAttributes = (item) => {
    let attr = item.atribut_custom_snapshot;
    if (!attr) return null;

    if (typeof attr === 'string') {
        try { attr = JSON.parse(attr); } catch (e) { return null; }
    }

    if (typeof attr === 'object' && attr !== null) {
        const validAttrs = {};
        let hasValidData = false;

        for (const [key, value] of Object.entries(attr)) {
            if (value !== null && value !== undefined && value !== '') {
                validAttrs[key] = value;
                hasValidData = true;
            }
        }
        return hasValidData ? validAttrs : null;
    }
    return null;
};

const getSisiFromFinishing = (item) => {
    const finishings = item.pesanan_item_finishing || [];
    let sisi = 1;
    finishings.forEach(f => {
        const namaFin = (f.nama_finishing_snapshot || '').toLowerCase();
        if (namaFin.includes('dua sisi') || namaFin.includes('2 sisi') || namaFin.includes('bolak')) {
            sisi = 2;
        }
    });
    return sisi;
};

const isKaliJumlahPesan = (fin) => Boolean(fin.sku_finishing?.kali_jumlah_pesan);

// ==========================================
// 2. KALKULASI HARGA (SINKRON DENGAN ADMIN DASHBOARD)
// ==========================================

// Kalkulasi Harga Satuan per Item
const getDisplayHargaSatuan = (item) => {
    let hargaAwal = Number(item.harga_satuan_snapshot) || 0;
    const attr = getCustomAttributes(item);
    const finishings = item.pesanan_item_finishing || [];
    const sisi = getSisiFromFinishing(item);

    // Hitung Kertas Tambahan (Cetak Buku)
    if (attr && attr['Jumlah Halaman'] !== undefined) {
        let hal = parseInt(attr['Jumlah Halaman'], 10);
        if (isNaN(hal) || hal < 1) hal = 1;
        hargaAwal += (Math.max(0, hal - 1) * sisi * 1500);
    }

    // Tambah Finishing (HANYA YANG KALI QTY)
    finishings.forEach(f => {
        const isKaliQty = isKaliJumlahPesan(f);
        if (isKaliQty) {
            let val = f.tipe === 'persen'
                ? (hargaAwal * (Number(f.harga_finishing_snapshot) / 100))
                : (Number(f.harga_finishing_snapshot) || 0);
            hargaAwal += val;
        }
    });

    return hargaAwal;
};

// Kalkulasi Subtotal Total (Produk + Flat Finishing + SLA Pengerjaan)
const getDisplaySubtotal = (item) => {
    let hargaAwal = Number(item.harga_satuan_snapshot) || 0;
    const attr = getCustomAttributes(item);
    const finishings = item.pesanan_item_finishing || [];
    const sisi = getSisiFromFinishing(item);

    // Hitung Kertas Tambahan (Cetak Buku)
    if (attr && attr['Jumlah Halaman'] !== undefined) {
        let hal = parseInt(attr['Jumlah Halaman'], 10);
        if (isNaN(hal) || hal < 1) hal = 1;
        hargaAwal += (Math.max(0, hal - 1) * sisi * 1500);
    }

    let total = hargaAwal * Number(item.jumlah || 1);

    // Hitung Finishing
    finishings.forEach(f => {
        const isKaliQty = isKaliJumlahPesan(f);
        let val = f.tipe === 'persen'
            ? (hargaAwal * (Number(f.harga_finishing_snapshot) / 100))
            : (Number(f.harga_finishing_snapshot) || 0);

        total += (isKaliQty ? val * item.jumlah : val);
    });

    // Tambahkan SLA ke Total Item Subtotal di Nota
    const sla = Number(item.harga_pengerjaan_snapshot || 0);

    return total + sla;
};

// Hitung khusus Jasa Tambahan Flat (Hanya untuk Display di Nota)
const getFlatFinishingTotal = (item) => {
    let hargaAwal = Number(item.harga_satuan_snapshot) || 0;
    const attr = getCustomAttributes(item);
    const sisi = getSisiFromFinishing(item);

    if (attr && attr['Jumlah Halaman'] !== undefined) {
        let hal = parseInt(attr['Jumlah Halaman'], 10);
        if (isNaN(hal) || hal < 1) hal = 1;
        hargaAwal += (Math.max(0, hal - 1) * sisi * 1500);
    }

    let finFlat = 0;
    const finishings = item.pesanan_item_finishing || [];
    finishings.forEach(f => {
        if (!isKaliJumlahPesan(f)) {
            let val = f.tipe === 'persen'
                ? (hargaAwal * (Number(f.harga_finishing_snapshot) / 100))
                : (Number(f.harga_finishing_snapshot) || 0);
            finFlat += val;
        }
    });
    return finFlat;
};

// ==========================================
// 3. GRAND TOTAL KESELURUHAN
// ==========================================
const totalHargaSeluruhBarang = computed(() => {
    if (!props.pesanan || !props.pesanan.pesanan_item) return 0;
    return props.pesanan.pesanan_item.reduce((sum, item) => sum + getDisplaySubtotal(item), 0);
});

const kodeUnik = computed(() => Number(props.pesanan?.kode_unik || 0));
const totalOngkir = computed(() => Number(props.pesanan?.harga_ongkir || 0));

// Total Harga = Seluruh Produk (+ Finishing + SLA) + Kode Unik
const totalHarga = computed(() => totalHargaSeluruhBarang.value + kodeUnik.value);
// Total Bayar = Total Harga + Ongkir
const totalBayar = computed(() => totalHarga.value + totalOngkir.value);

onMounted(() => {
    // window.print();
});
</script>

<template>
    <Head :title="`Nota - ${pesanan.id_pesan}`" />

    <div class="max-w-xl mx-auto p-6 bg-white text-black font-sans text-xs print:p-0 print:max-w-none">
        <div class="mb-6 flex justify-end gap-2 print:hidden">
            <button @click="window.print()" class="px-4 py-2 bg-neutral text-white rounded font-medium text-xs shadow hover:bg-neutral/80 transition-colors">
                🖨️ Cetak Nota
            </button>
        </div>

        <!-- Layout Nota Portrait -->
        <div class="border border-black p-5 space-y-4 bg-white">
            <!-- Header Toko & Tujuan -->
            <div class="flex justify-between items-start border-b border-black pb-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-blue-950 font-serif">bikincetak</h1>
                    <p class="text-[10px] italic text-gray-700">Digital Printing, Offset, Merchandise</p>
                    <p class="text-[9px] mt-1.5 text-gray-800 leading-tight">
                        WA : 083831862770 | Email : bikinkancetak@gmail.com <br>
                        Alamat : Jl. Barata Jaya XVII No. 3 Gubeng - Surabaya
                    </p>
                </div>
                <div class="text-right text-xs space-y-0.5">
                    <p>Surabaya, {{ formatSimpleDate(pesanan.created_at) }}</p>
                    <p class="font-medium pt-1">Kepada Yth.</p>
                    <p class="font-bold uppercase">{{ pesanan.customer?.user?.name || '-' }}</p>
                    <p class="text-[11px] text-gray-700 max-w-50 truncate uppercase">{{ pesanan.alamat?.kota || pesanan.alamat?.detail_alamat || 'Surabaya' }}</p>
                    <p class="font-bold mt-1 text-sm tracking-wide uppercase">SO {{ pesanan.id_pesan }}</p>
                </div>
            </div>

            <div class="font-bold text-xs tracking-wider uppercase">INVOICE</div>

            <!-- Tabel Barang -->
            <table class="w-full border-collapse border border-black text-xs">
                <thead>
                    <tr class="border-b border-black bg-gray-50">
                        <th class="border-r border-black p-2 text-left font-bold">Nama Barang</th>
                        <th class="border-r border-black p-2 text-center font-bold w-12">Qty</th>
                        <th class="border-r border-black p-2 text-right font-bold w-24">Satuan Rp.</th>
                        <th class="p-2 text-right font-bold w-24">Total Rp.</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in pesanan.pesanan_item" :key="item.id" class="border-b border-black align-top">
                        <td class="border-r border-black p-2">
                            <div class="font-semibold">{{ item.nama_produk_snapshot }}</div>

                            <div class="text-[9.5px] text-gray-800 mt-1.5 leading-tight space-y-0.5">
                                <!-- Render Atribut Custom -->
                                <div v-if="getCustomAttributes(item)">
                                    <span v-for="(val, key) in getCustomAttributes(item)" :key="key" class="block">
                                        • {{ key.toUpperCase() }}: {{ val }}
                                    </span>
                                </div>

                                <!-- Render Pilihan Finishing -->
                                <div v-if="item.pesanan_item_finishing?.length">
                                    <span v-for="(fin, fIdx) in item.pesanan_item_finishing" :key="fIdx" class="block">
                                        • {{ fin.kategori_finishing ? fin.kategori_finishing.toUpperCase() + ': ' : '' }}{{ fin.nama_finishing_snapshot }}
                                        <span class="italic opacity-75">
                                            ({{ fin.tipe === 'persen' ? `${fin.harga_finishing_snapshot}%` : `Rp ${formatRupiah(fin.harga_finishing_snapshot)}` }})
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <!-- Notice SLA & Finishing Flat -->
                            <div v-if="Number(item.harga_pengerjaan_snapshot || 0) > 0" class="mt-2 text-[10px] font-bold text-gray-900">
                                + SLA ({{ item.estimasi_pengerjaan_snapshot || item.estimasi_pengerjaan }}): Rp {{ formatRupiah(item.harga_pengerjaan_snapshot) }}
                            </div>
                            <div v-if="getFlatFinishingTotal(item) > 0" class="mt-0.5 text-[10px] font-bold text-gray-900">
                                + Jasa Tambahan (Flat): Rp {{ formatRupiah(getFlatFinishingTotal(item)) }}
                            </div>
                        </td>
                        <td class="border-r border-black p-2 text-center font-semibold">{{ item.jumlah }}</td>
                        <!-- Satuan sudah termasuk (Base + Sisi Halaman) + Finishing (yang di-kali Qty) -->
                        <td class="border-r border-black p-2 text-right">{{ formatRupiah(getDisplayHargaSatuan(item)) }}</td>
                        <!-- Total = (Satuan x Qty) + Finishing Flat + SLA -->
                        <td class="p-2 text-right font-semibold">{{ formatRupiah(getDisplaySubtotal(item)) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Bagian Bawah (Delivery, Info Pembayaran & Rincian Total) -->
            <div class="grid grid-cols-2 gap-4 pt-2">
                <!-- Kolom Kiri: Kurir & Rekening -->
                <div class="space-y-4 text-xs">
                    <div>
                        <p class="font-bold">Delivery</p>
                        <p class="text-gray-800 uppercase">{{ pesanan.ekspedisi_nama || 'Di Ambil' }} <span v-if="pesanan.ekspedisi_layanan && !pesanan.ekspedisi_nama?.includes('Ambil')">- {{ pesanan.ekspedisi_layanan }}</span></p>
                    </div>
                    <div class="space-y-0.5 pt-2">
                        <p class="font-medium">Pembayaran :</p>
                        <p class="font-bold">BCA 1930566086 | MANDIRI 9000043545889</p>
                        <p class="italic text-[10px]">an/ Mohammad Chairul Anam</p>
                    </div>
                </div>

                <!-- Kolom Kanan: Rincian Nominal & Stempel Lunas -->
                <div class="space-y-1 text-xs">
                    <div class="flex justify-between py-1 border-b border-gray-300">
                        <span class="font-medium">Ongkir</span>
                        <span>{{ formatRupiah(totalOngkir) }}</span>
                    </div>
                    <div v-if="kodeUnik > 0" class="flex justify-between py-1 border-b border-gray-300">
                        <span class="font-medium">Kode Unik</span>
                        <span>{{ formatRupiah(kodeUnik) }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-300 font-bold">
                        <span>Total Harga</span>
                        <span>{{ formatRupiah(totalHarga) }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-300 font-bold">
                        <span>Total Bayar</span>
                        <span>{{ formatRupiah(totalBayar) }}</span>
                    </div>
                    <div class="flex justify-between py-1 font-bold">
                        <span>Sisa</span>
                        <span>0</span>
                    </div>

                    <!-- Stempel Status Lunas -->
                    <div class="pt-3 flex justify-end">
                        <span class="text-red-600 font-black text-sm border-2 border-red-600 px-4 py-0.5 rotate-[-4deg] inline-block tracking-widest uppercase">
                            Lunas
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer Timestamp -->
            <div class="pt-4 border-t border-gray-300 flex justify-end text-[10px] text-gray-500 italic">
                print on {{ formatDate(new Date()) }}
            </div>
        </div>
    </div>

</template>
<style>
@media print {
    body {
        background: white !important;
        color: black !important;
    }
    @page {
        size: portrait;
        margin: 8mm;
    }
}
</style>
