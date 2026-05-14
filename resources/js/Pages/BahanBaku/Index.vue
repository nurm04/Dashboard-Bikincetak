<script setup>
import { ref } from 'vue';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';

const props = defineProps({
    bahan_baku: Array,
});

const headers = ['ID / Bahan', 'Satuan', 'Harga Beli', 'Stok (Awal / Skg)', 'Status', 'Aksi'];

const isDeleteModalOpen = ref(false);
const selectedId = ref(null);
const formDelete = useForm({});

const openDeleteModal = (id) => {
    selectedId.value = id;
    isDeleteModalOpen.value = true;
};

const doDelete = () => {
    if (!selectedId.value) return;
    formDelete.delete(route('bahan-baku.destroy', selectedId.value), {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            alertStore.show('Bahan baku berhasil dihapus!', 'success');
        },
    });
};
</script>

<template>
    <CustomAlertConfirm
        :show="isDeleteModalOpen"
        type="error"
        title="Hapus Bahan Baku"
        message="Menghapus bahan baku mungkin berdampak pada kalkulasi HPP produk yang menggunakan bahan ini. Lanjutkan?"
        confirmText="Ya, Hapus"
        @close="isDeleteModalOpen = false"
        @confirm="doDelete"
    />

    <Head title="Master Bahan Baku" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Master Bahan Baku
            </h2>
        </template>

        <div class="min-h-screen px-4 py-3 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="flex flex-col gap-4 mb-8 md:flex-row md:items-center md:justify-between">
                    <CustomButton type="link" :href="route('bahan-baku.create')" variant="primary">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </template>
                        Tambah Bahan Baku
                    </CustomButton>
                </div>

                <CustomTable :headers="headers">
                    <tr v-for="bahan in bahan_baku" :key="bahan.id_bahan_baku" class="transition-colors hover:bg-base-200/50">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-base-content uppercase tracking-tight">{{ bahan.nama_bahan_baku }}</span>
                                <span class="font-mono text-[10px] font-black text-primary">{{ bahan.id_bahan_baku }}</span>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-md bg-base-200 border border-base-300 text-[10px] font-black uppercase tracking-wider text-base-content/60">
                                {{ bahan.satuan }}
                            </span>
                        </td>

                        <td class="px-6 py-4 font-bold text-success italic tracking-tighter">
                            Rp {{ bahan.harga_beli.toLocaleString() }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 font-black text-xs italic">
                                <span class="opacity-30">{{ bahan.stok_awal }}</span>
                                <span class="text-primary tracking-tighter">→ {{ bahan.stok_sekarang }}</span>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div v-if="bahan.is_active" class="text-[9px] font-black uppercase text-success flex items-center gap-1">
                                <div class="w-1.5 h-1.5 rounded-full bg-success"></div> AKTIF
                            </div>
                            <div v-else class="text-[9px] font-black uppercase text-error flex items-center gap-1 opacity-50">
                                <div class="w-1.5 h-1.5 rounded-full bg-error"></div> NON-AKTIF
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-center space-x-2">
                                <CustomButton type="link" :href="route('bahan-baku.edit', bahan.id_bahan_baku)" variant="info" size="sm">Edit</CustomButton>
                                <CustomButton @click="openDeleteModal(bahan.id_bahan_baku)" variant="error" size="sm">Hapus</CustomButton>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="bahan_baku.length === 0">
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-sm font-bold tracking-widest uppercase">Data Bahan Kosong</p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
