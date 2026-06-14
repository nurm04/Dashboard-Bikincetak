<script setup>
import { ref } from 'vue';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomTable from '@/Components/CustomTable.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';
import CustomTableAction from '@/Components/CustomTableAction.vue';

const props = defineProps({
    customers: Array,
});

const headers = ['ID Customer', 'Nama / Email', 'No. WhatsApp', 'Level Member', 'Aksi'];

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

    form.delete(route('customer.destroy', selectedId.value), {
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
        title="Hapus Customer"
        message="Apakah Anda yakin ingin menghapus Customer ini? Data yang dihapus tidak dapat dikembalikan."
        confirmText="Ya, Hapus"
        @close="closeDeleteModal"
        @confirm="doDelete"
    />
    <Head title="Data Customer" />
    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Database Customer
            </h2>
        </template>
        <div class="min-h-screen px-4 py-3 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="flex flex-col gap-4 mb-10 md:flex-row md:items-center md:justify-between">
                    <CustomButton v-if="$can('akun', 'tambah')" type="link" :href="route('customer.create')" variant="primary" size="md">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        </template>
                        Registrasi Customer
                    </CustomButton>
                </div>

                <CustomTable :headers="headers">
                    <tr v-for="cust in customers" :key="cust.id_customer" class="transition-colors hover:bg-base-200/50">
                        <td class="px-6 py-4 font-mono text-xs font-bold text-primary">{{ cust.id_customer }}</td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-base-content">{{ cust.user?.name }}</div>
                            <div class="text-[10px] text-base-content/50 font-medium">{{ cust.user?.email }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-base-content/70">{{ cust.no_hp }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded-md bg-success text-[10px] font-bold uppercase tracking-tight text-base-content/70 border border-base-300">
                                {{ cust.role_customer?.role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <CustomTableAction v-slot="{ close }">
                                <div class="px-4 py-2 text-[10px] font-black text-base-content/20 uppercase tracking-widest border-b border-base-300/50 mb-1">
                                    Menu Customer
                                </div>

                                <Link v-if="$can('customer', 'ubah')" :href="route('customer.edit', cust.id_customer)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-primary hover:bg-primary/10 transition-colors">
                                    <svg class="w-4 h-4 mr-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    Edit Customer
                                </Link>
                                <Link v-if="$can('alamat', 'ubah')" :href="route('alamat.customer', cust.id_customer)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-info hover:bg-info/10 transition-colors">
                                    <svg class="w-4 h-4 mr-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    Alamat Customer
                                </Link>
                                <Link v-if="$can('customer', 'ubah')" :href="route('customer.password', cust.id_customer)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-warning hover:bg-warning/10 transition-colors">
                                    <svg class="w-4 h-4 mr-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    Password Customer
                                </Link>

                                <div class="my-1 border-t border-base-300/50"></div>

                                <button v-if="$can('customer', 'hapus')" @click="openDeleteModal(cust.id_customer); close()" class="flex items-center w-full px-4 py-2.5 text-sm font-bold text-error hover:bg-error/10 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Hapus Customer
                                </button>
                            </CustomTableAction>
                        </td>
                    </tr>

                    <tr v-if="customers.length === 0">
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-sm font-bold tracking-widest uppercase">Belum ada Master Customer</p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
