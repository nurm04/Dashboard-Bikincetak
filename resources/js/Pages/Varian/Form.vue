<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/CustomInput.vue';
import CustomTableForm from '@/Components/CustomTableForm.vue';
import CustomButton from '@/Components/CustomButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    varian: Object,
});

const isEdit = !!props.varian;

const form = useForm({
    nama_varian: props.varian?.nama_varian ?? '',
    pilihans: props.varian?.pilihan_varian?.map(p => p.nama_pilihan) ?? [''],
});

const addRow = () => {
    form.pilihans.push('');
};

const submit = () => {
    if (isEdit) {
        form.put(route('varian.update', props.varian.id_varian));
    } else {
        form.post(route('varian.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Varian' : 'Tambah Varian'" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                {{ isEdit ? 'Edit Varian: ' + varian.nama_varian : 'Master Varian Baru' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 border rounded-lg shadow-xl bg-base-100 border-base-300">
                    <form @submit.prevent="submit" class="space-y-8">

                        <div class="p-6 space-y-4 border rounded-3xl bg-base-200/50 border-base-300">
                            <CustomInput
                                v-model="form.nama_varian"
                                label="Nama Varian"
                                placeholder="Contoh: Bahan, Ukuran"
                                :error="form.errors.nama_varian"
                            />
                        </div>

                        <CustomTableForm
                            v-model="form.pilihans"
                            label="Daftar Pilihan Varian"
                            :headers="['Nama Pilihan']"
                            @add="addRow"
                        >
                            <template #row="{ row, index }">
                                <td class="px-4 py-2">
                                    <input
                                        v-model="form.pilihans[index]"
                                        type="text"
                                        placeholder="Ketik pilihan..."
                                        class="w-full p-0 text-sm font-bold bg-transparent border-none focus:ring-0 text-base-content"
                                    />
                                </td>
                            </template>
                        </CustomTableForm>

                        <p v-if="form.errors.pilihans" class="text-error text-[10px] font-bold ml-1">
                            {{ form.errors.pilihans }}
                        </p>

                        <div class="flex flex-col items-center gap-4 pt-6 sm:flex-row">
                            <CustomButton
                                type="submit"
                                variant="primary"
                                class="flex-1 w-full py-4 sm:w-auto rounded-2xl"
                                :disabled="form.processing"
                            >
                                {{ isEdit ? 'Perbarui Varian' : 'Simpan Master Varian' }}
                            </CustomButton>

                            <CustomButton
                                type="link"
                                :href="route('varian.index')"
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
