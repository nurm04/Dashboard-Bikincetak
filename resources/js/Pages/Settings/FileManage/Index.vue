<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Trash2, FolderArchive, Image as ImageIcon } from 'lucide-vue-next';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';

const props = defineProps({
    desain_pesanan: Array,
    nota_vendor: Array,
    bukti_bayar_vendor: Array,
});

const activeTab = ref('desain_pesanan');
const showConfirmModal = ref(false);
const form = useForm({files_to_delete: []});

const currentFiles = computed(() => {
    if (activeTab.value === 'desain_pesanan') return props.desain_pesanan;
    if (activeTab.value === 'nota_vendor') return props.nota_vendor;
    if (activeTab.value === 'bukti_bayar_vendor') return props.bukti_bayar_vendor;
    return [];
});

const headersFile = computed(() => [
    'Pilih', 'Preview File', 'Nama File', 'ID Referensi', 'Ukuran', 'Terakhir Diperbarui'
]);

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

const submitDelete = () => {
    if (form.files_to_delete.length === 0) return;
    showConfirmModal.value = true;
};

const executeDelete = () => {
    form.post('/file-manage/hapus', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showConfirmModal.value = false;
        },
    });
};

watch(activeTab, () => {
    form.files_to_delete = [];
});

const toggleAll = (event) => {
    if (event.target.checked) {
        form.files_to_delete = currentFiles.value.map(file => file.path);
    } else {
        form.files_to_delete = [];
    }
};

const totalSizeAllMB = computed(() => {
    const totalKB = currentFiles.value.reduce((sum, file) => sum + parseFloat(file.size || 0), 0);
    return (totalKB / 1024).toFixed(2);
});

const totalSizeSelectedMB = computed(() => {
    const selectedFiles = currentFiles.value.filter(file => form.files_to_delete.includes(file.path));
    const totalKB = selectedFiles.reduce((sum, file) => sum + parseFloat(file.size || 0), 0);
    return (totalKB / 1024).toFixed(2);
});
const getReferenceUrl = (file) => {
    if (file.id_referensi === 'Data referensi sudah dihapus') return '#';

    switch (activeTab.value) {
        case 'desain_pesanan':
            return `/pesan?search=${file.id_referensi}`;
        case 'nota_vendor':
            return `/produksi/histori?search=${file.id_referensi}`;
        case 'bukti_bayar_vendor':
            return `/tagihan-vendor?search=${file.id_referensi}`;
        default:
            return '#';
    }
};
</script>

