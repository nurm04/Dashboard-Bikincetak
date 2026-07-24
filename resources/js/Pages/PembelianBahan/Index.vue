<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
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
    pembelian: Array,
    filters: Object
});

const headers = ['ID Pembelian', 'Tanggal', 'Supplier', 'Total Biaya', 'Staf', 'Aksi'];

const isDeleteModalOpen = ref(false);
const selectedId = ref(null);
const form = useForm({});

const search = ref(props.filters?.search || '');

watch(
    search,
    debounce((newSearch) => {
        router.get(route('pembelian-bahan.index'), {
            search: newSearch
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
                    <CustomButton v-if="$can('pembelian-bahan', 'tambah')" type="link" :href="route('pembelian-bahan.create')" variant="primary" class="shrink-0 rounded-xl">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </template>
                        Tambah Pembelian
                    </CustomButton>

                    <div class="flex flex-col w-full md:w-auto">
                        <CustomInputSearch
                            v-model="search"
                            class="w-full sm:w-80"
                            placeholder="Cari ID, Supplier, Nama Bahan..."
                        />
                    </div>
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
                            Rp {{ pb.total_biaya.toLocaleString('id-ID') }}
                        </td>

                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-md bg-base-200 border border-base-300 text-[10px] font-black uppercase tracking-wider text-base-content/60">
                                {{ pb.staf?.user?.name ?? '-' }}
                            </span>
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
                                <p class="text-sm font-bold tracking-widest uppercase">
                                    {{ search ? 'Pencarian Tidak Ditemukan' : 'Belum ada Transaksi' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
