<script setup>
import CustomInput from '@/Components/CustomInput.vue';
import CustomButton from '@/Components/CustomButton.vue';
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
});
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

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <CustomInput
                id="name"
                type="text"
                v-model="form.name"
                label="Nama Lengkap"
                required
                autofocus
                autocomplete="name"
                :error="form.errors.name"
            />

            <CustomInput
                id="email"
                type="email"
                v-model="form.email"
                label="Email"
                required
                autocomplete="username"
                :error="form.errors.email"
            />

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-base-content">
                    Alamat email Anda belum diverifikasi.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="text-sm underline rounded-md text-primary hover:text-primary/80 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        Klik di sini untuk mengirim ulang email verifikasi.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-bold text-success"
                >
                    Link verifikasi baru telah dikirim ke alamat email Anda.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <CustomButton type="submit" variant="primary" :disabled="form.processing" class="px-6 py-2">
                    Simpan Perubahan
                </CustomButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm font-bold text-base-content/70"
                    >
                        Tersimpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
