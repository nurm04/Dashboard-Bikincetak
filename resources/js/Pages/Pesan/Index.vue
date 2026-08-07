<script setup>
import { ref, watch, computed } from 'vue';
import { alertStore } from '@/Utils/alertStore';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomInputNumber from '@/Components/Form/CustomInputNumber.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomInputSearch from '@/Components/Form/CustomInputSearch.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';
import CustomTableAction from '@/Components/CustomTableAction.vue';

const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const props = defineProps({
    pesanan: Array,
    enumPembayaran: Array,
    enumOperasional: Array,
    filters: Object
});

// ==========================================
// KALKULASI TOTAL TAGIHAN AKURAT (VUE)
// ==========================================
const getTagihanAkurat = (pesan) => {
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
            else if (atribut && atribut['Luas Dihargai (m2)'] !== undefined) {
                let luas = parseFloat(String(atribut['Luas Dihargai (m2)'])) || 1;
                if (luas < 1) luas = 1;
                hargaAwal = hargaAwal * luas;
            }

            let subtotalItem = hargaAwal * (Number(item.jumlah) || 1);

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

const pesananAkurat = computed(() => {
    return props.pesanan.map(p => {
        return {
            ...p,
            total_tagihan_real: getTagihanAkurat(p)
        };
    });
});

const headers = ['ID Pesanan', 'Kode Transaksi', 'Customer', 'Total Tagihan', 'Pembayaran', 'Operasional', 'Aksi'];

const showBayarSebagianModal = ref(false);
const selectedPesanId = ref(null);
const selectedPesan = ref(null);

const formPembayaran = useForm({ status_pembayaran: '', nominal_bayar: null });

const search = ref(props.filters?.search || '');
const filterPembayaran = ref(props.filters?.status_pembayaran || 'semua');
const filterOperasional = ref(props.filters?.status_operasional || 'semua');

const operasionalOptions = computed(() => {
    let opts = [{ value: 'semua', label: 'SEMUA OPERASIONAL' }];
    if (props.enumOperasional) {
        props.enumOperasional.forEach(opt => {
            if (opt !== 'keranjang') {
                opts.push({ value: opt, label: formatEnum(opt).toUpperCase() });
            }
        });
    }
    return opts;
});

const pembayaranOptions = computed(() => {
    let opts = [{ value: 'semua', label: 'SEMUA PEMBAYARAN' }];
    if (props.enumPembayaran) {
        props.enumPembayaran.forEach(opt => {
            opts.push({ value: opt, label: formatEnum(opt).toUpperCase() });
        });
    }
    return opts;
});

watch(
    [search, filterPembayaran, filterOperasional],
    debounce(([newSearch, newPembayaran, newOperasional]) => {
        router.get(route('pesan.index'), {
            search: newSearch,
            status_pembayaran: newPembayaran,
            status_operasional: newOperasional
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300)
);

const sudahAdaPembayaran = (pesan) => (pesan.total_dibayar ?? 0) > 0;
const sudahLunas = (pesan) => (pesan.total_dibayar ?? 0) >= (pesan.total_tagihan_real ?? 0);

const resetModal = () => {
    formPembayaran.reset();
    formPembayaran.clearErrors();
    selectedPesan.value = null;
    selectedPesanId.value = null;
    showBayarSebagianModal.value = false;
};

const updatePembayaran = (pesan, value) => {
    if (sudahLunas(pesan)) return;

    if (value === 'dibayar_sebagian') {
        selectedPesan.value = pesan;
        selectedPesanId.value = pesan.id_pesan;
        formPembayaran.reset();
        formPembayaran.status_pembayaran ='dibayar_sebagian';
        showBayarSebagianModal.value = true;
        return;
    }

    formPembayaran.status_pembayaran = value;
    formPembayaran.put(route('pesan.updatePembayaran', pesan.id_pesan), {
        preserveScroll: true,
        onSuccess: () => alertStore.show('Status Pembayaran berhasil diperbarui!','success'),
        onError: () => alertStore.show('Gagal memperbarui status pembayaran!','error')
    });
};

const submitBayarSebagian = () => {
    formPembayaran.put(route('pesan.updatePembayaran',selectedPesanId.value), {
        preserveScroll: true,
        onSuccess: () => {
            resetModal();
            alertStore.show('Pembayaran berhasil dicatat!','success');
        },
        onError: () => alertStore.show('Gagal mencatat pembayaran!','error')
    });
};

// ==========================================
// STATE & FUNGSI BARU: CUSTOM ALERT CONFIRM
// ==========================================
const showConfirmProduksi = ref(false);
const idPesananUntukProduksi = ref(null);
const isProcessingProduksi = ref(false);

const openConfirmProduksi = (id_pesan) => {
    idPesananUntukProduksi.value = id_pesan;
    showConfirmProduksi.value = true;
};

const submitLemparProduksi = () => {
    if (!idPesananUntukProduksi.value) return;

    isProcessingProduksi.value = true;

    router.post(route('pesan.lemparProduksi', idPesananUntukProduksi.value), {}, {
        preserveScroll: true,
        onSuccess: () => {
            showConfirmProduksi.value = false;
            alertStore.show('Pesanan berhasil dilempar ke produksi!', 'success');
        },
        onError: () => alertStore.show('Gagal memproses pesanan ke produksi!', 'error'),
        onFinish: () => {
            isProcessingProduksi.value = false;
        }
    });
};
// ==========================================

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka);
};

const formatEnum = (text) => {
    if (!text) return '';
    return text.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};
</script>

<template>
    <Head title="Manajemen Pesanan" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Daftar Pesanan & Transaksi
            </h2>
        </template>

        <div class="min-h-screen px-4 py-6 mx-auto sm:px-6 lg:px-8 max-w-7xl">

            <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between">
                <div class="w-full shrink-0 md:w-auto">
                    <CustomButton v-if="$can('pesan', 'tambah')" type="link" :href="route('pesan.pos-kasir')" variant="primary" block>
                        <template #icon>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M12 3.75a.75.75 0 01.75.75v6.75h6.75a.75.75 0 010 1.5h-6.75v6.75a.75.75 0 01-1.5 0v-6.75H4.5a.75.75 0 010-1.5h6.75V4.5a.75.75 0 01.75-.75z" clip-rule="evenodd" />
                            </svg>
                        </template>
                        POS Kasir
                    </CustomButton>
                </div>

                <div class="flex flex-col w-full gap-3 md:flex-row md:items-center md:w-auto">
                    <div class="w-full md:w-64 lg:w-72">
                        <CustomInputSearch
                            v-model="search"
                            placeholder="Cari ID / Nama Customer..."
                            class="w-full"
                        />
                    </div>
                    <div class="grid w-full grid-cols-2 gap-3 md:flex md:w-auto md:gap-3">
                        <div class="w-full md:w-48 lg:w-56">
                            <CustomSelect
                                v-model="filterPembayaran"
                                :options="pembayaranOptions"
                                valueKey="value"
                                labelKey="label"
                                placeholder="Semua Pembayaran"
                            />
                        </div>
                        <div class="w-full md:w-48 lg:w-56">
                            <CustomSelect
                                v-model="filterOperasional"
                                :options="operasionalOptions"
                                valueKey="value"
                                labelKey="label"
                                placeholder="Semua Operasional"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <CustomTable :headers="headers">
                <tr
                    v-for="p in pesananAkurat"
                    :key="p.id_pesan"
                    :class="p.status_operasional === 'batal' ? 'bg-base-200/30' : 'hover:bg-base-200/50'"
                    class="transition-colors"
                >
                    <td class="px-4 py-4 font-mono text-xs font-bold whitespace-nowrap" :class="p.status_operasional === 'batal' ? 'opacity-40 line-through' : 'text-primary'">
                        {{ p.id_pesan }}
                    </td>

                    <td class="px-4 py-4 whitespace-nowrap" :class="p.status_operasional === 'batal' ? 'opacity-40 line-through' : 'text-primary'">
                        {{ p.kode_transaksi }}
                    </td>

                    <td class="px-4 py-4 whitespace-nowrap" :class="p.status_operasional === 'batal' ? 'opacity-40 line-through' : ''">
                        <div class="font-bold text-base-content">{{ p.customer?.user?.name || 'Walk-in / Umum' }}</div>
                        <div class="text-[10px] opacity-40 font-mono tracking-wider">{{ p.id_customer }}</div>
                    </td>

                    <td class="px-4 py-4 text-sm font-black whitespace-nowrap" :class="p.status_operasional === 'batal' ? 'opacity-40 line-through' : 'text-base-content'">
                        {{ formatRupiah(p.total_tagihan_real ?? 0) }}
                    </td>

                    <td class="px-4 py-4 whitespace-nowrap" :class="p.status_operasional === 'batal' ? 'opacity-50' : ''">
                        <select
                            class="select select-bordered select-sm text-[10px] font-black uppercase tracking-wider rounded-xl shadow-sm"
                            :class="
                                p.status_pembayaran === 'lunas' ? 'text-success border-success/30 bg-success/5'
                                : p.status_pembayaran === 'dibayar_sebagian' ? 'text-warning border-warning/30 bg-warning/5' : 'text-error border-error/30 bg-error/5'
                            "
                            :value="p.status_pembayaran"
                            @change="updatePembayaran(p, $event.target.value)"
                            :disabled="formPembayaran.processing || sudahLunas(p) || p.status_operasional === 'batal'"
                        >
                            <option
                                v-for="status in enumPembayaran"
                                :key="status"
                                :value="status"
                                :disabled="(status === 'belum_lunas' && sudahAdaPembayaran(p)) || (status === 'dibayar_sebagian' && sudahLunas(p))"
                            >
                                {{ status.replace(/_/g, ' ').toUpperCase() }}
                            </option>
                        </select>
                        <div class="mt-1 text-[10px] font-bold opacity-60" :class="p.status_operasional === 'batal' ? 'line-through' : ''">
                            {{ formatRupiah(p.total_dibayar ?? 0) }} / {{ formatRupiah(p.total_tagihan_real ?? 0) }}
                        </div>
                    </td>

                    <td class="px-4 py-4 whitespace-nowrap" :class="p.status_operasional === 'batal' ? 'opacity-50' : ''">
                        <div class="inline-flex items-center px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-xl shadow-sm border border-base-300 bg-base-100 text-base-content">
                            {{ formatEnum(p.status_operasional) }}
                        </div>
                    </td>

                    <td class="px-4 py-4 text-center whitespace-nowrap">
                        <CustomTableAction v-slot="{ close }">

                            <!-- Header Menu -->
                            <div class="px-4 py-2 text-[10px] font-black text-base-content/40 uppercase tracking-widest border-b border-base-300/50 mb-1">
                                Menu Pesanan
                            </div>

                            <!-- 1. Detail Pesanan -->
                            <Link :href="route('pesan.detail', p.id_pesan)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-base-content hover:bg-base-200 transition-colors">
                                <svg class="w-4 h-4 mr-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Detail Pesanan
                            </Link>

                            <!-- 2. Riwayat Log (Sementara pakai '#' sampai route-nya lu daftarin di web.php) -->
                            <Link :href="route('pesan.log', p.id_pesan)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-info hover:bg-info/10 transition-colors">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Riwayat Log
                            </Link>

                            <!-- 3. Masuk Produksi (Kondisional) -->
                            <template v-if="p.status_pembayaran === 'belum_lunas' && !p.waktu_deadline && p.status_operasional !== 'batal'">
                                <div class="my-1 border-t border-base-300/50"></div>

                                <button @click="openConfirmProduksi(p.id_pesan); close()" class="flex items-center w-full px-4 py-2.5 text-sm font-bold text-primary hover:bg-primary/10 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Masuk Produksi
                                </button>
                            </template>

                        </CustomTableAction>
                    </td>
                </tr>

                <tr v-if="pesanan.length === 0">
                    <td colspan="7" class="px-6 py-20 text-center">
                        <div class="flex flex-col items-center gap-2 opacity-30">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <p class="text-xs font-black tracking-widest uppercase">
                                {{ search || filterOperasional !== 'semua' || filterPembayaran !== 'semua' ? 'Pencarian Tidak Ditemukan' : 'Belum ada Pesanan Masuk' }}
                            </p>
                        </div>
                    </td>
                </tr>
            </CustomTable>
        </div>

        <!-- MODAL BAYAR SEBAGIAN -->
        <dialog class="modal" :class="{ 'modal-open': showBayarSebagianModal }">
            <div class="w-11/12 max-w-lg modal-box">
                <h3 class="text-lg font-black">Pembayaran Sebagian</h3>

                <div v-if="selectedPesan" class="mt-4 space-y-4">
                    <div>
                        <CustomInput
                            label="Total Tagihan"
                            :model-value="formatRupiah(selectedPesan.total_tagihan_real)"
                            disabled
                        />
                    </div>
                    <div>
                        <CustomInputNumber
                            v-model="formPembayaran.nominal_bayar"
                            label="Nominal Bayar"
                            type="number"
                            placeholder="Masukkan nominal pembayaran"
                            :error="formPembayaran.errors.nominal_bayar"
                            :max="selectedPesan.total_tagihan_real - (selectedPesan.total_dibayar ?? 0)"
                        />
                    </div>
                    <div class="p-3 text-xs rounded-lg bg-base-200">
                        <div>
                            Sudah Dibayar: <b>{{formatRupiah(selectedPesan.total_dibayar ?? 0)}}</b>
                        </div>
                        <div>
                            Sisa Tagihan: <b>{{formatRupiah(selectedPesan.total_tagihan_real - (selectedPesan.total_dibayar ?? 0))}}</b>
                        </div>
                    </div>
                </div>

                <div class="justify-end mt-6 modal-action">
                    <CustomButton variant="secondary" @click="resetModal">Batal</CustomButton>
                    <CustomButton variant="primary" :disabled="!formPembayaran.nominal_bayar || formPembayaran.processing" @click="submitBayarSebagian">Simpan Pembayaran</CustomButton>
                </div>
            </div>
        </dialog>

        <!-- IMPLEMENTASI CUSTOM ALERT CONFIRM DI SINI -->
        <CustomAlertConfirm
            :show="showConfirmProduksi"
            type="info"
            title="Sistem Tempo Produksi"
            message="Yakin ingin memproses pesanan ini ke meja produksi tanpa pembayaran awal dari customer? Transaksi akan dicatat sebagai Piutang."
            confirmText="Ya, Masukkan Antrean"
            cancelText="Batal"
            :loading="isProcessingProduksi"
            @close="showConfirmProduksi = false"
            @confirm="submitLemparProduksi"
        />

    </StafLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
