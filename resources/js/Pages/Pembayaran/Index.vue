<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomInputSearch from '@/Components/CustomInputSearch.vue';

const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const props = defineProps({
    pembayaran: Array,
    filters: Object
});

const headers = ['ID Pembayaran', 'Pelanggan', 'Total Tagihan', 'Total Transfer', 'Staf', 'Aksi'];

const search = ref(props.filters?.search || '');

watch(
    search,
    debounce((newSearch) => {
        router.get(route('pembayaran.index'), {
            search: newSearch
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300)
);

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka);
};
</script>

<template>
    <Head title="Data Pembayaran" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Data Pembayaran
            </h2>
        </template>

        <div class="min-h-screen px-4 py-3 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">

                <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-end">
                    <CustomInputSearch
                        v-model="search"
                        class="w-full sm:w-80"
                        placeholder="Cari ID Pembayaran, Pesanan, atau Nama..."
                    />
                </div>

                <CustomTable :headers="headers">
                    <tr v-for="item in pembayaran" :key="item.id_pembayaran" class="transition-colors hover:bg-base-200/50">
                        <td class="px-6 py-4 font-mono text-xs font-bold text-primary">
                            {{ item.id_pembayaran }}
                            <div v-if="item.pesan" class="text-[10px] text-base-content/50 mt-1 uppercase tracking-wider">
                                {{ item.pesan.id_pesan }}
                            </div>
                        </td>

                        <td class="px-6 py-4 font-bold text-base-content">
                            {{ item.pesan?.customer?.user?.name || '-' }}
                            <div class="text-[10px] text-base-content/50 mt-1 font-mono tracking-wider">
                                {{ item.pesan?.customer?.id_customer || '' }}
                            </div>
                        </td>

                        <td class="px-6 py-4 text-base-content/80">
                            {{ formatRupiah(item.total_tagihan) }}
                        </td>

                        <td class="px-6 py-4 font-bold text-success">
                            {{ formatRupiah(item.nominal_bayar) }}
                        </td>

                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-md bg-base-200 border border-base-300 text-[10px] font-black uppercase tracking-wider text-base-content/60">
                                {{ item.staf?.user?.name || '-' }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <CustomButton type="link" :href="route('pembayaran.detail', item.id_pembayaran)" variant="info" size="sm">
                                Detail
                            </CustomButton>
                        </td>
                    </tr>

                    <tr v-if="pembayaran.length === 0">
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-sm font-bold tracking-widest uppercase">
                                    {{ search ? 'Pencarian Tidak Ditemukan' : 'Belum ada Data Pembayaran' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