<template>
    <Head title="Manajemen Storage File" />
    <StafLayout>
        <CustomAlertConfirm
            :show="showConfirmModal"
            type="error"
            title="HAPUS PERMANEN"
            :message="`Yakin ingin menghapus ${form.files_to_delete.length} file secara permanen? Kapasitas sebesar ${totalSizeSelectedMB} MB akan dibebaskan dan referensi di database akan dikosongkan.`"
            confirmText="Ya, Hapus File"
            cancelText="Batal"
            :loading="form.processing"
            @close="showConfirmModal = false"
            @confirm="executeDelete"
        />
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link href="/dashboard" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h2 class="text-xl font-semibold leading-tight text-base-content">
                            Manajemen Storage File
                        </h2>
                        <p class="mt-1 text-sm text-base-content/60">Pembersihan file fisik untuk menghemat kapasitas server.</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="px-4 py-8 mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">

            <!-- Navigation Tabs -->
            <div class="flex pb-px space-x-2 border-b border-base-300">
                <button
                    @click="activeTab = 'desain_pesanan'"
                    :class="[activeTab === 'desain_pesanan' ? 'border-base-content text-base-content font-semibold' : 'border-transparent text-base-content/60 hover:text-base-content']"
                    class="px-4 py-2 transition-colors duration-200 border-b-2"
                >
                    Desain Pesanan ({{ desain_pesanan.length }})
                </button>
                <button
                    @click="activeTab = 'nota_vendor'"
                    :class="[activeTab === 'nota_vendor' ? 'border-base-content text-base-content font-semibold' : 'border-transparent text-base-content/60 hover:text-base-content']"
                    class="px-4 py-2 transition-colors duration-200 border-b-2"
                >
                    Nota Vendor ({{ nota_vendor.length }})
                </button>
                <button
                    @click="activeTab = 'bukti_bayar_vendor'"
                    :class="[activeTab === 'bukti_bayar_vendor' ? 'border-base-content text-base-content font-semibold' : 'border-transparent text-base-content/60 hover:text-base-content']"
                    class="px-4 py-2 transition-colors duration-200 border-b-2"
                >
                    Bukti Bayar ({{ bukti_bayar_vendor.length }})
                </button>
            </div>

            <!-- Action Bar -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border shadow-sm bg-base-100 border-base-200 rounded-xl gap-4">

                <div class="flex items-center gap-4">
                    <!-- Checkbox Pilih Semua -->
                    <label class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity">
                        <input
                            type="checkbox"
                            @change="toggleAll"
                            :checked="form.files_to_delete.length === currentFiles.length && currentFiles.length > 0"
                            class="checkbox checkbox-sm checkbox-error"
                        >
                        <span class="text-sm font-semibold text-base-content select-none">Pilih Semua di Tab Ini</span>
                    </label>

                    <div class="hidden w-px h-6 sm:block bg-base-300"></div> <!-- Garis pemisah -->

                    <!-- Indikator Total File dan Total MB -->
                    <p class="text-sm font-medium text-base-content/70">
                        Terpilih: <span class="font-bold text-base-content">{{ form.files_to_delete.length }}</span> file
                        <span class="ml-1 text-xs px-2 py-0.5 rounded-full bg-base-200 border border-base-300">
                            Hapus <strong class="text-error">{{ totalSizeSelectedMB }} MB</strong> dari <strong class="text-base-content">{{ totalSizeAllMB }} MB</strong>
                        </span>
                    </p>
                </div>

                <button
                    v-if="$can('file-manage', 'hapus')"
                    @click="submitDelete"
                    :disabled="form.processing || form.files_to_delete.length === 0"
                    class="w-full text-white sm:w-auto btn btn-error btn-sm disabled:opacity-50"
                >
                    <Trash2 class="w-4 h-4 mr-1" />
                    Hapus Terpilih
                </button>
            </div>

            <!-- KONDISI KOSONG -->
            <div v-if="currentFiles.length === 0" class="flex flex-col items-center justify-center py-24 text-center border rounded-xl border-base-200 bg-base-100">
                <FolderArchive class="w-12 h-12 mb-4 text-base-content/20" />
                <h3 class="text-base font-semibold text-base-content">Folder Bersih</h3>
                <p class="mt-1 text-sm text-base-content/50">Tidak ada file lampiran yang tersimpan di direktori ini.</p>
            </div>

            <!-- TABEL DATA FILE -->
            <CustomTable v-else :headers="headersFile" class="border-none shadow-none">
                <template #header-0>
                    <input
                        type="checkbox"
                        @change="toggleAll"
                        :checked="form.files_to_delete.length === currentFiles.length && currentFiles.length > 0"
                        class="checkbox checkbox-sm checkbox-error"
                    >
                </template>

                <tr v-for="file in currentFiles" :key="file.path" class="transition-colors border-b hover:bg-base-200/30 border-base-200/50">

                    <td class="w-12 px-4 py-3 align-middle">
                        <input
                            type="checkbox"
                            :value="file.path"
                            v-model="form.files_to_delete"
                            class="checkbox checkbox-sm checkbox-error"
                        >
                    </td>

                    <td class="w-24 px-4 py-3 align-middle">
                        <a :href="file.url" target="_blank" class="flex items-center justify-center w-12 h-12 overflow-hidden transition-opacity border rounded bg-base-200 border-base-300 hover:opacity-80">
                            <img v-if="file.url.match(/\.(jpeg|jpg|gif|png)$/i)" :src="file.url" class="object-cover w-full h-full" alt="preview">
                            <ImageIcon v-else class="w-5 h-5 text-base-content/40" />
                        </a>
                    </td>

                    <!-- UBAH: Nama file sekarang bisa diklik -->
                    <td class="max-w-xs px-4 py-3 text-xs font-medium truncate align-middle text-base-content/80" :title="file.name">
                        <a :href="file.url" target="_blank" class="transition-colors hover:text-blue-600 hover:underline">
                            {{ file.name }}
                        </a>
                    </td>

                    <!-- UBAH: Link ID Referensi Dinamis -->
                    <td class="px-4 py-3 align-middle">
                        <span v-if="file.id_referensi === 'Data referensi sudah dihapus'"
                              class="text-[11px] font-mono font-black px-2 py-1 rounded border bg-error/10 text-error border-error/20">
                            {{ file.id_referensi }}
                        </span>

                        <Link v-else
                              :href="getReferenceUrl(file)"
                              class="inline-block text-[11px] font-mono font-black text-blue-600 bg-blue-50 px-2 py-1 rounded border border-blue-200 hover:bg-blue-600 hover:text-white transition-colors">
                            {{ file.id_referensi }}
                        </Link>
                    </td>

                    <td class="px-4 py-3 text-xs font-semibold align-middle text-base-content">
                        {{ file.size }}
                    </td>

                    <td class="px-4 py-3 text-xs align-middle text-base-content/70">
                        {{ formatTanggal(file.updated_at) }}
                    </td>

                </tr>
            </CustomTable>
        </div>
    </StafLayout>
</template>
