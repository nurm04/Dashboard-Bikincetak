<script setup>
import CustomInput from '@/Components/CustomInput.vue';
import CustomButton from '@/Components/CustomButton.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                // Catatan: Pastikan CustomInput lu punya tag input di dalamnya kalau pakai focus()
                passwordInput.value.$el?.querySelector('input')?.focus() || passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.$el?.querySelector('input')?.focus() || currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-bold text-base-content">
                Perbarui Password
            </h2>

            <p class="mt-1 text-sm text-base-content/70">
                Pastikan akun Anda menggunakan password panjang dan acak agar tetap aman.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">

            <CustomInput
                id="current_password"
                ref="currentPasswordInput"
                v-model="form.current_password"
                type="password"
                label="Password Saat Ini"
                autocomplete="current-password"
                :error="form.errors.current_password"
            />

            <CustomInput
                id="password"
                ref="passwordInput"
                v-model="form.password"
                type="password"
                label="Password Baru"
                autocomplete="new-password"
                :error="form.errors.password"
            />

            <CustomInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                label="Konfirmasi Password Baru"
                autocomplete="new-password"
                :error="form.errors.password_confirmation"
            />

            <div class="flex items-center gap-4">
                <CustomButton type="submit" variant="primary" :disabled="form.processing" class="px-6 py-2">
                    Simpan Password
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
