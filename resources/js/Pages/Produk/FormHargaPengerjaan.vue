<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomTableForm from '@/Components/CustomTableForm.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomInputNumber from '@/Components/CustomInputNumber.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    sku: Object,
});

const form = useForm({
    id_produk: props.sku.id_produk,
    pengerjaans: props.sku.harga_pengerjaan?.length > 0
        ? props.sku.harga_pengerjaan.map(p => ({ pengerjaan: p.pengerjaan, harga: p.harga }))
        : [{ pengerjaan: '1 Hari (Express)', harga: 0 }],
});

const addRow = () => {
    form.pengerjaans.push({ pengerjaan: '', harga: 0 });
};

const submit = () => {
    form.post(route('sku.syncHargaPengerjaan', props.sku.id_sku));
};
</script>

<template>
    <Head title="Atur Estimasi Pengerjaan" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Estimasi Pengerjaan: {{ sku.nama_sku }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 border rounded-lg shadow-xl bg-base-100 border-base-300">

                    <div class="p-4 mb-8 border border-primary/20 bg-primary/5 rounded-2xl">
                        <p class="text-[10px] font-black uppercase tracking-widest text-primary opacity-70">Manajemen Service Level</p>
                        <p class="text-sm font-medium text-base-content/60">Tambahkan opsi durasi pengerjaan. Contoh: "Selesai 3 Jam", "1 Hari Kerja", atau "Reguler (3-5 Hari)".</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-8">

                        <CustomTableForm
                            v-model="form.pengerjaans"
                            label="Daftar Opsi Pengerjaan"
                            :headers="['Estimasi Waktu / Nama Layanan', 'Tambahan Biaya (Rp)']"
                            @add="addRow"
                        >
                            <template #row="{ row, index }">
                                <td class="px-4 py-4">
                                    <input
                                        v-model="form.pengerjaans[index].pengerjaan"
                                        type="text"
                                        placeholder="Contoh: 1 Hari Selesai"
                                        class="w-full text-sm font-bold bg-transparent border-none focus:ring-0 text-base-content"
                                    />
                                </td>
                                <td class="px-4 py-4 border-l border-base-300/30 min-w-50">
                                    <CustomInputNumber
                                        v-model="form.pengerjaans[index].harga"
                                        prefix="Rp"
                                        :min="0"
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
