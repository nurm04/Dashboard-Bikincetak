<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/CustomInput.vue';
import CustomSelect from '@/Components/CustomSelect.vue';
import CustomRadioButton from '@/Components/CustomRadioButton.vue';
import CustomButton from '@/Components/CustomButton.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    akun: Object,
});

const isEdit = !!props.akun;

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
    nama_akun: props.akun?.nama_akun ?? '',
    kategori: props.akun?.kategori ?? '',
    saldo_normal: props.akun?.saldo_normal ?? 'debit',
});

const submit = () => {
    if (isEdit) {
        form.put(route('akun.update', props.akun.id_akun || props.akun.id));
    } else {
        form.post(route('akun.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Akun' : 'Tambah Akun Baru'" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                {{ isEdit ? 'Edit Akun: ' + (akun.id_akun || akun.id) : 'Tambah COA' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 border rounded-lg shadow-xl bg-base-100 border-base-300">
                    <form @submit.prevent="submit" class="space-y-8">

                        <CustomInput
                            v-model="form.nama_akun"
                            label="Nama Akun Keuangan"
                            placeholder="Contoh: Kas Besar atau Piutang Usaha"
                            :error="form.errors.nama_akun"
                            required
                        />

                        <CustomSelect
                            v-model="form.kategori"
                            label="Kategori Akun"
                            :options="kategoriOptions"
                            labelKey="label"
                            valueKey="value"
                            :error="form.errors.kategori"
                        />

                        <CustomRadioButton
                            v-model="form.saldo_normal"
                            label="Saldo Normal (Default)"
                            name="saldo_normal"
                            :options="saldoOptions"
                            :error="form.errors.saldo_normal"
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
                                {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Daftarkan Akun') }}
                            </CustomButton>

                            <CustomButton
                                type="link"
                                :href="route('akun.index')"
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
