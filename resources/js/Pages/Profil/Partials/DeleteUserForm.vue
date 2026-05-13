<script setup>
import CustomInput from '@/Components/CustomInput.vue';
import CustomButton from '@/Components/CustomButton.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.$el?.querySelector('input')?.focus() || passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.$el?.querySelector('input')?.focus() || passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-bold text-base-content">
                Hapus Akun
            </h2>

            <p class="mt-1 text-sm text-base-content/70">
                Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Sebelum menghapus akun Anda, harap unduh data atau informasi apa pun yang ingin Anda simpan.
            </p>
        </header>

        <button @click="confirmUserDeletion" class="px-6 shadow-lg btn btn-error text-error-content shadow-error/30 rounded-xl">
            Hapus Akun Permanen
        </button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6 bg-base-100">
                <h2 class="text-lg font-bold text-base-content">
                    Apakah Anda yakin ingin menghapus akun ini?
                </h2>

                <p class="mt-1 text-sm text-base-content/70">
                    Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Masukkan password Anda untuk mengonfirmasi penghapusan.
                </p>

                <div class="mt-6">
                    <CustomInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="w-full sm:w-3/4"
                        placeholder="Masukkan Password Anda"
                        :error="form.errors.password"
                        @keyup.enter="deleteUser"
                    />
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <CustomButton variant="secondary" @click="closeModal" class="px-6">
                        Batal
                    </CustomButton>

                    <button
                        class="px-6 shadow-lg btn btn-error text-error-content rounded-xl shadow-error/30"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Ya, Hapus Akun
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
