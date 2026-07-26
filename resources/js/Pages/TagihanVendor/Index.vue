<script setup>
import { ref, watch } from 'vue'; // Tambahkan watch
import { Head, useForm, router } from '@inertiajs/vue3'; // Tambahkan router
import { alertStore } from '@/Utils/alertStore';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomInputFile from '@/Components/Form/CustomInputFile.vue';
import CustomInputSearch from '@/Components/Form/CustomInputSearch.vue'; // Import komponen Search

const props = defineProps({
    pendingTagihan: Array,
    riwayatTagihan: Object,
    filters: Object, // Terima props filters
});

// LOGIKA PENCARIAN (SEARCH)
const search = ref(props.filters?.search || '');
const activeTab = ref('pending');

const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

watch(search, debounce((newSearch) => {
    // Sesuaikan URL '/tagihan-vendor' jika URL route kamu berbeda
    router.get('/tagihan-vendor', { search: newSearch }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 300));


const headersPending = ['Vendor', 'Info Bank', 'Jml Pekerjaan', 'Total Hutang', 'Aksi'];
const headersRiwayat = ['Tanggal Bayar', 'Vendor', 'Total Tagihan', 'Status', 'Aksi'];

// ... (Semua Form State, Confirm Modal, Detail Modal, dan DoBayar TETAP SAMA seperti sebelumnya) ...
const isConfirmModalOpen = ref(false);
const form = useForm({
    id_vendor: null,
    bukti_bayar: { tipe_file: 'upload', file: null, link_file: '' }
});
const confirmData = ref({ vendor: '', bank: '', total: 0 });
const isDetailModalOpen = ref(false);
const selectedVendorDetail = ref(null);

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka || 0);
};

const openDetailModal = (tipe, data) => {
    if (tipe === 'pending') {
        selectedVendorDetail.value = {
            is_lunas: false,
            id_vendor: data.id_vendor,
            nama_vendor: data.nama_vendor,
            info_bank: data.info_bank,
            total: data.total_hutang,
            items: data.items
        };
    } else if (tipe === 'riwayat') {
        selectedVendorDetail.value = {
            is_lunas: true,
            id_vendor: data.id_vendor,
            nama_vendor: data.vendor?.nama_vendor || 'Vendor Tidak Diketahui',
            info_bank: data.nama_bank ? `${data.nama_bank} - ${data.no_rekening} a.n ${data.atas_nama}` : '-',
            total: data.total_tagihan,
            items: data.pesanan_item_produksi?.map(i => ({
                id: i.id,
                id_pesan: i.pesanan_item?.pesan?.id_pesan || '-',
                tipe_pengerjaan: i.tipe_pengerjaan,
                qty_dikerjakan: i.qty_dikerjakan,
                total_tagihan_vendor: i.total_tagihan_vendor
            })) || []
        };
    }
    isDetailModalOpen.value = true;
};

const closeDetailModal = () => {
    isDetailModalOpen.value = false;
    setTimeout(() => { selectedVendorDetail.value = null; }, 300);
};

const openConfirmModal = (item) => {
    form.id_vendor = item.id_vendor;
    confirmData.value = { vendor: item.nama_vendor, bank: item.info_bank, total: item.total_hutang || item.total };
    isConfirmModalOpen.value = true;
};

const closeConfirmModal = () => {
    isConfirmModalOpen.value = false;
    form.reset();
    form.clearErrors();
    confirmData.value = { vendor: '', bank: '', total: 0 };
};

const doBayar = () => {
    if (!form.id_vendor) return;
    form.post(route('tagihan-vendor.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeConfirmModal();
            alertStore.show('Pembayaran berhasil diproses dan bukti tersimpan!', 'success');
            activeTab.value = 'riwayat';
        },
        onError: () => {
            if (!form.errors['bukti_bayar.file']) { alertStore.show('Gagal memproses pembayaran!', 'error'); }
        }
    });
};
</script>

