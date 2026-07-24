<script setup>
import { ref, watch } from 'vue';
import { alertStore } from '@/Utils/alertStore';
import { Head, useForm, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomInputSearch from '@/Components/Form/CustomInputSearch.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';

const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const props = defineProps({
    vendors: Array,
    filters: Object
});

const headers = ['ID Vendor', 'Perusahaan / Email', 'PIC & No. HP', 'Status', 'Aksi'];

const isDeleteModalOpen = ref(false);
const selectedId = ref(null);
const form = useForm({});

const search = ref(props.filters?.search || '');

watch(
    search,
    debounce((newSearch) => {
        router.get(route('vendor.index'), {
            search: newSearch,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300)
);

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

    form.delete(route('vendor.destroy', selectedId.value), {
        onSuccess: () => {
            closeDeleteModal();
            alertStore.show('Data vendor berhasil dihapus!', 'success');
        },
        onError: () => alertStore.show('Gagal menghapus vendor, pastikan tidak ada pesanan terikat!', 'error')
    });
};
</script>

<template>
    <CustomAlertConfirm
        :show="isDeleteModalOpen"
        type="error"
        title="Hapus Vendor"
        message="Apakah Anda yakin ingin menghapus Vendor ini? Data yang dihapus tidak dapat dikembalikan."
        confirmText="Ya, Hapus"
        @close="closeDeleteModal"
        @confirm="doDelete"
    />
    <Head title="Data Vendor" />
    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Database Vendor
            </h2>
        </template>
        <div class="min-h-screen px-4 py-3 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="flex flex-col gap-4 mb-8 md:flex-row md:items-center md:justify-between">
                    <CustomButton v-if="$can('vendor', 'tambah')" type="link" :href="route('vendor.create')" variant="primary" size="md" class="shrink-0 rounded-xl">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        </template>
                        Registrasi Vendor
                    </CustomButton>
                </div>
                <div class="mb-3 flex flex-col w-full gap-3 sm:flex-row sm:items-center md:w-auto">
                    <CustomInputSearch
                        v-model="search"
                        class="w-full sm:w-64"
                        placeholder="Cari Perusahaan / PIC / No. HP..."
                    />
                </div>

                <CustomTable :headers="headers">
                    <tr v-for="vendor in vendors" :key="vendor.id_vendor" class="transition-colors hover:bg-base-200/50">
                        <td class="px-6 py-4 font-mono text-xs font-bold text-primary">{{ vendor.id_vendor }}</td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-base-content">{{ vendor.nama_vendor }}</div>
                            <div class="text-[10px] text-base-content/50 font-medium">{{ vendor.user?.email }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-base-content">{{ vendor.nama_pic || '-' }}</div>
                            <div class="text-[10px] text-base-content/50 font-bold">{{ vendor.no_hp }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-tight border"
                                :class="vendor.is_active ? 'bg-success/20 text-success border-success/30' : 'bg-error/20 text-error border-error/30'"
                            >
                                {{ vendor.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center space-x-2">
                                <CustomButton v-if="$can('vendor', 'ubah')" type="link" :href="route('vendor.edit', vendor.id_vendor)" variant="info" size="sm">
                                    Edit
                                </CustomButton>
                                <CustomButton v-if="$can('vendor', 'hapus')" @click="openDeleteModal(vendor.id_vendor)" variant="error" size="sm">
                                    Hapus
                                </CustomButton>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="vendors.length === 0">
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-sm font-bold tracking-widest uppercase">
                                    {{ search ? 'Pencarian Tidak Ditemukan' : 'Belum ada Master Vendor' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
