<script setup>
import { ref } from 'vue';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomTable from '@/Components/CustomTable.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';

const props = defineProps({
    akuns: Array,
});

const headers = ['ID Akun', 'Nama Akun', 'Kategori', 'Saldo Normal', 'Aksi'];

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

    form.delete(route('akun.destroy', selectedId.value), {
        onSuccess: () => {
            closeDeleteModal();
            alertStore.show('Data berhasil dihapus!', 'success');
        },
        onError: () => alertStore.show('Ada kesalahan saat menghapus!', 'error')
    });
};
</script>

<template>
    <CustomAlertConfirm
        :show="isDeleteModalOpen"
        type="error"
        title="Hapus COA"
        message="Apakah Anda yakin ingin menghapus COA ini? Data yang dihapus tidak dapat dikembalikan."
        confirmText="Ya, Hapus"
        @close="closeDeleteModal"
        @confirm="doDelete"
    />
    <Head title="Manajemen COA" />
    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Database COA
            </h2>
        </template>

        <div class="min-h-screen px-4 py-3 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">

                <div class="flex flex-col gap-4 mb-8 md:flex-row md:items-center md:justify-between">
                    <CustomButton v-if="$can('akun', 'tambah')" type="link" :href="route('akun.create')" variant="primary" class="px-6 py-2 rounded-xl">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        </template>
                        Tambah COA
                    </CustomButton>
                </div>

                <CustomTable :headers="headers">
                    <tr v-for="item in akuns" :key="item.id_akun">
                        <td class="px-6 py-4 font-mono text-xs font-bold text-primary">{{ item.id_akun }}</td>
                        <td class="px-6 py-4 font-bold text-base-content">{{ item.nama_akun }}</td>
                        <td>
                            <span class="px-2 py-0.5 rounded-md bg-success text-[10px] font-bold uppercase tracking-tight text-base-content/70 border border-base-300">
                                {{ item.kategori }}
                            </span>
                        </td>
                        <td class="font-medium capitalize">{{ item.saldo_normal }}</td>
                        <td>
                            <div class="flex space-x-2">
                                <CustomButton v-if="$can('akun', 'ubah')" type="link" :href="route('akun.edit', item.id_akun || item.id)" variant="info" size="sm">
                                    Edit
                                </CustomButton>
                                <CustomButton v-if="$can('akun', 'hapus')" @click="openDeleteModal(item.id_akun)" variant="error" size="sm">
                                    Hapus
                                </CustomButton>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="akuns.length === 0">
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-sm font-bold tracking-widest uppercase">Belum ada Master COA</p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
