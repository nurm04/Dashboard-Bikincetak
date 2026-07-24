<script setup>
import { ref, watch, computed } from 'vue';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomInputSearch from '@/Components/Form/CustomInputSearch.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';

// Manual Debounce (Tanpa lodash)
const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const props = defineProps({
    akuns: Array,
    enumKategori: Array,
    enumSaldo: Array,
    filters: Object
});

const headers = ['ID Akun', 'Nama Akun', 'Kategori', 'Saldo Normal', 'Aksi'];

const isDeleteModalOpen = ref(false);
const selectedId = ref(null);
const form = useForm({});

const search = ref(props.filters?.search || '');
const filterKategori = ref(props.filters?.kategori || 'semua');
const filterSaldo = ref(props.filters?.saldo_normal || 'semua');

watch(
    [search, filterKategori, filterSaldo],
    debounce(([newSearch, newKategori, newSaldo]) => {
        router.get(route('akun.index'), {
            search: newSearch,
            kategori: newKategori,
            saldo_normal: newSaldo
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300)
);

const kategoriOptions = computed(() => {
    let opts = [{ value: 'semua', label: 'SEMUA KATEGORI' }];
    if (props.enumKategori) {
        props.enumKategori.forEach(opt => {
            opts.push({ value: opt, label: opt.toUpperCase() });
        });
    }
    return opts;
});

const saldoOptions = computed(() => {
    let opts = [{ value: 'semua', label: 'SEMUA SALDO' }];
    if (props.enumSaldo) {
        props.enumSaldo.forEach(opt => {
            opts.push({ value: opt, label: opt.toUpperCase() });
        });
    }
    return opts;
});

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
        onError: () => alertStore.show('Ada kesalahan saat menghapus COA yang digunakan transaksi!', 'error')
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
                    <CustomButton v-if="$can('akun', 'tambah')" type="link" :href="route('akun.create')" variant="primary" class="px-6 py-2 rounded-xl shrink-0">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        </template>
                        Tambah COA
                    </CustomButton>
                </div>
                <div class="mb-3 flex flex-col w-full gap-3 sm:flex-row sm:items-center md:w-auto">
                    <CustomInputSearch
                        v-model="search"
                        class="w-full sm:w-64"
                        placeholder="Cari ID / Nama Akun..."
                    />
                    <div class="w-full sm:w-48">
                        <CustomSelect
                            v-model="filterKategori"
                            :options="kategoriOptions"
                            valueKey="value"
                            labelKey="label"
                            placeholder="Semua Kategori"
                        />
                    </div>
                    <div class="w-full sm:w-40">
                        <CustomSelect
                            v-model="filterSaldo"
                            :options="saldoOptions"
                            valueKey="value"
                            labelKey="label"
                            placeholder="Semua Saldo"
                        />
                    </div>
                </div>

                <CustomTable :headers="headers">
                    <tr v-for="item in akuns" :key="item.id_akun" class="transition-colors hover:bg-base-200/50">
                        <td class="px-6 py-4 font-mono text-xs font-bold text-primary">{{ item.id_akun }}</td>
                        <td class="px-6 py-4 font-bold text-base-content">{{ item.nama_akun }}</td>
                        <td>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-tight border"
                                :class="
                                    item.kategori === 'harta' ? 'bg-success/20 text-success border-success/30' :
                                    item.kategori === 'kewajiban' ? 'bg-error/20 text-error border-error/30' :
                                    item.kategori === 'modal' ? 'bg-warning/20 text-warning border-warning/30' :
                                    item.kategori === 'pendapatan' ? 'bg-info/20 text-info border-info/30' :
                                    'bg-base-300 text-base-content/70 border-base-content/20'
                                "
                            >
                                {{ item.kategori }}
                            </span>
                        </td>
                        <td class="font-bold uppercase text-[11px] tracking-wider" :class="item.saldo_normal === 'debit' ? 'text-primary' : 'text-error'">
                            {{ item.saldo_normal }}
                        </td>
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
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-sm font-bold tracking-widest uppercase">
                                    {{ search || filterKategori !== 'semua' || filterSaldo !== 'semua' ? 'Pencarian Tidak Ditemukan' : 'Belum ada Master COA' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
