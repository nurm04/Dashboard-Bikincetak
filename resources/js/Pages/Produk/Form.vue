<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/CustomInput.vue';
import CustomSelect from '@/Components/CustomSelect.vue';
import CustomButton from '@/Components/CustomButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ produk: Object, kategoris: Array });
const isEdit = !!props.produk;

const form = useForm({
    nama_produk: props.produk?.nama_produk ?? '',
    id_kategori: props.produk?.id_kategori ?? '',
});

const submit = () => {
    if (isEdit) form.put(route('produk.update', props.produk.id_produk));
    else form.post(route('produk.store'));
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Produk' : 'Tambah Produk'" />
    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                {{ isEdit ? 'Edit produk: ' + props.produk.id_produk : 'Registrasi Produk Baru' }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 border rounded-lg shadow-xl bg-base-100 border-base-300">
                    <form @submit.prevent="submit" class="space-y-6">
                        <CustomInput v-model="form.nama_produk" label="Nama Produk" placeholder="Contoh: Print A0 Hot" :error="form.errors.nama_produk" />
                        <CustomSelect v-model="form.id_kategori" label="Kategori" :options="kategoris" labelKey="nama_kategori" valueKey="id_kategori" :error="form.errors.id_kategori" />
                        <div class="flex gap-4 pt-4">
                            <CustomButton type="submit" class="flex-1" :disabled="form.processing">Simpan Produk</CustomButton>
                            <CustomButton type="link" :href="route('produk.index')" variant="secondary">Batal</CustomButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
