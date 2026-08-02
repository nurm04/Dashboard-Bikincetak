<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import CustomCheckbox from '@/Components/Form/CustomCheckbox.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="w-full max-w-md p-8 mx-auto shadow-2xl sm:p-10 card bg-base-100 rounded-3xl border border-base-content/5">

            <div class="mb-8 text-center">
                <div className="flex flex-col items-center mb-5">
                    <div className="relative mb-2">
                        <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
                    </div>
                    <div className="h-1.5 w-8 bg-primary rounded-full"></div>
                </div>
                <p class="text-[10px] font-bold text-base-content/50 uppercase tracking-widest">
                    Silakan login untuk mengakses sistem
                </p>
            </div>

            <div v-if="status" class="p-3 mb-6 text-xs font-bold border rounded-xl text-success bg-success/10 border-success/20">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <CustomInput
                        label="Email"
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="Email"
                    />
                    <p v-if="form.errors.email" class="mt-2 ml-1 text-[10px] font-bold text-error uppercase tracking-wider">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <CustomInput
                        label="Password"
                        id="password"
                        type="password"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <p v-if="form.errors.password" class="mt-2 ml-1 text-[10px] font-bold text-error uppercase tracking-wider">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="flex items-center justify-between mt-2">
                    <label class="flex items-center gap-3 cursor-pointer w-fit">
                        <CustomCheckbox
                            label="Ingat Saya"
                            name="remember"
                            v-model="form.remember"
                        />
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[10px] font-black uppercase tracking-widest text-base-content/40 hover:text-primary transition-colors"
                    >
                        Lupa Password?
                    </Link>
                </div>

                <div class="pt-6">
                    <button
                        type="submit"
                        class="w-full h-12 shadow-lg btn btn-primary rounded-xl shadow-primary/30 hover:shadow-primary/50"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                        <span class="text-xs font-black tracking-widest uppercase">
                            {{ form.processing ? 'Memproses...' : 'Masuk Sistem' }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
