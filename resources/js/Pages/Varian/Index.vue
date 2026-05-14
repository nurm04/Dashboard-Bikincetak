<script setup>
import { ref } from 'vue';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomTable from '@/Components/CustomTable.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';

const props = defineProps({
    varians: Array,
});

const headers = ['ID Varian', 'Nama Varian', 'Daftar Pilihan', 'Aksi'];

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

    form.delete(route('varian.destroy', selectedId.value), {
        onSuccess: () => {
            closeDeleteModal();
            alertStore.show('Master Varian berhasil dihapus!', 'success');
        },
        onError: () => alertStore.show('Gagal menghapus varian!', 'error')
    });
};
</script>

<template>
    <CustomAlertConfirm
        :show="isDeleteModalOpen"
        type="error"
        title="Hapus Master Varian"
        message="Menghapus varian akan menghapus seluruh pilihan varian di dalamnya secara permanen. Lanjutkan? "
        confirmText="Ya, Hapus Semua"
        @close="isDeleteModalOpen = false"
        @confirm="doDelete"
    />

    <Head title="Master Varian" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Master Varian Produk
            </h2>
        </template>

        <div class="min-h-screen px-4 py-3 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">

                <div class="flex flex-col gap-4 mb-8 md:flex-row md:items-center md:justify-between">
                    <CustomButton v-if="$can('varian', 'tambah')" type="link" :href="route('varian.create')" variant="primary">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </template>
                        Tambah Master Varian
                    </CustomButton>
                </div>

                <CustomTable :headers="headers">
                    <tr v-for="varian in varians" :key="varian.id_varian" class="transition-colors hover:bg-base-200/50">
                        <td class="px-6 py-4 font-mono text-xs font-bold text-primary">
                            {{ varian.id_varian }}
                        </td>

                        <td class="px-6 py-4 font-bold text-base-content">
                            {{ varian.nama_varian }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="pilihan in varian.pilihan_varian"
                                    :key="pilihan.id_pilihan"
                                    class="px-2 py-1 rounded-md bg-base-200 border border-base-300 text-[10px] font-black uppercase tracking-wider text-base-content/60"
                                >
                                    {{ pilihan.nama_pilihan }}
                                </span>
                                <span v-if="!varian.pilihan_varian?.length" class="text-xs italic text-base-content/30">
                                    Tidak ada pilihan
                                </span>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-center space-x-2">
                                <CustomButton v-if="$can('varian', 'ubah')" type="link" :href="route('varian.edit', varian.id_varian)" variant="info" size="sm">
                                    Edit
                                </CustomButton>
                                <CustomButton v-if="$can('varian', 'hapus')" @click="openDeleteModal(varian.id_varian)" variant="error" size="sm">
                                    Hapus
                                </CustomButton>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="varians.length === 0">
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-sm font-bold tracking-widest uppercase">Belum ada Master Varian</p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
