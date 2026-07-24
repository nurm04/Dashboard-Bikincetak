<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Lupa Password" />

        <div class="w-full max-w-md p-8 mx-auto shadow-2xl sm:p-10 card bg-base-100 rounded-3xl border border-base-content/5">

            <div class="mb-6 text-center">
                <h2 class="text-xl font-black tracking-widest uppercase text-primary">Reset Password</h2>
                <p class="mt-4 text-[10px] font-bold text-base-content/50 uppercase tracking-widest leading-relaxed">
                    Tidak masalah. Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang password.
                </p>
            </div>

            <div
                v-if="status"
                class="p-3 mb-6 text-[10px] font-black tracking-widest uppercase border rounded-xl text-success bg-success/10 border-success/20 text-center"
            >
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
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
                />

                <div class="pt-2">
                    <CustomButton
                        type="submit"
                        variant="primary"
                        class="w-full h-12 shadow-lg rounded-xl shadow-primary/30 hover:shadow-primary/50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Memproses...' : 'Kirim Link Reset' }}
                    </CustomButton>
                </div>

                <div class="mt-6 text-center">
                    <Link
                        :href="route('login')"
                        class="text-[10px] font-black tracking-widest uppercase text-base-content/40 hover:text-primary transition-colors"
                    >
                        Kembali ke Halaman Login
                    </Link>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
