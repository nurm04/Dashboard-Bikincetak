<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomTextarea from '@/Components/Form/CustomTextarea.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    vendor: Object,
});

const isEdit = !!props.vendor;

const form = useForm({
    nama_vendor: props.vendor?.nama_vendor ?? '',
    email: props.vendor?.user?.email ?? '',
    password: '',
    nama_pic: props.vendor?.nama_pic ?? '',
    no_hp: props.vendor?.no_hp ?? '',
    alamat_lengkap: props.vendor?.alamat_lengkap ?? '',
    is_active: props.vendor?.is_active ?? true,
    nama_bank: props.vendor?.nama_bank ?? '',
    no_rekening: props.vendor?.no_rekening ?? '',
    atas_nama: props.vendor?.atas_nama ?? '',
});

const statusOptions = [
    { value: true, label: 'Aktif' },
    { value: false, label: 'Nonaktif' }
];

const submit = () => {
    if (isEdit) {
        form.put(route('vendor.update', props.vendor.id_vendor));
    } else {
        form.post(route('vendor.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Vendor' : 'Registrasi Vendor'" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('vendor.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        {{ isEdit ? 'Edit Data: ' + vendor.id_vendor : 'Tambah Vendor Baru' }}
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 border rounded-lg shadow-xl bg-base-100 border-base-300">
                    <form @submit.prevent="submit" class="space-y-8">

                        <div class="space-y-4">
                            <h2 class="text-xs font-black text-base-content/50 uppercase tracking-[0.2em] ml-1">Data Perusahaan & Akses</h2>
                            <div class="p-6 space-y-4 border rounded-lg bg-base-200/50 border-base-300">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <CustomInput v-model="form.nama_vendor" label="Nama Vendor / Perusahaan" placeholder="PT Cetak Bersama" :error="form.errors.nama_vendor" />
                                    <CustomInput v-model="form.email" label="Email Login" type="email" placeholder="vendor@example.com" :error="form.errors.email" />
                                </div>
                                <CustomInput v-model="form.password" label="Password Login" type="password"
                                    :placeholder="isEdit ? 'Kosongkan jika tidak diganti' : 'Minimal 6 karakter'"
                                    :required="!isEdit" :error="form.errors.password" />
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h2 class="text-xs font-black text-base-content/50 uppercase tracking-[0.2em] ml-1">Kontak & Operasional</h2>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <CustomInput v-model="form.nama_pic" label="Nama PIC (Penanggung Jawab)" placeholder="Budi Santoso" :error="form.errors.nama_pic" />
                                <CustomInput v-model="form.no_hp" label="No. WhatsApp" placeholder="0812..." :error="form.errors.no_hp" />
                            </div>

                            <CustomTextarea
                                v-model="form.alamat_lengkap"
                                label="Alamat Lengkap"
                                placeholder="Tuliskan alamat lengkap vendor..."
                                :error="form.errors.alamat_lengkap"
                                :rows="3"
                            />
                        </div>

                        <div class="space-y-4">
                            <h2 class="text-xs font-black text-base-content/50 uppercase tracking-[0.2em] ml-1">Informasi Pembayaran (Opsional)</h2>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <CustomInput v-model="form.nama_bank" label="Nama Bank" placeholder="BCA / Mandiri" :error="form.errors.nama_bank" />
                                <CustomInput v-model="form.no_rekening" label="Nomor Rekening" placeholder="1234567890" :error="form.errors.no_rekening" />
                                <CustomInput v-model="form.atas_nama" label="Atas Nama" placeholder="Budi Santoso" :error="form.errors.atas_nama" />
                            </div>

                            <div v-if="isEdit" class="w-full md:w-1/2">
                                <CustomSelect
                                    v-model="form.is_active"
                                    label="Status Vendor"
                                    :options="statusOptions"
                                    valueKey="value"
                                    labelKey="label"
                                    :error="form.errors.is_active"
                                />
                            </div>
                        </div>

                        <div class="flex flex-col items-center gap-4 pt-6 sm:flex-row">
                            <CustomButton
                                type="submit"
                                variant="primary"
                                class="flex-1 w-full py-4 rounded-lg sm:w-auto"
                                :disabled="form.processing"
                            >
                                <template #icon v-if="!form.processing">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </template>
                                {{ isEdit ? 'Perbarui Data Vendor' : 'Konfirmasi & Simpan Vendor' }}
                            </CustomButton>

                            <CustomButton
                                type="link"
                                :href="route('vendor.index')"
                                variant="secondary"
                                class="w-full py-4 rounded-lg sm:w-auto"
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
