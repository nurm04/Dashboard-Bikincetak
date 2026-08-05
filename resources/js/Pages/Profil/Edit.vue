<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.get(route('dashboard'));
    }
};
</script>

<template>
    <Head title="Profile" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <button @click.prevent="goBack" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300 transition-colors hover:bg-base-200">
                        <ArrowLeft class="w-4 h-4" />
                    </button>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Profil Saya
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">

                <div class="p-4 transition-colors border shadow-xl bg-base-100 border-base-300 sm:rounded-lg sm:p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="p-4 transition-colors border shadow-xl bg-base-100 border-base-300 sm:rounded-lg sm:p-8">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="p-4 transition-colors border shadow-xl bg-base-100 border-base-300 sm:rounded-lg sm:p-8">
                    <DeleteUserForm class="max-w-xl" />
                </div>

            </div>
        </div>
    </StafLayout>
</template>
