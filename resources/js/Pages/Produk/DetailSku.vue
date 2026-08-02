<script setup>
import { ref, computed } from 'vue';
import { alertStore } from '@/Utils/alertStore';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';
import CustomTableAction from '@/Components/CustomTableAction.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    produk: Object,
});

const headers = ['Nama SKU / ID SKU', 'Daftar Finishing', 'Daftar Harga Grosir', 'Daftar Harga Pengerjaan', 'Daftar Diskon', 'Aksi'];

// --- STATE DELETE ---
const isDeleteModalOpen = ref(false);
const selectedId = ref(null);
const formDelete = useForm({});

// --- STATE IMPORT CSV ---
const isImportModalOpen = ref(false);
const importForm = useForm({
    skala_import: 'produk_ini', // [TAMBAHAN] Pilihan skala: 'produk_ini' atau 'semua_produk'
    tipe_import: 'sku_finishing',
    file_csv: null,
});

// [TAMBAHAN] Opsi skala import
const skalaOptions = computed(() => [
    { value: 'produk_ini', label: `Hanya untuk Produk Ini (${props.produk?.id_produk})` },
    { value: 'semua_produk', label: 'Berlaku untuk Semua Produk (Sesuai ID di CSV)' },
]);

const importOptions = [
    { value: 'sku_finishing', label: '1. Data Tambahan Finishing' },
    { value: 'harga_bertingkat', label: '2. Data Harga Bertingkat (Grosir)' },
    { value: 'harga_pengerjaan', label: '3. Data Harga Pengerjaan (SLA)' },
    { value: 'diskon_customer', label: '4. Data Diskon Customer (Member)' },
    { value: 'komposisi', label: '5. Data Komposisi (BOM)' },
];

// --- FUNGSI DELETE ---
const openDeleteModal = (id) => {
    selectedId.value = id;
    isDeleteModalOpen.value = true;
};
const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    selectedId.value = null;
};
const doDelete = () => {
    if (!selectedId.value) return;
    formDelete.delete(route('sku.destroy', selectedId.value), {
        onSuccess: () => closeDeleteModal(),
        onError: () => alertStore.show('Gagal menghapus Sku!', 'error')
    });
};

// --- FUNGSI IMPORT CSV ---
const handleFileChange = (e) => {
    importForm.file_csv = e.target.files[0];
};

const closeImportModal = () => {
    isImportModalOpen.value = false;
    importForm.reset();
    importForm.clearErrors();
};

const submitImport = () => {
    if (!importForm.file_csv) {
        alertStore.show('Pilih file CSV terlebih dahulu!', 'warning');
        return;
    }

    importForm.post(route('sku.importCsv', props.produk.id_produk), {
        preserveScroll: true,
        onSuccess: () => {
            closeImportModal();
            alertStore.show('Data berhasil di-import!', 'success');
        },
        onError: (errors) => {
            console.error(errors);
            alertStore.show(errors.file_csv || errors.message || 'Gagal melakukan import data', 'error');
        }
    });
};

