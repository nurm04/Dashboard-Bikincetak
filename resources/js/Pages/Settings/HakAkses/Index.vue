<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomTableForm from '@/Components/CustomTableForm.vue';
import CustomButton from '@/Components/CustomButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import CustomCheckbox from '@/Components/CustomCheckbox.vue';

const props = defineProps({
    modul: Object,
    roles: Array,
    existingAkses: Array
});

const form = useForm({
    akses: props.roles.map(role => {
        const current = props.existingAkses.find(a => a.id_role_staf === role.id_role_staf);
        return {
            id_role_staf: role.id_role_staf,
            role: role.role,
            bisa_buka: current ? !!current.bisa_buka : false,
            bisa_tambah: current ? !!current.bisa_tambah : false,
            bisa_ubah: current ? !!current.bisa_ubah : false,
            bisa_hapus: current ? !!current.bisa_hapus : false,
        };
    })
});

const submit = () => {
    form.post(route('hak-akses.sync', props.modul.id));
};
</script>

<template>
    <Head :title="'Hak Akses: ' + modul.nama_modul" />
    <StafLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-base-content">
                Konfigurasi Akses: {{ modul.nama_modul }}
            </h2>
        </template>

        <div class="py-12 px-4 mx-auto max-w-6xl">
            <div class="p-8 border rounded-lg shadow-xl bg-base-100 border-base-300">
                <form @submit.prevent="submit">
                    <CustomTableForm
                        v-model="form.akses"
                        label="Otoritas Role Staf"
                        :headers="['Role / Jabatan', 'Buka', 'Tambah', 'Ubah', 'Hapus']"
                        :can-add="false"
                        :can-delete="false"
                    >
                        <template #row="{ row, index }">
                            <td class="px-4 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-black uppercase text-base-content tracking-widest">{{ row.role }}</span>
                                    <span class="text-[9px] opacity-40 font-bold tracking-tighter italic">{{ row.id_role_staf }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center border-l border-base-300/10 w-24">
                                <CustomCheckbox v-model="form.akses[index].bisa_buka" color="primary" />
                            </td>
                            <td class="px-4 py-4 text-center border-l border-base-300/10 w-24">
                                <CustomCheckbox v-model="form.akses[index].bisa_tambah" color="success" />
                            </td>
                            <td class="px-4 py-4 text-center border-l border-base-300/10 w-24">
                                <CustomCheckbox v-model="form.akses[index].bisa_ubah" color="warning" />
                            </td>
                            <td class="px-4 py-4 text-center border-l border-base-300/10 w-24">
                                <CustomCheckbox v-model="form.akses[index].bisa_hapus" color="error" />
                            </td>
                        </template>
                    </CustomTableForm>

                    <div class="mt-10 flex gap-4 border-t border-base-300 pt-8">
                        <CustomButton type="submit" variant="primary" class="flex-1 py-4 rounded-2xl" :disabled="form.processing">
                            Simpan Seluruh Hak Akses
                        </CustomButton>
                        <CustomButton type="link" :href="route('hak-akses.index')" variant="secondary" class="py-4 rounded-2xl">Batal</CustomButton>
                    </div>
                </form>
            </div>
        </div>
    </StafLayout>
</template>
