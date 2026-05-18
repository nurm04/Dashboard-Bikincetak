<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/CustomInput.vue';
import CustomInputNumber from '@/Components/CustomInputNumber.vue';
import CustomSelect from '@/Components/CustomSelect.vue';
import CustomTableForm from '@/Components/CustomTableForm.vue';
import CustomButton from '@/Components/CustomButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    pembelian: Object,
    bahan_baku: Array,
});

const isEdit = !!props.pembelian;

const form = useForm({
    tanggal_beli: props.pembelian?.tanggal_beli ? props.pembelian.tanggal_beli.split(' ')[0] : '',
    nama_supplier: props.pembelian?.nama_supplier ?? '',
    details: props.pembelian?.detail_pembelian?.map(d => ({
        id_bahan_baku: d.id_bahan_baku,
        jumlah: d.jumlah,
        harga_satuan: d.harga_satuan,
    })) || [{ id_bahan_baku: '', jumlah: 1, harga_satuan: 0 }],
});

const addRow = () => {
    form.details.push({ id_bahan_baku: '', jumlah: 1, harga_satuan: 0 });
};

const submit = () => {
    if (isEdit) {
        form.put(route('pembelian-bahan.update', props.pembelian.id_pembelian));
    } else {
        form.post(route('pembelian-bahan.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Pembelian' : 'Tambah Pembelian'" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                {{ isEdit ? 'Edit Pembelian: ' + pembelian.id_pembelian : 'Transaksi Pembelian Baru' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 border rounded-lg shadow-xl bg-base-100 border-base-300">
                    <form @submit.prevent="submit" class="space-y-8">

                        <div class="p-6 space-y-4 border rounded-3xl bg-base-200/50 border-base-300">
                            <CustomInput
                                v-model="form.tanggal_beli"
                                type="date"
                                label="Tanggal Pembelian"
                                :error="form.errors.tanggal_beli"
                            />
                            <CustomInput
                                v-model="form.nama_supplier"
                                label="Nama Supplier"
                                placeholder="Contoh: CV. Kertas Abadi"
                                :error="form.errors.nama_supplier"
                            />
                        </div>

                        <CustomTableForm
                            v-model="form.details"
                            label="Daftar Bahan Baku"
                            :headers="['Bahan Baku', 'Jumlah', 'Harga Satuan']"
                            @add="addRow"
                        >
                            <template #row="{ row, index }">
                                <td class="px-4 py-2">
                                    <CustomSelect
                                        v-model="form.details[index].id_bahan_baku"
                                        :options="bahan_baku"
                                        label-key="nama_bahan_baku"
                                        value-key="id_bahan_baku"
                                        placeholder="Pilih Bahan..."
                                        class="w-full"
                                    />
                                </td>
                                <td class="px-4 py-2">
                                    <CustomInputNumber
                                        v-model="form.details[index].jumlah"
                                        :min="1"
                                    />
                                </td>
                                <td class="px-4 py-2">
                                    <CustomInputNumber
                                        v-model="form.details[index].harga_satuan"
                                        :min="0"
                                    />
                                </td>
                            </template>
                        </CustomTableForm>

                        <p v-if="form.errors.details" class="text-error text-[10px] font-bold ml-1">
                            {{ form.errors.details }}
                        </p>

                        <div class="flex flex-col items-center gap-4 pt-6 sm:flex-row">
                            <CustomButton
                                type="submit"
                                variant="primary"
                                class="flex-1 w-full py-4 sm:w-auto rounded-2xl"
                                :disabled="form.processing"
                            >
                                {{ isEdit ? 'Perbarui Pembelian' : 'Simpan Pembelian' }}
                            </CustomButton>

                            <CustomButton
                                type="link"
                                :href="route('pembelian-bahan.index')"
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
