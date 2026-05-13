<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/CustomInput.vue';
import CustomSelect from '@/Components/CustomSelect.vue';
import CustomRadioButton from '@/Components/CustomRadioButton.vue';
import CustomButton from '@/Components/CustomButton.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    kategori: Object,
});

const isEdit = !!props.kategori;

// Opsi untuk CustomSelect
const kategoriOptions = [
    { label: 'Harta', value: 'harta' },
    { label: 'Kewajiban', value: 'kewajiban' },
    { label: 'Modal', value: 'modal' },
    { label: 'Pendapatan', value: 'pendapatan' },
    { label: 'Beban', value: 'beban' },
];

// Opsi untuk CustomRadioButton
const saldoOptions = [
    { label: 'Debit', value: 'debit' },
    { label: 'Kredit', value: 'kredit' }
];

const form = useForm({
    nama_kategori: props.kategori?.nama_kategori ?? '',
});

const submit = () => {
    if (isEdit) {
        form.put(route('kategori.update', props.kategori.id_kategori || props.kategori.id));
    } else {
        form.post(route('kategori.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Kategori' : 'Tambah Kategori Baru'" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ isEdit ? 'Edit Kategori: ' + (kategori.id_kategori || kategori.id) : 'Tambah Kategori' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 border rounded-lg shadow-xl bg-base-100 border-base-300">
                    <form @submit.prevent="submit" class="space-y-8">

                        <CustomInput
                            v-model="form.nama_kategori"
                            label="Nama Kategori"
                            placeholder="Contoh: Print Digital"
                            :error="form.errors.nama_kategori"
                            required
                        />
                        <div class="flex flex-col items-center gap-4 pt-6 sm:flex-row">
                            <CustomButton
                                type="submit"
                                variant="primary"
                                class="flex-1 w-full py-4 sm:w-auto rounded-2xl"
                                :disabled="form.processing"
                            >
                                <template #icon v-if="!form.processing">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </template>
                                {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Daftarkan Kategori') }}
                            </CustomButton>

                            <CustomButton
                                type="link"
                                :href="route('kategori.index')"
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
