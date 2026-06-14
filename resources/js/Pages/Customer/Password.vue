<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/CustomInput.vue';
import CustomButton from '@/Components/CustomButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';

const props = defineProps({
    customer: Object,
});

const form = useForm({
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(
        route('customer.password.update', props.customer.id_customer),
        {
            onSuccess: () => {
                alertStore.show(
                    'Password berhasil diperbarui dan email notifikasi telah dikirim.',
                    'success'
                );
            },
            onError: () => {
                alertStore.show(
                    'Gagal memperbarui password.',
                    'error'
                );
            }
        }
    );
};
</script>

<template>
    <Head title="Ubah Password Customer" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Ubah Password Customer
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

                <div class="p-10 border rounded-lg shadow-xl bg-base-100 border-base-300">

                    <div class="mb-8">
                        <h1 class="text-2xl font-black">
                            {{ customer.user.name }}
                        </h1>

                        <p class="mt-1 text-sm text-base-content/60">
                            {{ customer.user.email }}
                        </p>

                        <div class="mt-4 alert alert-warning">
                            <span>
                                Password baru akan dikirim otomatis ke email customer setelah disimpan.
                            </span>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">

                        <CustomInput
                            v-model="form.password"
                            label="Password Baru"
                            type="password"
                            placeholder="Minimal 8 karakter"
                            :error="form.errors.password"
                            required
                        />

                        <CustomInput
                            v-model="form.password_confirmation"
                            label="Konfirmasi Password"
                            type="password"
                            placeholder="Ulangi password"
                            :error="form.errors.password_confirmation"
                            required
                        />

                        <div class="flex gap-4 pt-4">

                            <CustomButton
                                type="submit"
                                variant="warning"
                                :disabled="form.processing"
                                class="flex-1"
                            >
                                Simpan Password Baru
                            </CustomButton>

                            <CustomButton
                                type="link"
                                :href="route('customer.index')"
                                variant="secondary"
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
