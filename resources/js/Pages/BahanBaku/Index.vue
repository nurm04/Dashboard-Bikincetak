<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';
import CustomInputSearch from '@/Components/CustomInputSearch.vue';
import CustomSelect from '@/Components/CustomSelect.vue';
import { alertStore } from '@/Utils/alertStore';

const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const props = defineProps({
    bahan_baku: Array,
    filters: Object
});

const headers = ['ID / Bahan', 'Satuan', 'Berat (Gram)', 'Harga Beli', 'Stok (Awal / Skg)', 'Status', 'Aksi'];

const isDeleteModalOpen = ref(false);
const selectedId = ref(null);
const formDelete = useForm({});

const search = ref(props.filters?.search || '');
const filterActive = ref(props.filters?.is_active ?? 'semua');

const activeOptions = [
    { value: 'semua', label: 'SEMUA STATUS' },
    { value: '1', label: 'AKTIF' },
    { value: '0', label: 'NON-AKTIF' },
];

watch(
    [search, filterActive],
    debounce(([newSearch, newActive]) => {
        router.get(route('bahan-baku.index'), {
            search: newSearch,
            is_active: newActive
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
                    <CustomButton type="link" :href="route('bahan-baku.create')" variant="primary" class="shrink-0 rounded-xl">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </template>
                        Tambah Bahan Baku
                    </CustomButton>
                </div>
                <div class="mb-3 flex flex-col w-full gap-3 sm:flex-row sm:items-center md:w-auto">
                    <CustomInputSearch
                        v-model="search"
                        class="w-full sm:w-64"
                        placeholder="Cari ID / Nama / Harga..."
                    />
                    <div class="w-full sm:w-48">
                        <CustomSelect
                            v-model="filterActive"
                            :options="activeOptions"
                            valueKey="value"
                            labelKey="label"
                            placeholder="Semua Status"
                        />
                    </div>
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

                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-md bg-base-200 border border-base-300 text-[10px] font-black uppercase tracking-wider text-base-content/60">
                                {{ bahan.berat_gram_persatuan }}
                            </span>
                        </td>

                        <td class="px-6 py-4 font-bold text-success italic tracking-tighter">
                            Rp {{ bahan.harga_beli.toLocaleString('id-ID') }}
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
                        <td colspan="7" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-sm font-bold tracking-widest uppercase">
                                    {{ search || filterActive !== 'semua' ? 'Pencarian Tidak Ditemukan' : 'Data Bahan Kosong' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
