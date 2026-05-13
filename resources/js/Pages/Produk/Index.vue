<script setup>
import { ref } from 'vue';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomTable from '@/Components/CustomTable.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';
import Dropdown from '@/Components/Dropdown.vue';
import CustomTableAction from '@/Components/CustomTableAction.vue';

const props = defineProps({ produks: Array });
const headers = ['ID Produk', 'Produk / Kategori', 'Varian Aktif', 'Total SKU', 'Aksi'];

const isDeleteModalOpen = ref(false);
const selectedId = ref(null);
const form = useForm({});

const openDeleteModal = (id) => {
    selectedId.value = id;
    isDeleteModalOpen.value = true;
};

const doDelete = () => {
    form.delete(route('produk.destroy', selectedId.value), {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            alertStore.show('Produk berhasil dihapus!', 'success');
        }
    });
};
</script>

<template>
    <CustomAlertConfirm
        :show="isDeleteModalOpen"
        type="error"
        title="Hapus Produk"
        message="Semua data varian produk dan SKU terkait akan ikut terhapus permanen."
        confirmText="Ya, Hapus Semua"
        @close="isDeleteModalOpen = false"
        @confirm="doDelete"
    />
    <Head title="Manajemen Produk" />
    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">Database Produk</h2>
        </template>

        <div class="min-h-screen px-4 py-12 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="flex items-center justify-between mb-8">
                    <CustomButton type="link" :href="route('produk.create')" variant="primary">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        </template>
                        Produk Baru
                    </CustomButton>
                </div>

                <CustomTable :headers="headers">
                    <tr v-for="prd in produks" :key="prd.id_produk" class="transition-colors hover:bg-base-200/50">
                        <td class="px-6 py-4 font-mono text-xs font-bold text-primary">{{ prd.id_produk }}</td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-base-content">{{ prd.nama_produk }}</div>
                            <div class="text-[10px] text-base-content/50 font-medium">{{ prd.kategori?.nama_kategori }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                <span v-for="v in prd.varians" :key="v.id_varian" class="px-2 py-0.5 rounded-md bg-success text-[10px] font-bold uppercase tracking-tight text-base-content/70 border border-base-300">
                                    {{ v.nama_varian }}
                                </span>
                                <span v-if="!prd.varians.length" class="text-[10px] italic opacity-30">Belum ada varian</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold">{{ prd.produk_sku?.length || 0 }} SKU</td>
                        <td class="px-6 py-4 text-center">
                            <CustomTableAction v-slot="{ close }">
                                <div class="px-4 py-2 text-[10px] font-black text-base-content/20 uppercase tracking-widest border-b border-base-300/50 mb-1">
                                    Menu Produk
                                </div>

                                <Link :href="route('produk.edit', prd.id_produk)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-base-content hover:bg-base-200 transition-colors">
                                    <svg class="w-4 h-4 mr-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    Edit Produk
                                </Link>

                                <Link :href="route('produk.varian', prd.id_produk)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-info hover:bg-info/10 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                                    Atur Varian
                                </Link>

                                <Link
                                    :href="prd.varians.length > 0 ? route('produk.sku', prd.id_produk) : '#'"
                                    @click="prd.varians.length > 0 ? close() : $event.preventDefault()"
                                    :class="{
                                        'opacity-50 pointer-events-none grayscale cursor-not-allowed': prd.varians.length === 0,
                                        'text-success hover:bg-success/10': prd.varians.length > 0
                                    }"
                                    class="flex items-center px-4 py-2.5 text-sm font-bold transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                    Generate SKU
                                </Link>

                                <Link
                                    :href="prd.produk_sku.length > 0 ? route('produk.detailSku', prd.id_produk) : '#'"
                                    @click="prd.produk_sku.length > 0 ? close() : $event.preventDefault()"
                                    :class="{
                                        'opacity-50 pointer-events-none grayscale cursor-not-allowed': prd.produk_sku.length === 0,
                                        'text-primary hover:bg-primary/10': prd.produk_sku.length > 0
                                    }"
                                    class="flex items-center px-4 py-2.5 text-sm font-bold transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    Detail SKU
                                </Link>

                                <div class="my-1 border-t border-base-300/50"></div>

                                <button @click="openDeleteModal(prd.id_produk); close()" class="flex items-center w-full px-4 py-2.5 text-sm font-bold text-error hover:bg-error/10 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Hapus Produk
                                </button>
                            </CustomTableAction>
                        </td>
                    </tr>
                    <tr v-if="produks.length === 0">
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-sm font-bold tracking-widest uppercase">Belum ada Master Produk</p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
