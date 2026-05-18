<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/CustomInput.vue';
import CustomSelectSearch from '@/Components/CustomSelectSearch.vue';
import CustomButton from '@/Components/CustomButton.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import CustomTableForm from '@/Components/CustomTableForm.vue';
import { alertStore } from '@/Utils/alertStore';

const props = defineProps({
    customer: Object,
    roles: Array,
});

const isEdit = !!props.customer;

const form = useForm({
    name: props.customer?.user?.name ?? '',
    email: props.customer?.user?.email ?? '',
    password: '',
    no_hp: props.customer?.no_hp ?? '',
    id_role_customer: props.customer?.id_role_customer ?? '',
    alamats: props.customer?.alamat?.map(a => a.alamat) ?? [''],
});

const addNewRole = (val) => {
    const roleName = prompt("Masukkan nama role baru:", val);
    if (roleName) {
        router.post(route('role-customer.store'), { role: roleName }, { preserveScroll: true });
    }
};

const addRow = () => {
    form.alamats.push('');
};

const submit = () => {
    if (isEdit) {
        form.put(route('customer.update', props.customer.id_customer), {
            onSuccess: () => alertStore.show('Data customer dan alamat berhasil diperbarui!', 'success'),
            onError: () => alertStore.show('Gagal memperbarui data!', 'error')
        });
    } else {
        form.post(route('customer.store'), {
            onSuccess: () => alertStore.show('Customer berhasil disimpan!', 'success'),
            onError: () => alertStore.show('Gagal menyimpan data!', 'error')
        });
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Customer' : 'Registrasi Customer'" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                {{ isEdit ? 'Edit Data: ' + customer.id_customer : 'Tambah Customer Baru' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 border rounded-lg shadow-xl bg-base-100 border-base-300">
                    <form @submit.prevent="submit" class="space-y-8">

                        <div class="p-6 space-y-4 border rounded-lg bg-base-200/50 border-base-300">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <CustomInput v-model="form.name" label="Nama Lengkap" placeholder="John Doe" :error="form.errors.name" required />
                                <CustomInput v-model="form.email" label="Email Aktif" type="email" placeholder="john@example.com" :error="form.errors.email" required />
                            </div>
                            <CustomInput v-model="form.password" label="Password" type="password"
                                :placeholder="isEdit ? 'Kosongkan jika tidak ganti' : 'Minimal 8 karakter'"
                                :required="!isEdit" :error="form.errors.password" />
                        </div>

                        <div class="space-y-4">
                            <h2 class="text-xs font-black text-base-content/50 uppercase tracking-[0.2em] ml-1">Profil & Membership</h2>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <CustomInput v-model="form.no_hp" label="No. WhatsApp" placeholder="0812..." :error="form.errors.no_hp" required />

                                <CustomSelectSearch
                                    v-model="form.id_role_customer"
                                    label="Level Member"
                                    :options="roles"
                                    labelKey="role"
                                    valueKey="id_role_customer"
                                    :error="form.errors.id_role_customer"
                                    @onCreate="addNewRole"
                                />
                            </div>
                        </div>

                        <CustomTableForm
                            v-model="form.alamats"
                            label="Daftar Alamat"
                            :headers="['Alamat']"
                            @add="addRow"
                        >
                            <template #row="{ row, index }">
                                <td class="px-4 py-2">
                                    <input
                                        v-model="form.alamats[index]"
                                        type="text"
                                        placeholder="Ketik Alamat..."
                                        class="w-full p-0 text-sm font-bold bg-transparent border-none focus:ring-0 text-base-content outline-none"
                                        required
                                    />
                                </td>
                            </template>
                        </CustomTableForm>

                        <p v-if="form.errors.alamats" class="text-error text-[10px] font-bold ml-1">
                            {{ form.errors.alamats }}
                        </p>

                        <div class="flex flex-col items-center gap-4 pt-6 sm:flex-row">
                            <CustomButton
                                type="submit"
                                variant="primary"
                                class="flex-1 w-full py-4 rounded-lg sm:w-auto font-black"
                                :disabled="form.processing"
                            >
                                <template #icon v-if="!form.processing">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </template>
                                {{ isEdit ? 'Perbarui Data Customer' : 'Konfirmasi & Simpan Customer' }}
                            </CustomButton>

                            <CustomButton
                                type="link"
                                :href="route('customer.index')"
                                variant="secondary"
                                class="w-full py-4 rounded-lg sm:w-auto"
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
