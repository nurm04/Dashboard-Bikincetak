<script setup>
import { ref } from 'vue';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomTable from '@/Components/CustomTable.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';

const props = defineProps({
    pembelian: Array,
});

const headers = ['ID Pembelian', 'Tanggal', 'Supplier', 'Total Biaya', 'Staf', 'Aksi'];

const isDeleteModalOpen = ref(false);
const selectedId = ref(null);
const form = useForm({});

const openDeleteModal = (id) => {
    selectedId.value = id;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    selectedId.value = null;
    form.reset();
};

const doDelete = () => {
    if (!selectedId.value) return;

    form.delete(route('pembelian-bahan.destroy', selectedId.value), {
        onSuccess: () => {
            closeDeleteModal();
            alertStore.show('Transaksi pembelian berhasil dihapus!', 'success');
        },
        onError: () => alertStore.show('Gagal menghapus transaksi!', 'error')
    });
};
</script>

<template>
    <CustomAlertConfirm
        :show="isDeleteModalOpen"
        type="error"
        title="Hapus Transaksi Pembelian"
        message="Menghapus transaksi ini akan mengembalikan stok bahan baku seperti semula. Lanjutkan?"
        confirmText="Ya, Hapus Semua"
        @close="isDeleteModalOpen = false"
        @confirm="doDelete"
    />

    <Head title="Pembelian Bahan" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Transaksi Pembelian Bahan
            </h2>
        </template>

        <div class="min-h-screen px-4 py-3 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">

                <div class="flex flex-col gap-4 mb-8 md:flex-row md:items-center md:justify-between">
                    <CustomButton v-if="$can('pembelian-bahan', 'tambah')" type="link" :href="route('pembelian-bahan.create')" variant="primary">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </template>
                        Tambah Pembelian
                    </CustomButton>
                </div>

                <CustomTable :headers="headers">
                    <tr v-for="pb in pembelian" :key="pb.id_pembelian" class="transition-colors hover:bg-base-200/50">
                        <td class="px-6 py-4 font-mono text-xs font-bold text-primary">
                            {{ pb.id_pembelian }}
                        </td>

                        <td class="px-6 py-4 font-bold text-base-content">
                            {{ String(pb.tanggal_beli).split('T')[0] }}
                        </td>

                        <td class="px-6 py-4 font-bold text-base-content">
                            {{ pb.nama_supplier }}
                        </td>

                        <td class="px-6 py-4 font-bold text-base-content">
                            Rp {{ pb.total_biaya.toLocaleString() }}
                        </td>

                        <td class="px-6 py-4 font-bold text-base-content">
                            {{ pb.staf?.user?.name ?? '-' }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-center space-x-2">
                                <CustomButton v-if="$can('pembelian-bahan', 'ubah')" type="link" :href="route('pembelian-bahan.edit', pb.id_pembelian)" variant="info" size="sm">
                                    Edit
                                </CustomButton>
                                <CustomButton v-if="$can('pembelian-bahan', 'hapus')" @click="openDeleteModal(pb.id_pembelian)" variant="error" size="sm">
                                    Hapus
                                </CustomButton>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="pembelian.length === 0">
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-sm font-bold tracking-widest uppercase">Belum ada Transaksi</p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
