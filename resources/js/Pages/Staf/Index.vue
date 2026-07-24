<script setup>
import { ref, watch, computed } from 'vue';
import { alertStore } from '@/Utils/alertStore';
import { Head, useForm, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
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
    stafs: Array,
    roles: Array,
    filters: Object
});

const headers = ['ID Staf', 'Nama / Email', 'No. WhatsApp', 'Role', 'Aksi'];

const isDeleteModalOpen = ref(false);
const selectedId = ref(null);
const form = useForm({});

const search = ref(props.filters?.search || '');
const filterRole = ref(props.filters?.role_id || 'semua');

watch(
    [search, filterRole],
    debounce(([newSearch, newRole]) => {
        router.get(route('staf.index'), {
            search: newSearch,
            role_id: newRole
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300)
);

const roleOptions = computed(() => {
    let opts = [{ value: 'semua', label: 'SEMUA ROLE' }];
    if (props.roles) {
        props.roles.forEach(r => {
            opts.push({ value: r.id_role_staf, label: r.role.toUpperCase() });
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

    form.delete(route('staf.destroy', selectedId.value), {
        onSuccess: () => {
            closeDeleteModal();
            alertStore.show('Data berhasil dihapus!', 'success');
        },
        onError: () => alertStore.show('Ada kesalahan saat menghapus staf!', 'error')
    });
};
</script>

<template>
    <CustomAlertConfirm
        :show="isDeleteModalOpen"
        type="error"
        title="Hapus Staf"
        message="Apakah Anda yakin ingin menghapus Staf ini? Data yang dihapus tidak dapat dikembalikan."
        confirmText="Ya, Hapus"
        @close="closeDeleteModal"
        @confirm="doDelete"
    />
    <Head title="Data Staf" />
    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Database Staf
            </h2>
        </template>
        <div class="min-h-screen px-4 py-3 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="flex flex-col gap-4 mb-8 md:flex-row md:items-center md:justify-between">
                    <CustomButton v-if="$can('staf', 'tambah')" type="link" :href="route('staf.create')" variant="primary" size="md" class="shrink-0 rounded-xl">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        </template>
                        Registrasi Staf
                    </CustomButton>
                </div>
                <div class="mb-3 flex flex-col w-full gap-3 sm:flex-row sm:items-center md:w-auto">
                    <CustomInputSearch
                        v-model="search"
                        class="w-full sm:w-64"
                        placeholder="Cari Nama / Email / No. HP..."
                    />

                    <div class="w-full sm:w-48 md:w-56">
                        <CustomSelect
                            v-model="filterRole"
                            :options="roleOptions"
                            valueKey="value"
                            labelKey="label"
                            placeholder="Semua Role"
                        />
                    </div>
                </div>

                <CustomTable :headers="headers">
                    <tr v-for="staf in stafs" :key="staf.id_staf" class="transition-colors hover:bg-base-200/50">
                        <td class="px-6 py-4 font-mono text-xs font-bold text-primary">{{ staf.id_staf }}</td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-base-content">{{ staf.user?.name }}</div>
                            <div class="text-[10px] text-base-content/50 font-medium">{{ staf.user?.email }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-base-content/70">{{ staf.no_hp }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-tight border"
                                :class="
                                    staf.role_staf?.role === 'Admin' ? 'bg-primary/20 text-primary border-primary/30' :
                                    staf.role_staf?.role === 'Kasir' ? 'bg-info/20 text-info border-info/30' :
                                    'bg-success/20 text-success border-success/30'
                                "
                            >
                                {{ staf.role_staf?.role }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center space-x-2">
                                <CustomButton v-if="$can('staf', 'ubah')" type="link" :href="route('staf.edit', staf.id_staf)" variant="info" size="sm">
                                    Edit
                                </CustomButton>
                                <CustomButton v-if="$can('staf', 'hapus')" @click="openDeleteModal(staf.id_staf)" variant="error" size="sm">
                                    Hapus
                                </CustomButton>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="stafs.length === 0">
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-sm font-bold tracking-widest uppercase">
                                    {{ search || filterRole !== 'semua' ? 'Pencarian Tidak Ditemukan' : 'Belum ada Master Staf' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
