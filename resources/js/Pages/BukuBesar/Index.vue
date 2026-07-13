<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/CustomInput.vue';
import CustomButton from '@/Components/CustomButton.vue';

const props = defineProps({
    bukuBesar: Array,
    filters: Object,
});

const filterForm = ref({
    start_month: props.filters?.start_month || '',
    end_month: props.filters?.end_month || '',
});

const applyFilter = () => {
    router.get(route('buku-besar.detail'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(angka || 0);
};

const formatTanggal = (tgl) => {
    if (!tgl) return '-';
    const d = new Date(tgl);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '-');
};

const totalDebit = computed(() => props.bukuBesar.reduce((sum, item) => sum + Number(item.debit), 0));
const totalKredit = computed(() => props.bukuBesar.reduce((sum, item) => sum + Number(item.kredit), 0));
</script>

<template>
    <Head title="General Ledger" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold leading-tight text-base-content">
                    Buku Besar (General Ledger)
                </h2>
                <a :href="`/dashboard?start_month=${filterForm.start_month}&end_month=${filterForm.end_month}`" class="font-bold btn btn-sm btn-ghost">
                    ⬅ Kembali
                </a>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-[95%] sm:px-6 lg:px-8">

                <div class="overflow-hidden border shadow-sm bg-base-100 sm:rounded-lg border-base-300">
                    <div class="p-4 md:p-6">

                        <div class="flex flex-col justify-between gap-4 pb-4 mb-6 border-b md:flex-row md:items-center border-base-200">
                            <h3 class="text-sm font-black tracking-widest uppercase opacity-50">Laporan Transaksi Jurnal</h3>

                            <div class="flex flex-wrap items-end gap-2">
                                 <div class="w-40">
                                    <CustomInput label="Mulai Bulan" type="month" v-model="filterForm.start_month"/>
                                </div>

                                <div class="w-40">
                                    <CustomInput label="Sampai Bulan" type="month" v-model="filterForm.end_month"/>
                                </div>

                                <CustomButton @click="applyFilter" size="sm">
                                    Terapkan
                                </CustomButton>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="table w-full border-collapse table-xs table-pin-rows">
                                <thead class="bg-base-200/50 text-base-content">
                                    <tr class="uppercase text-[10px] tracking-wider border-y border-base-300">
                                        <th class="py-3 font-black">Tanggal</th>
                                        <th class="py-3 font-black">Akun (COA)</th>
                                        <th class="py-3 font-black text-right">Debit</th>
                                        <th class="py-3 font-black text-right border-r border-base-300">Kredit</th>
                                        <th class="py-3 pl-4 font-black">Voucher Type</th>
                                        <th class="py-3 font-black">No. Referensi</th>
                                        <th class="py-3 font-black">Keterangan</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-if="bukuBesar.length === 0">
                                        <td colspan="7" class="py-10 font-bold text-center opacity-50">
                                            Tidak ada transaksi di rentang bulan ini.
                                        </td>
                                    </tr>

                                    <tr v-for="(item, index) in bukuBesar" :key="item.id_buku_besar" class="hover border-b border-base-200 text-[11px]">
                                        <td class="font-mono opacity-70 whitespace-nowrap">{{ formatTanggal(item.tanggal_transaksi) }}</td>
                                        <td class="font-semibold text-primary whitespace-nowrap">{{ item.nama_akun }}</td>
                                        <td class="font-mono text-right whitespace-nowrap" :class="item.debit > 0 ? 'text-base-content' : 'opacity-30'">
                                            {{ formatRupiah(item.debit) }}
                                        </td>
                                        <td class="font-mono text-right border-r whitespace-nowrap border-base-300" :class="item.kredit > 0 ? 'text-base-content' : 'opacity-30'">
                                            {{ formatRupiah(item.kredit) }}
                                        </td>
                                        <td class="pl-4 uppercase opacity-70 whitespace-nowrap text-[10px] font-bold">{{ item.tipe_referensi }}</td>
                                        <td class="font-mono text-[10px] font-bold opacity-80 whitespace-nowrap">{{ item.id_referensi }}</td>
                                        <td class="min-w-50">{{ item.keterangan }}</td>
                                    </tr>
                                </tbody>

                                <tfoot>
                                    <tr class="text-xs font-black border-t-2 bg-base-200/50 border-base-300">
                                        <td colspan="2" class="py-3 pr-4 tracking-wider text-right uppercase">Total Mutasi</td>
                                        <td class="font-mono text-right text-success">{{ formatRupiah(totalDebit) }}</td>
                                        <td class="font-mono text-right border-r text-error border-base-300">{{ formatRupiah(totalKredit) }}</td>
                                        <td colspan="3">
                                            <span v-if="totalDebit === totalKredit" class="ml-2 font-black text-white uppercase badge badge-success badge-sm">✅ Balance</span>
                                            <span v-else class="ml-2 font-black text-white uppercase badge badge-error badge-sm">❌ Unbalanced</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </StafLayout>
</template>

<style scoped>
table td {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}
</style>
