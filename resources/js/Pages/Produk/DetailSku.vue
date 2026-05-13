<script setup>
import { ref } from 'vue';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomTable from '@/Components/CustomTable.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';
import CustomTableAction from '@/Components/CustomTableAction.vue';

const props = defineProps({
    produk: Object,
});

const headers = ['ID SKU', 'Nama SKU', 'Daftar Harga Grosir', 'Daftar Harga Pengerjaan', 'Daftar Diskon', 'Aksi'];

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

    form.delete(route('sku.destroy', selectedId.value), {
        onSuccess: () => {
            closeDeleteModal();
        },
        onError: () => alertStore.show('Gagal menghapus Sku!', 'error')
    });
};
</script>

<template>
    <CustomAlertConfirm
        :show="isDeleteModalOpen"
        type="error"
        title="Hapus Data Produk Sku"
        message="Menghapus Produk Sku akan menghapus seluruh harga grosir dan harga pengerjaan di dalamnya secara permanen. Lanjutkan? "
        confirmText="Ya, Hapus Semua"
        @close="isDeleteModalOpen = false"
        @confirm="doDelete"
    />

    <Head title="Detail Sku Produk" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Detail Sku Produk {{ props.produk.id_produk }}
            </h2>
        </template>

        <div class="min-h-screen px-4 py-3 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">

                <CustomTable :headers="headers">
                    <tr v-for="sku in props.produk.produk_sku" :key="sku.id_sku" class="transition-colors hover:bg-base-200/50">
                        <td class="px-6 py-4 font-mono text-xs font-bold text-primary">
                            {{ sku.id_sku }}
                        </td>
                        <td class="px-6 py-4 font-bold text-base-content">
                            {{ sku.nama_sku }}
                        </td>
                        <td class="px-6 py-4">
                            <div v-if="sku.harga_bertingkat?.length > 0" class="flex items-center gap-2">
                                <span class="font-black badge badge-primary badge-sm">{{ sku.harga_bertingkat.length }}</span>
                                <span class="text-[10px] font-bold uppercase opacity-50">Level Grosir</span>
                            </div>
                            <span v-else class="text-[10px] italic opacity-30">Belum diatur</span>
                        </td>

                        <td class="px-6 py-4">
                            <div v-if="sku.harga_pengerjaan?.length > 0" class="flex items-center gap-2">
                                <span class="font-black text-white badge badge-info badge-sm">{{ sku.harga_pengerjaan.length }}</span>
                                <span class="text-[10px] font-bold uppercase opacity-50">Opsi Estimasi</span>
                            </div>
                            <span v-else class="text-[10px] italic opacity-30">Belum diatur</span>
                        </td>

                        <td class="px-6 py-4">
                            <div v-if="sku.diskon_customer?.length > 0" class="flex items-center gap-2">
                                <span class="font-black text-white badge badge-warning badge-sm">{{ sku.diskon_customer.length }}</span>
                                <span class="text-[10px] font-bold uppercase opacity-50">Diskon Customer</span>
                            </div>
                            <span v-else class="text-[10px] italic opacity-30">Belum diatur</span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <CustomTableAction v-slot="{ close }">
                                <div class="px-4 py-2 text-[10px] font-black text-base-content/20 uppercase tracking-widest border-b border-base-300/50 mb-1">
                                    Menu Produk
                                </div>

                                <Link :href="route('sku.hargaBertingkat', sku.id_sku)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-primary hover:bg-primary/10 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                                    Harga Bertingkat
                                </Link>

                                <Link :href="route('sku.hargaPengerjaan', sku.id_sku)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-info hover:bg-info/10 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                                    Harga Pengerjaan
                                </Link>

                                <Link :href="route('sku.diskonCustomer', sku.id_sku)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-warning hover:bg-warning/10 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Diskon Member
                                </Link>

                                <div class="my-1 border-t border-base-300/50"></div>

                                <button @click="openDeleteModal(sku.id_sku); close()" class="flex items-center w-full px-4 py-2.5 text-sm font-bold text-error hover:bg-error/10 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Hapus Sku
                                </button>
                            </CustomTableAction>
                        </td>
                    </tr>

                    <tr v-if="props.produk.produk_sku.length === 0">
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-sm font-bold tracking-widest uppercase">Belum ada Sku di Produk {{ props.produk.id_produk }}</p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
