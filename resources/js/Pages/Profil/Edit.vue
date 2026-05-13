<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight tracking-wider uppercase text-base-content">
                Profil Saya
            </h2>
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
