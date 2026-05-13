<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomTableForm from '@/Components/CustomTableForm.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomInputNumber from '@/Components/CustomInputNumber.vue';
import CustomSelect from '@/Components/CustomSelect.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    sku: Object,
    roles: Array,
});

const form = useForm({
    id_produk: props.sku.id_produk,
    diskon: props.roles.map(role => {
        const existingDiskon = props.sku.diskon_customer?.find(
            d => d.id_role_customer === role.id_role_customer
        );

        return {
            id_role_customer: role.id_role_customer,
            nama_role: role.role,
            tipe: existingDiskon?.tipe ?? 'persen',
            nilai: existingDiskon?.nilai ?? 0
        };
    }),
});

const submit = () => {
    form.post(route('sku.syncdiskonCustomer', props.sku.id_sku));
};

const tipeOptions = [
    { label: 'Persen (%)', value: 'persen' },
    { label: 'Rupiah (Rp)', value: 'nominal' }
];
</script>

<template>
    <Head title="Atur Diskon Member" />
    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight italic uppercase tracking-tighter">
                Diskon Member: {{ sku.nama_sku }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 border rounded-lg shadow-xl bg-base-100 border-base-300">

                    <form @submit.prevent="submit" class="space-y-8">
                        <CustomTableForm
                            v-model="form.diskon"
                            label="Konfigurasi Diskon Role"
                            :headers="['Role Customer', 'Tipe Potongan', 'Nilai Diskon']"
                            :can-add="false"
                            :can-delete="false"
                        >
                            <template #row="{ row, index }">
                                <td class="px-4 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black uppercase tracking-widest text-primary">
                                            {{ row.nama_role }}
                                        </span>
                                        <span class="text-[9px] font-bold opacity-40 uppercase">ID: {{ row.id_role_customer }}</span>
                                    </div>
                                </td>

                                <td class="px-2 py-4 border-l border-base-300/30 w-48">
                                    <CustomSelect
                                        v-model="form.diskon[index].tipe"
                                        :options="tipeOptions"
                                        label-key="label"
                                        value-key="value"
                                    />
                                </td>

                                <td class="px-2 py-4 border-l border-base-300/30 min-w-[180px]">
                                    <CustomInputNumber
                                        v-model="form.diskon[index].nilai"
                                        :prefix="form.diskon[index].tipe === 'nominal' ? 'Rp' : ''"
                                        :suffix="form.diskon[index].tipe === 'persen' ? '%' : ''"
                                        :min="0"
                                        :max="form.diskon[index].tipe === 'persen' ? 100 : undefined"
                                        :error="form.errors[`diskon.${index}.nilai`]"
                                    />
                                </td>
                            </template>
                        </CustomTableForm>

                        <div class="flex gap-4 pt-8 border-t border-base-300">
                            <CustomButton type="submit" variant="primary" class="flex-1 py-4 rounded-2xl" :disabled="form.processing">
                                Simpan Semua Diskon
                            </CustomButton>
                            <CustomButton type="link" :href="route('produk.detailSku', sku.id_produk)" variant="secondary" class="py-4 rounded-2xl">
                                Batal
                            </CustomButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