<template>
    <Head title="Tagihan Vendor" />

    <!-- MODAL DETAIL PEKERJAAN VENDOR -->
    <dialog class="modal" :class="{ 'modal-open': isDetailModalOpen }">
        <div class="max-w-3xl modal-box bg-base-100">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-base-content">
                    Detail Pekerjaan: <span class="text-primary">{{ selectedVendorDetail?.nama_vendor }}</span>
                </h3>

                <!-- TAMBAHAN: Info Bank di Modal Detail -->
                <div class="flex items-center gap-2 mt-2">
                    <span class="px-3 py-1.5 text-xs font-bold border rounded-lg bg-base-200 border-base-300 text-base-content/80">
                        💳 Tujuan Transfer: <span class="font-mono text-primary">{{ selectedVendorDetail?.info_bank }}</span>
                    </span>
                </div>

                <p class="mt-3 text-sm opacity-80 text-base-content">
                    Rincian pekerjaan vendor untuk tagihan ini.
                </p>
            </div>

            <div class="overflow-x-auto border rounded-lg border-base-300">
                <table class="table w-full table-sm">
                    <thead class="bg-base-200 text-base-content/70">
                        <tr>
                            <th class="py-3">No. Pesanan (SO)</th>
                            <th class="py-3">Jenis Pekerjaan</th>
                            <th class="py-3 text-center">Qty</th>
                            <th class="py-3 text-right">Subtotal Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="pekerjaan in selectedVendorDetail?.items" :key="pekerjaan.id" class="border-b border-base-300 last:border-0 hover:bg-base-200/50">
                            <td class="font-mono text-xs">{{ pekerjaan.id_pesan }}</td>
                            <td class="font-medium">{{ pekerjaan.tipe_pengerjaan }}</td>
                            <td class="text-center">{{ pekerjaan.qty_dikerjakan }}</td>
                            <td class="font-mono font-medium text-right text-error">{{ formatRupiah(pekerjaan.total_tagihan_vendor) }}</td>
                        </tr>
                        <tr v-if="!selectedVendorDetail?.items?.length">
                            <td colspan="4" class="py-4 text-center text-sm opacity-50">Data rincian tidak ditemukan.</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-base-200/50">
                        <tr>
                            <th colspan="3" class="py-3 text-right text-base-content">Total Tagihan:</th>
                            <th class="py-3 font-mono text-base text-right text-error">{{ formatRupiah(selectedVendorDetail?.total) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="modal-action">
                <button type="button" class="btn btn-ghost" @click="closeDetailModal">Tutup</button>
                <!-- Tombol bayar melempar seluruh object detail -->
                <button
                    v-if="$can('tagihan-vendor', 'tambah') && !selectedVendorDetail?.is_lunas"
                    type="button"
                    class="btn btn-primary"
                    @click="closeDetailModal(); openConfirmModal(selectedVendorDetail)"
                >
                    Bayar Tagihan Ini
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop" @click="closeDetailModal">
            <button>close</button>
        </form>
    </dialog>

    <!-- MODAL PEMBAYARAN CUSTOM -->
    <dialog class="modal" :class="{ 'modal-open': isConfirmModalOpen }">
        <div class="max-w-md modal-box bg-base-100">
            <h3 class="text-lg font-bold text-base-content">Konfirmasi Pembayaran</h3>
            <p class="py-2 text-sm opacity-80 text-base-content">
                Silakan lakukan transfer sesuai rincian berikut, lalu lampirkan bukti transfernya.
            </p>

            <!-- TAMBAHAN: Card Info Transfer -->
            <div class="p-4 mb-4 border shadow-sm rounded-xl bg-info/5 border-info/20">
                <p class="mb-1 text-[10px] font-bold tracking-widest uppercase text-info/70">Tujuan Transfer</p>
                <p class="font-bold text-base-content">{{ confirmData.vendor }}</p>
                <p class="font-mono text-sm font-semibold text-primary">{{ confirmData.bank }}</p>

                <div class="flex items-center justify-between pt-3 mt-3 border-t border-info/20">
                    <span class="text-xs font-semibold text-base-content/70">Total Dibayar:</span>
                    <span class="text-lg font-mono font-bold text-error">{{ formatRupiah(confirmData.total) }}</span>
                </div>
            </div>

            <div class="mt-2 mb-6">
                <CustomInputFile
                    v-model="form.bukti_bayar"
                    label="Bukti Transfer / Nota"
                    :showTipeFile="false"
                    :error="form.errors['bukti_bayar.file']"
                    :disabled="form.processing"
                />
            </div>

            <div class="modal-action">
                <button type="button" class="btn btn-ghost" :disabled="form.processing" @click="closeConfirmModal">
                    Batal
                </button>
                <button type="button" class="btn btn-primary" :disabled="form.processing" @click="doBayar">
                    <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                    {{ form.processing ? 'Memproses...' : 'Ya, Bayar Lunas' }}
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop" @click="!form.processing && closeConfirmModal()">
            <button>close</button>
        </form>
    </dialog>

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Manajemen Tagihan Vendor
            </h2>
        </template>

        <div class="min-h-screen px-4 py-3 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">

                <!-- BAGIAN TABS & SEARCH -->
                <div class="flex flex-col sm:flex-row gap-4 mb-8 border-b border-base-300 justify-between sm:items-end pb-px">
                    <div class="flex gap-4">
                        <button
                            @click="activeTab = 'pending'"
                            :class="[
                                'pb-3 text-sm font-bold tracking-wide transition-colors border-b-2',
                                activeTab === 'pending'
                                    ? 'text-primary border-primary'
                                    : 'text-base-content/50 border-transparent hover:text-base-content/80'
                            ]"
                        >
                            Menunggu Pembayaran
                            <span v-if="pendingTagihan.length > 0" class="px-2 py-0.5 ml-2 text-xs text-white bg-error rounded-full">
                                {{ pendingTagihan.length }}
                            </span>
                        </button>
                        <button
                            @click="activeTab = 'riwayat'"
                            :class="[
                                'pb-3 text-sm font-bold tracking-wide transition-colors border-b-2',
                                activeTab === 'riwayat'
                                    ? 'text-primary border-primary'
                                    : 'text-base-content/50 border-transparent hover:text-base-content/80'
                            ]"
                        >
                            Riwayat Lunas
                        </button>
                    </div>

                    <!-- INPUT SEARCH BARU -->
                    <div class="w-full sm:w-72 pb-2">
                        <CustomInputSearch
                            v-model="search"
                            placeholder="Cari Vendor / Info Bank / ID Pesanan..."
                        />
                    </div>
                </div>

                <!-- TAB 1: PENDING -->
                <div v-if="activeTab === 'pending'">
                    <CustomTable :headers="headersPending">
                        <tr v-for="item in pendingTagihan" :key="item.id_vendor" class="transition-colors hover:bg-base-200/50">
                            <!-- Data Tabel Pending Sama -->
                            <td class="px-6 py-4 font-bold text-base-content">{{ item.nama_vendor }}</td>
                            <td class="px-6 py-4 text-xs font-medium text-base-content/70">{{ item.info_bank }}</td>
                            <td class="px-6 py-4 font-bold text-center text-base-content">
                                {{ item.jumlah_pekerjaan }} <span class="text-xs font-normal text-base-content/50">Item</span>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-error">{{ formatRupiah(item.total_hutang) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center space-x-2">
                                    <CustomButton @click="openDetailModal('pending', item)" variant="info" size="sm">Detail</CustomButton>
                                    <CustomButton v-if="$can('tagihan-vendor', 'tambah')" @click="openConfirmModal(item)" variant="primary" size="sm">Bayar Semua</CustomButton>
                                </div>
                            </td>
                        </tr>

                        <!-- Pesan Jika Kosong/Tidak Ditemukan -->
                        <tr v-if="pendingTagihan.length === 0">
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center opacity-30">
                                    <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-sm font-bold tracking-widest uppercase">{{ search ? 'Pencarian Tidak Ditemukan' : 'Tidak ada tagihan pending' }}</p>
                                </div>
                            </td>
                        </tr>
                    </CustomTable>
                </div>

                <!-- TAB 2: RIWAYAT LUNAS -->
                <div v-if="activeTab === 'riwayat'">
                    <CustomTable :headers="headersRiwayat">
                        <tr v-for="tagihan in riwayatTagihan.data" :key="tagihan.id" class="transition-colors hover:bg-base-200/50">
                            <!-- Data Tabel Riwayat Sama -->
                            <td class="px-6 py-4 text-sm text-base-content/80">
                                {{ new Date(tagihan.tanggal_bayar).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                            </td>
                            <td class="px-6 py-4 font-bold text-base-content">{{ tagihan.vendor?.nama_vendor }}</td>
                            <td class="px-6 py-4 font-mono font-bold text-success">{{ formatRupiah(tagihan.total_tagihan) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 text-[10px] font-black tracking-wider uppercase border rounded-md text-success border-success/30 bg-success/10">Lunas</span>
                                    <a v-if="tagihan.bukti_bayar" :href="`/storage/${tagihan.bukti_bayar}`" target="_blank" class="text-primary hover:text-primary-focus" title="Lihat Bukti">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" /></svg>
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center space-x-2">
                                    <CustomButton @click="openDetailModal('riwayat', tagihan)" variant="info" size="sm">Detail</CustomButton>
                                </div>
                            </td>
                        </tr>

                        <!-- Pesan Jika Kosong/Tidak Ditemukan -->
                        <tr v-if="riwayatTagihan.data.length === 0">
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center opacity-30">
                                    <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    <p class="text-sm font-bold tracking-widest uppercase">{{ search ? 'Pencarian Tidak Ditemukan' : 'Belum ada riwayat pembayaran' }}</p>
                                </div>
                            </td>
                        </tr>
                    </CustomTable>

                    <!-- PAGINATION BARU UNTUK TAB RIWAYAT -->
                    <div class="flex justify-center pb-8 mt-8" v-if="riwayatTagihan.links && riwayatTagihan.links.length > 3">
                        <div class="join">
                            <Link v-for="(link, i) in riwayatTagihan.links" :key="i"
                                :href="link.url || '#'"
                                class="font-medium join-item btn btn-sm"
                                :class="[
                                    link.active ? 'btn-active btn-neutral' : 'bg-base-100',
                                    !link.url ? 'btn-disabled text-base-content/30' : ''
                                ]"
                                v-html="link.label"
                            ></Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </StafLayout>
</template>
