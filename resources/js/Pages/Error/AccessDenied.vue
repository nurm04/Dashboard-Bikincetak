<script setup>
import { Head } from '@inertiajs/vue3';
import CustomButton from '@/Components/Form/CustomButton.vue';

defineProps({
    modul: String
});
</script>

<template>
    <Head title="Akses Ditolak" />

    <div class="flex flex-col items-center justify-center min-h-screen px-4 bg-base-200">
        <div class="w-full max-w-md p-10 text-center border shadow-xl bg-base-100 rounded-3xl border-base-300">
            <div class="flex justify-center mb-6 text-error opacity-80">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-24 h-24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>

            <h1 class="mb-2 text-2xl font-black text-base-content">Akses Ditolak!</h1>
            <p class="mb-8 text-sm font-medium leading-relaxed text-base-content/60">
                Maaf, jabatan Anda tidak memiliki izin untuk mengakses modul <strong class="text-error">{{ modul }}</strong>.
            </p>

            <CustomButton type="link" href="/dashboard" variant="primary" class="w-full">
                Kembali ke Dashboard
            </CustomButton>
        </div>
    </div>
</template>
