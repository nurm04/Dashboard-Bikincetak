<script setup>
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    nama_vendor: user.vendor?.nama_vendor || user.name,
    nama_pic: user.vendor?.nama_pic || '',
    no_hp: user.vendor?.no_hp || '',
    alamat_lengkap: user.vendor?.alamat_lengkap || '',
    nama_bank: user.vendor?.nama_bank || '',
    no_rekening: user.vendor?.no_rekening || '',
    atas_nama: user.vendor?.atas_nama || '',
    // is_active dihapus dari sini karena vendor gak boleh non-aktifin dirinya sendiri
});

const submitForm = () => {
    // Semua role (termasuk vendor) tetap pakai rute bawaan profile
    form.patch(route('profil.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-bold text-base-content">
                Informasi Profil
            </h2>
            <p class="mt-1 text-sm text-base-content/70">
                Perbarui informasi profil dan alamat email akun Anda.
            </p>
        </header>

        <form @submit.prevent="submitForm" class="mt-6 space-y-6">
            <!-- TAMPILAN JIKA BUKAN VENDOR -->
            <template v-if="user.role !== 'vendor'">
                <CustomInput id="name" type="text" v-model="form.name" label="Nama Lengkap" required autofocus autocomplete="name" :error="form.errors.name" />
            </template>

            <!-- TAMPILAN JIKA VENDOR -->
            <template v-else>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <CustomInput id="nama_vendor" type="text" v-model="form.nama_vendor" label="Nama Vendor / Perusahaan" required autofocus :error="form.errors.nama_vendor" />
                    <CustomInput id="nama_pic" type="text" v-model="form.nama_pic" label="Nama PIC (Penanggung Jawab)" :error="form.errors.nama_pic" />
                    <CustomInput id="no_hp" type="text" v-model="form.no_hp" label="Nomor Handphone" required :error="form.errors.no_hp" />
                    <CustomInput id="alamat_lengkap" type="text" v-model="form.alamat_lengkap" label="Alamat Lengkap" :error="form.errors.alamat_lengkap" />
                </div>
            </template>

            <!-- EMAIL DIGUNAKAN OLEH KEDUANYA -->
            <CustomInput id="email" type="email" v-model="form.email" label="Email" required autocomplete="username" :error="form.errors.email" />

            <!-- FORM REKENING (Hanya tampil jika user adalah vendor) -->
            <div v-if="user.role === 'vendor'" class="space-y-6 pt-4 mt-6 border-t border-base-200">
                <header>
                    <h2 class="text-lg font-bold text-base-content">Informasi Rekening Bank</h2>
                    <p class="mt-1 text-sm text-base-content/70">Pastikan data rekening sesuai untuk keperluan pencairan dana.</p>
                </header>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <CustomInput id="nama_bank" type="text" v-model="form.nama_bank" label="Nama Bank (Cth: BCA, Mandiri)" :error="form.errors.nama_bank" />
                    <CustomInput id="no_rekening" type="text" v-model="form.no_rekening" label="Nomor Rekening" :error="form.errors.no_rekening" />
                    <div class="md:col-span-2">
                        <CustomInput id="atas_nama" type="text" v-model="form.atas_nama" label="Atas Nama Rekening" :error="form.errors.atas_nama" />
                    </div>
                </div>
            </div>

            <!-- VERIFIKASI EMAIL BAWAAN LARAVEL BREEZE -->
            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-base-content">
                    Alamat email Anda belum diverifikasi.
                    <Link :href="route('verification.send')" method="post" as="button" class="text-sm underline rounded-md text-primary hover:text-primary/80 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        Klik di sini untuk mengirim ulang email verifikasi.
                    </Link>
                </p>
                <div v-show="status === 'verification-link-sent'" class="mt-2 text-sm font-bold text-success">
                    Link verifikasi baru telah dikirim ke alamat email Anda.
                </div>
            </div>

            <!-- TOMBOL SIMPAN -->
            <div class="flex items-center gap-4 pt-2">
                <CustomButton type="submit" variant="primary" :disabled="form.processing" class="px-6 py-2">
                    Simpan Perubahan
                </CustomButton>
                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm font-bold text-success">Tersimpan.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
