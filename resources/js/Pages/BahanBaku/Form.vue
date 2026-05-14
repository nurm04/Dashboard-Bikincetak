<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/CustomInput.vue';
import CustomInputNumber from '@/Components/CustomInputNumber.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomCheckbox from '@/Components/CustomCheckbox.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    bahan: Object,
});

const isEdit = !!props.bahan;

const form = useForm({
    nama_bahan_baku: props.bahan?.nama_bahan_baku ?? '',
    satuan: props.bahan?.satuan ?? '',
    harga_beli: props.bahan?.harga_beli ?? 0,
    stok_awal: props.bahan?.stok_awal ?? 0,
    is_active: props.bahan ? !!props.bahan.is_active : true,
});

const submit = () => {
    if (isEdit) {
        form.put(route('bahan-baku.update', props.bahan.id_bahan_baku));
    } else {
        form.post(route('bahan-baku.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Bahan Baku' : 'Tambah Bahan Baku'" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                {{ isEdit ? 'Edit Bahan: ' + bahan.nama_bahan_baku : 'Input Bahan Baku Baru' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 border rounded-lg shadow-xl bg-base-100 border-base-300">
                    <form @submit.prevent="submit" class="space-y-8">

                        <div class="p-6 space-y-6 border rounded-lg bg-base-200/50 border-base-300">
                            <CustomInput
                                v-model="form.nama_bahan_baku"
                                label="Nama Bahan Baku"
                                placeholder="Contoh: Kertas Art Paper 260gr"
                                :error="form.errors.nama_bahan_baku"
                            />

                            <div class="grid grid-cols-2 gap-4">
                                <CustomInput
                                    v-model="form.satuan"
                                    label="Satuan"
                                    placeholder="Lembar/Roll/Botol"
                                    :error="form.errors.satuan"
                                />
                                <div>
                                    <label class="block mb-1 ml-1 text-xs font-bold text-base-content/70">Harga Beli</label>
                                    <CustomInputNumber v-model="form.harga_beli" prefix="Rp" />
                                </div>
                            </div>
                        </div>

                        <div v-if="!isEdit" class="p-6 border rounded-lg bg-primary/5 border-primary/10">
                             <label class="block mb-1 ml-1 text-xs font-bold text-primary">Saldo Awal Stok</label>
                             <CustomInputNumber v-model="form.stok_awal" />
                             <p class="mt-2 text-[9px] font-bold opacity-40 uppercase tracking-tight">Stok ini akan otomatis menjadi saldo awal sekarang.</p>
                        </div>

                        <div v-if="isEdit" class="flex justify-center p-4 border border-dashed rounded-2xl border-base-300">
                            <CustomCheckbox v-model="form.is_active" label="Bahan Masih Tersedia / Aktif" color="primary" />
                        </div>

                        <div class="flex flex-col items-center gap-4 pt-6 sm:flex-row border-t border-base-300/50">
                            <CustomButton
                                type="submit"
                                variant="primary"
                                class="flex-1 w-full py-4 rounded-2xl"
                                :disabled="form.processing"
                            >
                                {{ isEdit ? 'Perbarui Data Bahan' : 'Simpan Bahan Baku' }}
                            </CustomButton>

                            <CustomButton
                                type="link"
                                :href="route('bahan-baku.index')"
                                variant="secondary"
                                class="w-full py-4 sm:w-auto rounded-2xl px-8"
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
