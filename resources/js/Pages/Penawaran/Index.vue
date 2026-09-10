<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomInputSearch from '@/Components/Form/CustomInputSearch.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import CustomTableAction from '@/Components/CustomTableAction.vue';
import { CheckCircle2 } from 'lucide-vue-next';

const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const props = defineProps({
    penawaran: Object,
    filters: Object
});

const headers = ['ID Penawaran', 'Kode Penawaran', 'Pelanggan', 'Tgl Dibuat', 'Estimasi Total', 'Status', 'Aksi'];

const search = ref(props.filters?.search || '');
const filterStatus = ref(props.filters?.status || 'semua');

const statusOptions = [
    { value: 'semua', label: 'SEMUA STATUS' },
    { value: 'draft', label: 'DRAFT' },
    { value: 'terkirim', label: 'TERKIRIM' },
    { value: 'disetujui', label: 'DISETUJUI (ACC)' },
    { value: 'ditolak', label: 'DITOLAK' },
    { value: 'kedaluwarsa', label: 'KEDALUWARSA' }
];

watch(
    [search, filterStatus],
    debounce(([newSearch, newStatus]) => {
        router.get(route('penawaran.index'), {
            search: newSearch,
            status: newStatus === 'semua' ? '' : newStatus
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300)
);

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka || 0);
};

// Hitung Estimasi Total (Frontend)
const hitungEstimasi = (items) => {
    if (!items || items.length === 0) return 0;

    let total = 0;
    items.forEach(item => {
        let hargaAwal = Number(item.harga_satuan_snapshot) || 0;
        let qty = Number(item.jumlah) || 1;

        let subtotal = hargaAwal * qty;

        if (item.penawaran_item_finishing) {
            item.penawaran_item_finishing.forEach(fin => {
                subtotal += Number(fin.harga_finishing_snapshot) || 0;
            });
        }
        total += subtotal;
    });

    return total;
};

// Utility Badge Status
const getStatusBadge = (statusPnw) => {
    switch (statusPnw) {
        case 'draft':
            return 'bg-base-200 text-base-content border-base-300';
        case 'terkirim':
            return 'bg-info/10 text-info border-info/30';
        case 'disetujui':
            return 'bg-success/10 text-success border-success/30';
        case 'ditolak':
            return 'bg-error/10 text-error border-error/30';
        case 'kedaluwarsa':
            return 'bg-warning/10 text-warning border-warning/30';
        default:
            return 'bg-base-200 text-base-content';
    }
};

const formatEnum = (text) => {
    if (!text) return '';
    return text.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};
</script>

<template>
    <Head title="Manajemen Penawaran" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Daftar Surat Penawaran
            </h2>
        </template>

        <div class="min-h-screen px-4 py-6 mx-auto sm:px-6 lg:px-8 max-w-7xl">

            <!-- Filter & Action Section -->
            <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between">
                <div class="w-full shrink-0 md:w-auto">
                    <CustomButton v-if="$can('penawaran', 'tambah')" type="link" :href="route('penawaran.create')" variant="primary" block>
                        <template #icon>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M12 3.75a.75.75 0 01.75.75v6.75h6.75a.75.75 0 010 1.5h-6.75v6.75a.75.75 0 01-1.5 0v-6.75H4.5a.75.75 0 010-1.5h6.75V4.5a.75.75 0 01.75-.75z" clip-rule="evenodd" />
                            </svg>
                        </template>
                        Buat Penawaran
                    </CustomButton>
                </div>

                <div class="flex flex-col w-full gap-3 md:flex-row md:items-center md:w-auto">
                    <div class="w-full md:w-64 lg:w-72">
                        <CustomInputSearch
                            v-model="search"
                            placeholder="Cari ID / Nama Customer..."
                            class="w-full"
                        />
                    </div>
                    <div class="w-full md:w-48 lg:w-56">
                        <CustomSelect
                            v-model="filterStatus"
                            :options="statusOptions"
                            valueKey="value"
                            labelKey="label"
                            placeholder="Semua Status"
                        />
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <CustomTable :headers="headers" :pagination="penawaran">
                <tr
                    v-for="item in penawaran.data"
                    :key="item.id_penawaran"
                    class="transition-colors hover:bg-base-200/50"
                >
                    <td class="px-4 py-4 font-mono text-xs font-bold whitespace-nowrap">
                        <p class="text-primary">{{ item.id_penawaran }}</p>
                        <p v-if="item.id_pesan_terkait" class="text-[10px] mt-1 text-success font-bold flex items-center gap-1">
                            <CheckCircle2 class="w-3 h-3" /> SO: {{ item.id_pesan_terkait }}
                        </p>
                    </td>

                    <td class="px-4 py-4 whitespace-nowrap text-primary">
                        {{ item.kode_penawaran }}
                    </td>

                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="font-bold text-base-content">{{ item.customer?.user?.name || 'Pelanggan Terhapus' }}</div>
                        <div class="text-[10px] opacity-40 font-mono tracking-wider">{{ item.customer?.no_hp || '-' }}</div>
                    </td>

                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold">{{ new Date(item.tanggal_penawaran).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}</div>
                        <div v-if="item.berlaku_sampai" class="text-[10px] font-bold mt-1 text-error">
                            Batas: {{ new Date(item.berlaku_sampai).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                        </div>
                    </td>

                    <td class="px-4 py-4 text-sm font-black whitespace-nowrap text-base-content">
                        {{ formatRupiah(hitungEstimasi(item.penawaran_item)) }}
                    </td>

                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="inline-flex items-center px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-xl shadow-sm border" :class="getStatusBadge(item.status_penawaran)">
                            {{ formatEnum(item.status_penawaran) }}
                        </div>
                    </td>

                    <td class="px-4 py-4 text-center whitespace-nowrap">
                        <CustomTableAction v-slot="{ close }">
                            <div class="px-4 py-2 text-[10px] font-black text-base-content/40 uppercase tracking-widest border-b border-base-300/50 mb-1">
                                Menu Penawaran
                            </div>

                            <Link :href="route('penawaran.detail', item.id_penawaran)" @click="close" class="flex items-center px-4 py-2.5 text-sm font-bold text-base-content hover:bg-base-200 transition-colors">
                                <svg class="w-4 h-4 mr-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Detail Penawaran
                            </Link>
                        </CustomTableAction>
                    </td>
                </tr>

                <tr v-if="penawaran.data.length === 0">
                    <td colspan="6" class="px-6 py-20 text-center">
                        <div class="flex flex-col items-center gap-2 opacity-30">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <p class="text-xs font-black tracking-widest uppercase">
                                {{ search || filterStatus !== 'semua' ? 'Pencarian Tidak Ditemukan' : 'Belum ada Surat Penawaran' }}
                            </p>
                        </div>
                    </td>
                </tr>
            </CustomTable>

            <!-- Pagination -->
            <div v-if="penawaran.links && penawaran.links.length > 3" class="flex justify-center mt-6">
                <div class="shadow-sm join border-base-300">
                    <Link
                        v-for="(link, k) in penawaran.links"
                        :key="k"
                        :href="link.url || '#'"
                        class="join-item btn btn-sm"
                        :class="{
                            'btn-active btn-primary': link.active,
                            'btn-disabled': !link.url
                        }"
                        v-html="link.label"
                    />
                </div>
            </div>

        </div>
    </StafLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
