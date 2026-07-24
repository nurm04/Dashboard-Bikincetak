<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <!-- Wrapper Card menyesuaikan tema sistem -->
        <div class="w-full max-w-md p-8 mx-auto shadow-2xl sm:p-10 card bg-base-100 rounded-3xl border border-base-content/5">

            <!-- Header -->
            <div class="mb-8 text-center">
                <h2 class="text-3xl font-black tracking-widest uppercase text-primary">Password Baru</h2>
                <p class="mt-2 text-[10px] font-bold text-base-content/50 uppercase tracking-widest leading-relaxed">
                    Silakan buat password baru untuk akun Anda
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Input Email -->
                <CustomInput
                    v-model="form.email"
                    id="email"
                    type="email"
                    label="Alamat Email"
                    placeholder="admin@bikincetak.com"
                    :error="form.errors.email"
                    required
                    autofocus
                    autocomplete="username"
                    readonly
                />

                <!-- Input Password Baru -->
                <CustomInput
                    v-model="form.password"
                    id="password"
                    type="password"
                    label="Password Baru"
                    placeholder="••••••••"
                    :error="form.errors.password"
                    required
                    autocomplete="new-password"
                />

                <!-- Input Konfirmasi Password -->
                <CustomInput
                    v-model="form.password_confirmation"
                    id="password_confirmation"
                    type="password"
                    label="Konfirmasi Password"
                    placeholder="••••••••"
                    :error="form.errors.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <!-- Tombol Submit -->
                <div class="pt-4">
                    <CustomButton
                        type="submit"
                        variant="primary"
                        class="w-full h-12 shadow-lg rounded-xl shadow-primary/30 hover:shadow-primary/50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Memproses...' : 'Simpan Password' }}
                    </CustomButton>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