// --- FUNGSI DOWNLOAD TEMPLATE CSV DINAMIS ---
const downloadTemplate = () => {
    let csvContent = "data:text/csv;charset=utf-8,";
    const tipe = importForm.tipe_import;

    let headerArray = [];
    let rowContoh = [];

    // [UPDATE] Coba berikan contoh SKU dari produk ini jika ada
    const contohSku = props.produk?.produk_sku?.[0]?.id_sku || "PRD-001-SKU-001";

    if (tipe === 'sku_finishing') {
        headerArray = ["id_sku", "id_pilihan_finishing", "minimum_pesan", "harga_tambahan"];
        rowContoh = [contohSku, "FIN-001", "1", "5000"];
    } else if (tipe === 'harga_bertingkat') {
        headerArray = ["id_sku", "min", "max", "tipe", "nilai"];
        rowContoh = [contohSku, "10", "50", "nominal", "2000"];
    } else if (tipe === 'harga_pengerjaan') {
        headerArray = ["id_sku", "pengerjaan", "tipe", "nilai"];
        rowContoh = [contohSku, "1 Hari Jadi", "persen", "50"];
    } else if (tipe === 'diskon_customer') {
        headerArray = ["id_sku", "id_role_customer", "tipe", "nilai"];
        rowContoh = [contohSku, "ROLE-RESELLER", "persen", "10"];
    } else if (tipe === 'komposisi') {
        headerArray = ["id_sku", "id_bahan_baku", "id_pilihan_finishing", "jumlah_pakai", "hpp"];
        rowContoh = [contohSku, "BHN-001", "", "0.5", "15000"];
    }

    csvContent += headerArray.join(",") + "\r\n";
    csvContent += rowContoh.join(",") + "\r\n";

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Template_${tipe}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <!-- MODAL DELETE (Dibiarkan Sesuai Aslinya) -->
    <CustomAlertConfirm
        :show="isDeleteModalOpen" type="error" title="Hapus Data Produk Sku"
        message="Menghapus Produk Sku akan menghapus seluruh harga grosir dan harga pengerjaan di dalamnya secara permanen. Lanjutkan? "
        confirmText="Ya, Hapus Semua" @close="closeDeleteModal" @confirm="doDelete"
    />

    <!-- MODAL IMPORT CSV -->
    <dialog :class="['modal', { 'modal-open': isImportModalOpen }]">
        <div class="modal-box bg-base-100 rounded-2xl max-w-lg">
            <h3 class="font-black text-lg mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-primary"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                Import Data Pendukung SKU
            </h3>

            <div class="space-y-4">
                <!-- [TAMBAHAN] Pilihan Skala Import -->
                <CustomSelect
                    v-model="importForm.skala_import"
                    label="Skala Penerapan Data"
                    :options="skalaOptions"
                    valueKey="value" labelKey="label"
                />

                <CustomSelect
                    v-model="importForm.tipe_import"
                    label="Pilih Target Tabel / Data"
                    :options="importOptions"
                    valueKey="value" labelKey="label"
                />

                <div class="form-control w-full">
                    <label class="label"><span class="label-text text-xs font-bold opacity-70">Upload File CSV</span></label>
                    <input type="file" accept=".csv" @change="handleFileChange" class="file-input file-input-bordered file-input-primary w-full shadow-sm" />
                    <label class="label mt-1">
                        <span class="label-text-alt text-error font-bold" v-if="importForm.errors.file_csv">{{ importForm.errors.file_csv }}</span>
                    </label>
                </div>

                <div class="p-3 bg-info/10 border border-info/20 rounded-xl text-xs flex justify-between items-center">
                    <div class="flex items-center gap-2 text-info">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                        <span class="font-medium">Bingung formatnya?</span>
                    </div>
                    <button type="button" @click="downloadTemplate" class="text-info font-black hover:underline tracking-widest uppercase">
                        Download Template
                    </button>
                </div>
            </div>

            <div class="modal-action mt-6 gap-2">
                <button type="button" class="btn text-xs font-bold uppercase" @click="closeImportModal" :disabled="importForm.processing">Batal</button>
                <CustomButton variant="primary" class="px-8 text-xs font-black uppercase tracking-widest" @click="submitImport" :disabled="importForm.processing || !importForm.file_csv">
                    <span v-if="importForm.processing" class="loading loading-spinner loading-sm"></span>
                    <span v-else>Mulai Import</span>
                </CustomButton>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button @click="closeImportModal">close</button></form>
    </dialog>

    <Head title="Detail Sku Produk" />

    <StafLayout>
        <template #header>
            <!-- REVISI: flex-col di mobile, flex-row di desktop, tambah gap-4 -->
            <div class="flex flex-col md:flex-row md:items-center justify-between w-full gap-4">
                <div class="flex items-center gap-4">
                    <!-- REVISI: Tambah shrink-0 biar tombol back ga penyok -->
                    <Link :href="route('produk.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300 shrink-0">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Detail Sku Produk {{ props.produk.id_produk }}
                    </h2>
                </div>

                <!-- REVISI: Tambah w-full md:w-auto biar tombolnya menuhi layar pas di HP -->
                <button @click="isImportModalOpen = true" class="w-full md:w-auto btn btn-sm btn-primary rounded-xl text-xs font-black uppercase tracking-widest shadow-md shadow-primary/20">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                    Import CSV
                </button>
            </div>
        </template>

        <div class="min-h-screen px-4 py-3 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <!-- ISI TABLE -->
                <CustomTable :headers="headers">
                   <tr v-for="sku in props.produk.produk_sku" :key="sku.id_sku" class="transition-colors hover:bg-base-200/50">
                        <!-- REVISI: Tambah whitespace-nowrap di semua td biar tabel aman pas di-scroll mobile -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-base-content">{{ sku.nama_sku }}</div>
                            <div class="text-[10px] text-primary font-medium">{{ sku.id_sku }}</div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <div v-if="sku.sku_finishing?.length > 0" class="flex items-center gap-2">
                                <span class="font-black badge badge-primary badge-sm">{{ sku.sku_finishing.length }}</span>
                                <span class="text-[10px] font-bold uppercase opacity-50">Finishing</span>
                            </div>
                            <span v-else class="text-[10px] italic opacity-30">Belum diatur</span>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <div v-if="sku.harga_bertingkat?.length > 0" class="flex items-center gap-2">
                                <span class="font-black badge badge-primary badge-sm">{{ sku.harga_bertingkat.length }}</span>
                                <span class="text-[10px] font-bold uppercase opacity-50">Level Grosir</span>
                            </div>
                            <span v-else class="text-[10px] italic opacity-30">Belum diatur</span>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <div v-if="sku.harga_pengerjaan?.length > 0" class="flex items-center gap-2">
                                <span class="font-black text-white badge badge-info badge-sm">{{ sku.harga_pengerjaan.length }}</span>
                                <span class="text-[10px] font-bold uppercase opacity-50">Opsi Estimasi</span>
                            </div>
                            <span v-else class="text-[10px] italic opacity-30">Belum diatur</span>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <div v-if="sku.diskon_customer?.length > 0" class="flex items-center gap-2">
                                <span class="font-black text-white badge badge-warning badge-sm">{{ sku.diskon_customer.length }}</span>
                                <span class="text-[10px] font-bold uppercase opacity-50">Diskon Customer</span>
                            </div>
                            <span v-else class="text-[10px] italic opacity-30">Belum diatur</span>
                        </td>

                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <CustomTableAction v-slot="{ close }">
                                <div class="px-4 py-2 text-[10px] font-black text-base-content/20 uppercase tracking-widest border-b border-base-300/50 mb-1">
                                    Menu Produk
                                </div>
                                <Link v-if="$can('produk-sku', 'tambah')" :href="route('sku.finishing', sku.id_sku)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-success hover:bg-success/10 transition-colors">Finishing</Link>
                                <Link v-if="$can('produk-sku', 'tambah')" :href="route('sku.hargaBertingkat', sku.id_sku)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-primary hover:bg-primary/10 transition-colors">Harga Bertingkat</Link>
                                <Link v-if="$can('produk-sku', 'tambah')" :href="route('sku.hargaPengerjaan', sku.id_sku)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-info hover:bg-info/10 transition-colors">Harga Pengerjaan</Link>
                                <Link v-if="$can('produk-sku', 'tambah')" :href="route('sku.diskonCustomer', sku.id_sku)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-warning hover:bg-warning/10 transition-colors">Diskon Member</Link>
                                <Link v-if="$can('produk-sku', 'tambah')" :href="route('sku.komposisi', sku.id_sku)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-base-content hover:bg-base-content/10 transition-colors">Komposisi Produk</Link>
                                <div class="my-1 border-t border-base-300/50"></div>
                                <button v-if="$can('produk-sku', 'hapus')" @click="openDeleteModal(sku.id_sku); close()" class="flex items-center w-full px-4 py-2.5 text-sm font-bold text-error hover:bg-error/10 transition-colors">Hapus Sku</button>
                            </CustomTableAction>
                        </td>
                    </tr>
                    <tr v-if="props.produk.produk_sku.length === 0">
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <p class="text-sm font-bold tracking-widest uppercase">Belum ada Sku di Produk {{ props.produk.id_produk }}</p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
