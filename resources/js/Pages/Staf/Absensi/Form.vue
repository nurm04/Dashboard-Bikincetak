<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import CustomTextarea from '@/Components/Form/CustomTextarea.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    absensi: Object, // Kalau null berarti mode "Tambah Baru", kalau ada isinya berarti mode "Edit"
    stafs: Array     // Daftar staf dari backend buat dropdown
});

const isEdit = !!props.absensi;

const statusOptions = [
    { value: 'Tepat Waktu', label: 'Hadir (Tepat Waktu)' },
    { value: 'Terlambat', label: 'Hadir (Terlambat)' },
    { value: 'Alpha', label: 'Alpha / Bolos' },
    { value: 'Izin', label: 'Izin' },
    { value: 'Sakit', label: 'Sakit' },
];

const form = useForm({
    id_staf: props.absensi?.id_staf ?? '',
    tanggal: props.absensi?.tanggal ?? new Date().toISOString().split('T')[0], // Default hari ini
    status: props.absensi?.status ?? 'Izin',
    jam_masuk: props.absensi?.jam_masuk ?? '',
    jam_keluar: props.absensi?.jam_keluar ?? '',
    keterangan: props.absensi?.keterangan ?? '',
});

const submit = () => {
    if (isEdit) {
        form.put(route('absensi.update', props.absensi.id));
    } else {
        form.post(route('absensi.storeManual'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Absensi' : 'Input Absensi Manual'" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('absensi.rekap')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        {{ isEdit ? 'Edit Catatan Absensi' : 'Input Absensi Manual / Izin' }}
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="p-8 border shadow-xl rounded-2xl bg-base-100 border-base-300">
                    <form @submit.prevent="submit" class="space-y-6">

                        <div class="p-6 space-y-6 border rounded-2xl bg-base-200/30 border-base-300">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <CustomSelect
                                        v-model="form.id_staf"
                                        label="Nama Staf"
                                        :options="stafs"
                                        valueKey="value"
                                        labelKey="label"
                                        :error="form.errors.id_staf"
                                        :disabled="isEdit"
                                    />
                                    <p v-if="isEdit" class="text-[9px] font-bold text-warning mt-1">*Nama staf tidak dapat diubah di mode edit</p>
                                </div>

                                <CustomInput
                                    v-model="form.tanggal"
                                    type="date"
                                    label="Tanggal Absensi"
                                    :error="form.errors.tanggal"
                                    :disabled="isEdit"
                                />
                            </div>

                            <div class="divider opacity-10 my-0"></div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                                <CustomSelect
                                    v-model="form.status"
                                    label="Status Kehadiran"
                                    :options="statusOptions"
                                    valueKey="value"
                                    labelKey="label"
                                    :error="form.errors.status"
                                />

                                <CustomInput
                                    v-model="form.jam_masuk"
                                    type="time"
                                    label="Jam Masuk (Opsional)"
                                    :error="form.errors.jam_masuk"
                                />

                                <CustomInput
                                    v-model="form.jam_keluar"
                                    type="time"
                                    label="Jam Pulang (Opsional)"
                                    :error="form.errors.jam_keluar"
                                />
                            </div>

                            <CustomTextarea
                                v-model="form.keterangan"
                                label="Keterangan / Alasan (Opsional)"
                                placeholder="Contoh: Surat dokter menyusul, Izin keluarga, Lupa absen pulang, dll."
                                :error="form.errors.keterangan"
                                rows="3"
                            />
                        </div>

                        <div class="flex flex-col items-center gap-4 pt-4 sm:flex-row">
                            <CustomButton
                                type="submit"
                                variant="primary"
                                class="flex-1 w-full py-4 sm:w-auto rounded-xl font-black uppercase tracking-widest"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing" class="loading loading-spinner"></span>
                                <span v-else>{{ isEdit ? 'Simpan Perubahan' : 'Catat Kehadiran' }}</span>
                            </CustomButton>

                            <CustomButton
                                type="link"
                                :href="route('absensi.rekap')"
                                variant="secondary"
                                class="w-full py-4 sm:w-auto rounded-xl font-bold uppercase tracking-widest"
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
