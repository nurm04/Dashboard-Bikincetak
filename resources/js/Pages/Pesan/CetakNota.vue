<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, computed } from 'vue';

const props = defineProps({
    pesanan: Object,
    kode_unik: Number,
    grand_total: Number,
    total_dibayar: Number,
    sisa_tagihan: Number,
    bank_name: String,
    bank_number: String,
    bank_owner: String
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
// 1. ENGINE KALKULASI UTAMA (SATU PINTU BIAR SINKRON)
// ==========================================
const getParsedItem = (item) => {
    let hargaDasar = Number(item.harga_satuan_snapshot) || 0;
    let attr = item.atribut_custom_snapshot;

    if (typeof attr === 'string') {
        try { attr = JSON.parse(attr); } catch (e) { attr = null; }
    } else {
        attr = typeof attr === 'object' ? attr : null;
    }

    const finishings = item.pesanan_item_finishing || item.finishing || [];
    let sisi = 1;

    finishings.forEach(f => {
        const namaFin = (f.nama_finishing_snapshot || '').toLowerCase();
        if (namaFin.includes('dua sisi') || namaFin.includes('2 sisi') || namaFin.includes('bolak')) {
            sisi = 2;
        }
    });

    // A. LOGIC BUKU
    if (attr && attr['Jumlah Halaman'] !== undefined) {
        let hal = parseInt(String(attr['Jumlah Halaman']), 10);
        if (isNaN(hal) || hal < 1) hal = 1;
        hargaDasar += (Math.max(0, hal - 1) * sisi * 1500);
    }
    // B. LOGIC METERAN
    else if (attr && attr['Luas Dihargai (m2)'] !== undefined) {
        let luas = parseFloat(String(attr['Luas Dihargai (m2)'])) || 1;
        if (luas < 1) luas = 1;
        hargaDasar = hargaDasar * luas; // Kalikan harga dengan luas meteran
    }

    let finishingPerItem = 0;
    let finishingFlat = 0;

    // C. LOGIC FINISHING (Pakai hargaDasar yang udah fix meterannya)
    finishings.forEach(f => {
        const isKaliQty = Boolean(f.sku_finishing?.kali_jumlah_pesan) || Boolean(f.kali_jumlah_pesan);
        let val = f.tipe === 'persen'
            ? (hargaDasar * (Number(f.harga_finishing_snapshot) / 100))
            : (Number(f.harga_finishing_snapshot) || 0);

        if (isKaliQty) {
            finishingPerItem += val;
        } else {
            finishingFlat += val;
        }
    });

    const qty = Number(item.jumlah) || 1;
    const sla = Number(item.harga_pengerjaan_snapshot || 0);

    // D. REKAP HARGA AKHIR
    const hargaSatuan = hargaDasar + finishingPerItem; // Tampil di kolom "Satuan Rp"
    const subtotal = (hargaSatuan * qty) + finishingFlat + sla; // Tampil di kolom "Total Rp"

    return { hargaSatuan, subtotal, finishingFlat, sla, attr };
};

// ==========================================
// 2. GETTER UNTUK TABEL
// ==========================================
const getDisplayHargaSatuan = (item) => getParsedItem(item).hargaSatuan;
const getDisplaySubtotal = (item) => getParsedItem(item).subtotal;
const getFlatFinishingTotal = (item) => getParsedItem(item).finishingFlat;

const getCustomAttributesDisplay = (item) => {
    const attr = getParsedItem(item).attr;
    if (attr && typeof attr === 'object') {
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

// ==========================================
// 3. OVERRIDE GRAND TOTAL (HITUNG MANUAL DI VUE)
// ==========================================
const totalHargaSeluruhBarang = computed(() => {
    if (!props.pesanan || !props.pesanan.pesanan_item) return 0;
    return props.pesanan.pesanan_item.reduce((sum, item) => sum + getDisplaySubtotal(item), 0);
});

const totalOngkir = computed(() => Number(props.pesanan?.harga_ongkir || 0));
const diskonVoucher = computed(() => Number(props.pesanan?.diskon_voucher_nominal || 0));
const kodeUnik = computed(() => Number(props.kode_unik || props.pesanan?.kode_unik || 0));

// GANTI props.grand_total KARENA BACKEND TIDAK HITUNG METERAN
const totalTagihan = computed(() => {
    return totalHargaSeluruhBarang.value + totalOngkir.value + kodeUnik.value - diskonVoucher.value;
});

const telahDibayar = computed(() => Number(props.total_dibayar || props.pesanan?.total_dibayar || 0));

// GANTI props.sisa_tagihan AGAR SINKRON DENGAN TOTAL YANG BARU
const sisaTagihan = computed(() => totalTagihan.value - telahDibayar.value);

const cleanProductName = (name) => {
    if (!name) return '';
    return name.replace(/^[A-Za-z]+-\d+-/, '').replace(/-/g, ' ');
};

onMounted(() => {
    setTimeout(() => {
        window.print();
    }, 500);
});
</script>

<template>
    <Head :title="`Nota - ${pesanan.id_pesan}`" />

    <div class="max-w-xl p-6 mx-auto font-sans text-xs text-black bg-white print:p-0 print:max-w-none">
        <div class="flex justify-end gap-2 mb-6 print:hidden">
            <button @click="window.print()" class="px-4 py-2 text-xs font-medium text-white transition-colors rounded shadow bg-neutral hover:bg-neutral/80">
                🖨️ Cetak Nota
            </button>
        </div>

        <div class="p-5 space-y-4 bg-white border border-black">
            <!-- Header Toko & Tujuan -->
            <div class="flex items-start justify-between pb-4 border-b border-black">
                <div>
                    <h1 class="font-serif text-2xl font-black tracking-tight text-blue-950">bikincetak</h1>
                    <p class="text-[10px] italic text-gray-700">Digital Printing, Offset, Merchandise</p>
                    <p class="text-[9px] mt-1.5 text-gray-800 leading-tight">
                        WA : 083831862770 | Email : order@bikincetak.co.id <br>
                        Alamat : Jl. Barata Jaya XVII No. 3 Gubeng - Surabaya
                    </p>
                </div>
                <div class="text-right text-xs space-y-0.5">
                    <p>Surabaya, {{ formatSimpleDate(pesanan.created_at) }}</p>
                    <p class="pt-1 font-medium">Kepada Yth.</p>
                    <p class="font-bold uppercase">{{ pesanan.customer?.user?.name || '-' }}</p>
                    <p class="text-[11px] text-gray-700 max-w-50 truncate uppercase">{{ pesanan.alamat?.kota || pesanan.alamat?.detail_alamat || 'Surabaya' }}</p>
                    <p class="pt-1 font-medium">Kode Transaksi</p>
                    <p class="mt-1 text-sm font-bold tracking-wide uppercase">{{ pesanan.kode_transaksi }}</p>
                </div>
            </div>

            <div class="text-xs font-bold tracking-wider uppercase">INVOICE</div>

            <!-- Tabel Barang -->
            <table class="w-full text-xs border border-collapse border-black">
                <thead>
                    <tr class="border-b border-black bg-gray-50">
                        <th class="p-2 font-bold text-left border-r border-black">Nama Barang</th>
                        <th class="w-12 p-2 font-bold text-center border-r border-black">Qty</th>
                        <th class="w-24 p-2 font-bold text-right border-r border-black">Satuan Rp.</th>
                        <th class="w-24 p-2 font-bold text-right">Total Rp.</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in pesanan.pesanan_item" :key="item.id" class="align-top border-b border-black">
                        <td class="p-2 border-r border-black">
                            <div class="font-semibold">{{ cleanProductName(item.nama_produk_snapshot) }}</div>

                            <div class="text-[9.5px] text-gray-800 mt-1.5 leading-tight space-y-0.5">
                                <div v-if="getCustomAttributesDisplay(item)">
                                    <span v-for="(val, key) in getCustomAttributesDisplay(item)" :key="key" class="block">
                                        • {{ key.toUpperCase() }}: {{ val }}
                                    </span>
                                </div>

                                <div v-if="item.pesanan_item_finishing?.length">
                                    <span v-for="(fin, fIdx) in item.pesanan_item_finishing" :key="fIdx" class="block">
                                        • {{ fin.kategori_finishing ? fin.kategori_finishing.toUpperCase() + ': ' : '' }}{{ fin.nama_finishing_snapshot }}
                                        <span class="italic opacity-75">
                                            ({{ fin.tipe === 'persen' ? `${fin.harga_finishing_snapshot}%` : `Rp ${formatRupiah(fin.harga_finishing_snapshot)}` }})
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <div v-if="getParsedItem(item).sla > 0" class="mt-2 text-[10px] font-bold text-gray-900">
                                + SLA ({{ item.estimasi_pengerjaan_snapshot || item.estimasi_pengerjaan }}): Rp {{ formatRupiah(getParsedItem(item).sla) }}
                            </div>
                            <div v-if="getParsedItem(item).finishingFlat > 0" class="mt-0.5 text-[10px] font-bold text-gray-900">
                                + Jasa Tambahan (Flat): Rp {{ formatRupiah(getParsedItem(item).finishingFlat) }}
                            </div>
                        </td>
                        <td class="p-2 font-semibold text-center border-r border-black">{{ item.jumlah }}</td>
                        <!-- SATUAN AKAN TEPAT SESUAI HARGA DASAR + FINISHING PCS -->
                        <td class="p-2 text-right border-r border-black">{{ formatRupiah(getDisplayHargaSatuan(item)) }}</td>
                        <td class="p-2 font-semibold text-right">{{ formatRupiah(getDisplaySubtotal(item)) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Bagian Bawah -->
            <div class="grid grid-cols-2 gap-4 pt-2">

                <!-- Kolom Kiri: Kurir & Rekening -->
                <div class="space-y-4 text-xs">
                    <div>
                        <p class="font-bold">Delivery</p>
                        <p class="text-gray-800 uppercase">{{ pesanan.ekspedisi_nama || 'Di Ambil' }} <span v-if="pesanan.ekspedisi_layanan && !pesanan.ekspedisi_nama?.includes('Ambil')">- {{ pesanan.ekspedisi_layanan }}</span></p>
                    </div>
                    <div class="space-y-0.5 pt-2">
                        <p class="font-medium">Pembayaran :</p>
                        <p class="font-bold">{{ bank_name }} {{ bank_number }}</p>
                        <p class="italic text-[10px]">an/ {{ bank_owner }}</p>
                    </div>
                </div>

                <!-- Kolom Kanan: Rincian Nominal Dinamis -->
                <div class="space-y-1 text-xs">
                    <div class="flex justify-between py-1 text-gray-700 border-b border-gray-300">
                        <span>Subtotal Produk</span>
                        <span>{{ formatRupiah(totalHargaSeluruhBarang) }}</span>
                    </div>
                    <div v-if="totalOngkir > 0" class="flex justify-between py-1 text-gray-700 border-b border-gray-300">
                        <span>Ongkos Kirim</span>
                        <span>{{ formatRupiah(totalOngkir) }}</span>
                    </div>
                    <div v-if="kodeUnik > 0" class="flex justify-between py-1 text-gray-700 border-b border-gray-300">
                        <span>Kode Unik</span>
                        <span>{{ formatRupiah(kodeUnik) }}</span>
                    </div>
                    <div v-if="diskonVoucher > 0" class="flex justify-between py-1 font-bold text-green-600 border-b border-gray-300">
                        <span>Diskon Voucher</span>
                        <span>- {{ formatRupiah(diskonVoucher) }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b-2 border-black font-black text-[13px]">
                        <span>GRAND TOTAL</span>
                        <!-- GRAND TOTAL YANG BARU & AKURAT -->
                        <span>{{ formatRupiah(totalTagihan) }}</span>
                    </div>
                    <div class="flex justify-between py-1 font-bold text-green-700">
                        <span>Telah Dibayar</span>
                        <span>{{ formatRupiah(telahDibayar) }}</span>
                    </div>
                    <div class="flex justify-between py-1 font-bold" :class="sisaTagihan > 0 ? 'text-red-600' : ''">
                        <span>Sisa Tagihan</span>
                        <!-- SISA TAGIHAN YANG BARU & AKURAT -->
                        <span>{{ formatRupiah(sisaTagihan) }}</span>
                    </div>

                    <!-- STEMPEL STATUS DINAMIS -->
                    <div class="flex justify-end pt-4">
                        <span v-if="sisaTagihan <= 0" class="text-green-600 font-black text-sm border-2 border-green-600 px-4 py-0.5 rotate-[-4deg] inline-block tracking-widest uppercase">
                            LUNAS
                        </span>
                        <span v-else-if="telahDibayar > 0" class="text-orange-500 font-black text-sm border-2 border-orange-500 px-4 py-0.5 rotate-[-4deg] inline-block tracking-widest uppercase">
                            DP / SEBAGIAN
                        </span>
                        <span v-else class="text-red-600 font-black text-sm border-2 border-red-600 px-4 py-0.5 rotate-[-4deg] inline-block tracking-widest uppercase">
                            BELUM BAYAR
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
