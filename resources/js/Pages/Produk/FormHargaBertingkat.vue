<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomTableForm from '@/Components/CustomTableForm.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomInputNumber from '@/Components/Form/CustomInputNumber.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    sku: Object,
});

const form = useForm({
    id_produk: props.sku.id_produk,
    hargas: props.sku.harga_bertingkat?.length > 0
        ? props.sku.harga_bertingkat.map(h => ({ min: h.min, max: h.max, tipe: h.tipe ?? 'nominal', nilai: h.nilai ?? 0 }))
        : [{ min: 1, max: 10, tipe: 'nominal', nilai: 0 }],
});

const addRow = () => {
    const lastMax = form.hargas[form.hargas.length - 1].max;
    form.hargas.push({ min: lastMax + 1, max: lastMax + 10, tipe: 'nominal', nilai: 0 });
};

const submit = () => {
    form.post(route('sku.syncHargaBertingkat', props.sku.id_sku));
};

const tipeOptions = [
    { label: 'Rupiah (Rp)', value: 'nominal' },
    { label: 'Persen (%)', value: 'persen' }
];
</script>

<template>
    <Head title="Atur Harga Grosir" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Harga Grosir: {{ sku.nama_sku }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 border rounded-lg shadow-xl bg-base-100 border-base-300">

                    <div class="p-4 mb-8 border border-info/20 bg-info/5 rounded-2xl">
                        <p class="text-[10px] font-black uppercase tracking-widest text-info opacity-70">Petunjuk Konfigurasi</p>
                        <p class="text-sm font-medium text-base-content/60">Tentukan rentang kuantitas (Min - Max) dan tipe penyesuaian harga (Nominal Rp atau Potongan Persen) untuk setiap level grosir.</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-8">

                        <CustomTableForm
                            v-model="form.hargas"
                            label="Tingkatan Harga"
                            :headers="['Min Qty', 'Max Qty', 'Tipe Penyesuaian', 'Nilai Harga/Potongan']"
                            @add="addRow"
                        >
                            <template #row="{ row, index }">
                                <td class="px-2 py-4 w-24">
                                    <input
                                        v-model.number="form.hargas[index].min"
                                        type="number"
                                        class="w-full text-sm font-bold text-center bg-transparent border-none focus:ring-0"
                                    />
                                </td>
                                <td class="px-2 py-4 border-l border-base-300/30 w-24">
                                    <input
                                        v-model.number="form.hargas[index].max"
                                        type="number"
                                        class="w-full text-sm font-bold text-center bg-transparent border-none focus:ring-0"
                                    />
                                </td>
                                <td class="px-2 py-4 border-l border-base-300/30 w-48">
                                    <CustomSelect
                                        v-model="form.hargas[index].tipe"
                                        :options="tipeOptions"
                                        label-key="label"
                                        value-key="value"
                                    />
                                </td>
                                <td class="px-4 py-4 border-l border-base-300/30 min-w-45">
                                    <CustomInputNumber
                                        v-model="form.hargas[index].nilai"
                                        :prefix="form.hargas[index].tipe === 'nominal' ? 'Rp' : ''"
                                        :suffix="form.hargas[index].tipe === 'persen' ? '%' : ''"
                                        :min="0"
                                        :max="form.hargas[index].tipe === 'persen' ? 100 : undefined"
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
                                Simpan Semua Tingkatan Harga
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
