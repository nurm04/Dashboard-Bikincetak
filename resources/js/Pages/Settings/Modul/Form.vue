<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    modul: Object,
});

const isEdit = !!props.modul;

const form = useForm({
    nama_modul: props.modul?.nama_modul ?? '',
});

const submit = () => {
    if (isEdit) {
        form.put(route('modul.update', props.modul.id));
    } else {
        form.post(route('modul.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Modul' : 'Tambah Modul Baru'" />
    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                {{ isEdit ? 'Edit Modul: ' + modul.nama_modul : 'Tambah Modul Sistem Baru' }}
            </h2>
        </template>

        <div class="py-12 px-4 mx-auto max-w-3xl">
            <div class="p-10 border rounded-3xl shadow-xl bg-base-100 border-base-300">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block mb-2 ml-1 text-xs font-black text-base-content/50 uppercase tracking-widest">
                            Nama Modul
                        </label>
                        <input
                            v-model="form.nama_modul"
                            type="text"
                            placeholder="Contoh: Laporan Keuangan, Data Stok..."
                            class="w-full px-4 py-3 rounded-2xl border-base-300 bg-base-200/30 focus:ring-4 focus:ring-primary/10 focus:border-primary font-bold transition-all"
                            :class="{'border-error': form.errors.nama_modul}"
                        />
                        <p v-if="form.errors.nama_modul" class="mt-2 ml-1 text-[10px] font-bold text-error uppercase">
                            {{ form.errors.nama_modul }}
                        </p>
                    </div>

                    <div class="pt-6 flex gap-4 border-t border-base-300/50">
                        <CustomButton
                            type="submit"
                            variant="primary"
                            class="flex-1 py-4 rounded-2xl"
                            :disabled="form.processing"
                        >
                            {{ isEdit ? 'Simpan Perubahan' : 'Buat Modul Sekarang' }}
                        </CustomButton>

                        <CustomButton
                            type="link"
                            :href="route('hak-akses.index')"
                            variant="secondary"
                            class="py-4 rounded-2xl"
                        >
                            Batal
                        </CustomButton>
                    </div>
                </form>
            </div>
        </div>
    </StafLayout>
</template>
