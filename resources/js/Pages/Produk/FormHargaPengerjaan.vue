<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomTableForm from '@/Components/CustomTableForm.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomInputNumber from '@/Components/Form/CustomInputNumber.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    sku: Object,
});

const form = useForm({
    id_produk: props.sku.id_produk,
    pengerjaans: props.sku.harga_pengerjaan?.length > 0
        ? props.sku.harga_pengerjaan.map(p => ({ pengerjaan: p.pengerjaan, tipe: p.tipe ?? 'nominal', nilai: p.nilai ?? 0 }))
        : [{ pengerjaan: '1 Hari', tipe: 'nominal', nilai: 0 }],
});

const addRow = () => {
    form.pengerjaans.push({ pengerjaan: '', tipe: 'nominal', nilai: 0 });
};

const submit = () => {
    form.post(route('sku.syncHargaPengerjaan', props.sku.id_sku));
};

const tipeOptions = [
    { label: 'Rupiah (Rp)', value: 'nominal' },
    { label: 'Persen (%)', value: 'persen' }
];
</script>

<template>
    <Head title="Atur Estimasi Pengerjaan" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('produk.detailSku', sku.id_produk)" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Estimasi Pengerjaan: {{ sku.nama_sku }}
                    </h2>
                </div>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 border rounded-lg shadow-xl bg-base-100 border-base-300">

                    <div class="p-4 mb-8 border border-primary/20 bg-primary/5 rounded-2xl">
                        <p class="text-[10px] font-black uppercase tracking-widest text-primary opacity-70">Manajemen Service Level</p>
                        <p class="text-sm font-medium text-base-content/60">Tambahkan opsi durasi pengerjaan. Pilih apakah biaya tambahannya berupa Nominal Pasti (Rp) atau Persentase Tambahan (%).</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-8">

                        <CustomTableForm
                            v-model="form.pengerjaans"
                            label="Daftar Opsi Pengerjaan"
                            :headers="['Estimasi Waktu / Nama Layanan', 'Tipe Biaya', 'Nilai Biaya']"
                            @add="addRow"
                        >
                            <template #row="{ row, index }">
                                <td class="px-4 py-4 w-1/2">
                                    <input
                                        v-model="form.pengerjaans[index].pengerjaan"
                                        type="text"
                                        placeholder="Contoh: 1 Hari Selesai"
                                        class="w-full text-sm font-bold bg-transparent border-none focus:ring-0 text-base-content"
                                    />
                                </td>
                                <td class="px-2 py-4 border-l border-base-300/30 w-48">
                                    <CustomSelect
                                        v-model="form.pengerjaans[index].tipe"
                                        :options="tipeOptions"
                                        label-key="label"
                                        value-key="value"
                                    />
                                </td>
                                <td class="px-4 py-4 border-l border-base-300/30 min-w-45">
                                    <CustomInputNumber
                                        v-model="form.pengerjaans[index].nilai"
                                        :prefix="form.pengerjaans[index].tipe === 'nominal' ? 'Rp' : ''"
                                        :suffix="form.pengerjaans[index].tipe === 'persen' ? '%' : ''"
                                        :min="0"
                                        :max="form.pengerjaans[index].tipe === 'persen' ? 100 : undefined"
                                    />
                                </td>
                            </template>
                        </CustomTableForm>

                        <div class="flex flex-col items-center gap-4 pt-8 border-t border-base-300 sm:flex-row">
                            <CustomButton
                                type="submit"
                                variant="primary"
                                class="flex-1 w-full py-4 sm:w-auto rounded-2xl"
                                :disabled="form.processing"
                            >
                                Simpan Opsi Pengerjaan
                            </CustomButton>

                            <CustomButton
                                type="link"
                                :href="route('produk.detailSku', sku.id_produk)"
                                variant="secondary"
                                class="w-full py-4 sm:w-auto rounded-2xl"
                            >
                                Batal
                            </CustomButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
