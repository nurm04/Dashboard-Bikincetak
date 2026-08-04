<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomInputSearch from '@/Components/Form/CustomInputSearch.vue';
import CustomTable from '@/Components/CustomTable.vue';

const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const props = defineProps({
    pembayaran: Array,
    filters: Object
});

// ==========================================
// KALKULASI TOTAL TAGIHAN AKURAT (VUE) - METERAN FIX
// ==========================================
const getTagihanAkurat = (pesan) => {
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
    const kodeUnik = Number(pesan.kode_unik || 0);

    return totalMurniProduk + totalPengerjaan + ongkir - diskon + kodeUnik;
};

// Map pembayaran yang masuk dan inject tagihan akurat dari pesanan
const pembayaranAkurat = computed(() => {
    return props.pembayaran.map(p => {
        return {
            ...p,
            total_tagihan_real: getTagihanAkurat(p.pesan)
        };
    });
});
// ==========================================

// Btw gua ubah judul "Total Transfer" jadi "Nominal Dibayar" biar gak bingung sama isi tabelnya
const headers = ['ID Pembayaran', 'Pelanggan', 'Total Tagihan', 'Nominal Dibayar', 'Staf', 'Aksi'];

const search = ref(props.filters?.search || '');

watch(
    search,
    debounce((newSearch) => {
        router.get(route('pembayaran.index'), {
            search: newSearch
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300)
);

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka);
};
</script>

<template>
    <Head title="Data Pembayaran" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Data Pembayaran
            </h2>
        </template>

        <div class="min-h-screen px-4 py-3 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">

                <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-end">
                    <CustomInputSearch
                        v-model="search"
                        class="w-full sm:w-80"
                        placeholder="Cari ID Pembayaran, Pesanan, atau Nama..."
                    />
                </div>

                <CustomTable :headers="headers">
                    <!-- PAKAI pembayaranAkurat BUKAN pembayaran -->
                    <tr v-for="item in pembayaranAkurat" :key="item.id_pembayaran" class="transition-colors hover:bg-base-200/50">
                        <td class="px-6 py-4 font-mono text-xs font-bold text-primary">
                            {{ item.id_pembayaran }}
                            <div v-if="item.pesan" class="text-[10px] text-base-content/50 mt-1 uppercase tracking-wider">
                                {{ item.pesan.id_pesan }}
                            </div>
                        </td>

                        <td class="px-6 py-4 font-bold text-base-content">
                            {{ item.pesan?.customer?.user?.name || '-' }}
                            <div class="text-[10px] text-base-content/50 mt-1 font-mono tracking-wider">
                                {{ item.pesan?.customer?.id_customer || '' }}
                            </div>
                        </td>

                        <td class="px-6 py-4 font-black text-base-content/80">
                            <!-- NAMPILIN TAGIHAN YANG UDAH DIHITUNG ULANG -->
                            {{ formatRupiah(item.total_tagihan_real) }}
                        </td>

                        <td class="px-6 py-4 font-bold text-success">
                            {{ formatRupiah(item.nominal_bayar) }}
                        </td>

                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-md bg-base-200 border border-base-300 text-[10px] font-black uppercase tracking-wider text-base-content/60">
                                {{ item.staf?.user?.name || '-' }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <CustomButton type="link" :href="route('pembayaran.detail', item.id_pembayaran)" variant="info" size="sm">
                                Detail
                            </CustomButton>
                        </td>
                    </tr>

                    <tr v-if="pembayaran.length === 0">
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-sm font-bold tracking-widest uppercase">
                                    {{ search ? 'Pencarian Tidak Ditemukan' : 'Belum ada Data Pembayaran' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
