<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/CustomInput.vue';
import CustomButton from '@/Components/CustomButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import CustomCheckbox from '@/Components/CustomCheckbox.vue';

const props = defineProps({
    customer: Object
});

const form = useForm({
    id_alamat: null,

    label: '',
    nama_penerima: '',
    no_hp: '',

    provinsi: '',
    kota: '',
    kecamatan: '',
    kelurahan: '',

    kode_pos: '',

    alamat_lengkap: '',

    latitude: null,
    longitude: null,

    is_default: false,
});

const resetForm = () => {
    form.reset();

    form.id_alamat = null;
    form.is_default = false;
};

const editAlamat = (alamat) => {

    form.id_alamat = alamat.id_alamat;

    form.label = alamat.label ?? '';
    form.nama_penerima = alamat.nama_penerima ?? '';
    form.no_hp = alamat.no_hp ?? '';

    form.provinsi = alamat.provinsi ?? '';
    form.kota = alamat.kota ?? '';
    form.kecamatan = alamat.kecamatan ?? '';
    form.kelurahan = alamat.kelurahan ?? '';

    form.kode_pos = alamat.kode_pos ?? '';

    form.alamat_lengkap = alamat.alamat_lengkap ?? '';

    form.latitude = alamat.latitude ?? '';
    form.longitude = alamat.longitude ?? '';

    form.is_default = alamat.is_default;
};

const submit = () => {
    if (form.id_alamat) {
        form.put(
            route('alamat.update', form.id_alamat),
            {
                preserveScroll: true,
                onSuccess: () => {
                    alertStore.show('Alamat berhasil diperbarui','success');
                    resetForm();
                }
            }
        );
        return;
    }

    form.post(
        route('alamat.store',props.customer.id_customer),
        {
            preserveScroll: true,
            onSuccess: () => {
                alertStore.show('Alamat berhasil ditambahkan','success');
                resetForm();
            }
        }
    );
};

const hapusAlamat = (alamat) => {
    if (!confirm('Hapus alamat ini?')) {
        return;
    }

    form.delete(
        route('alamat.destroy', alamat.id_alamat),
        {
            preserveScroll: true,
            onSuccess: () => {
                alertStore.show('Alamat berhasil dihapus','success');
            }
        }
    );
};
</script>

<template>
    <Head title="Alamat Customer" />

    <StafLayout>

        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Alamat Customer
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
                <!-- CUSTOMER INFO -->
                <div class="p-6 border rounded-xl bg-base-100 border-base-300 shadow-lg">
                    <h1 class="text-2xl font-black">{{ customer.user.name }}</h1>
                    <p class="mt-1 text-base-content/60">{{ customer.user.email }}</p>
                    <p class="mt-1 text-base-content/60">{{ customer.no_hp }}</p>                </div>

                <!-- FORM -->
                <div class="p-8 border rounded-xl bg-base-100 border-base-300 shadow-lg">
                    <h2 class="mb-6 text-xs font-black tracking-[0.2em] uppercase text-base-content/50">
                        {{ form.id_alamat ? 'Edit Alamat' : 'Tambah Alamat' }}
                    </h2>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <CustomInput v-model="form.label" label="Label" placeholder="Rumah / Kantor"/>
                            <CustomInput v-model="form.nama_penerima" label="Nama Penerima" :error="form.errors.nama_penerima" required/>
                            <CustomInput v-model="form.no_hp" label="No HP" :error="form.errors.no_hp" required/>
                            <CustomInput v-model="form.kode_pos" label="Kode Pos" :error="form.errors.kode_pos" required/>
                            <CustomInput v-model="form.provinsi" label="Provinsi" :error="form.errors.provinsi" required/>
                            <CustomInput v-model="form.kota" label="Kota" :error="form.errors.kota" required/>
                            <CustomInput v-model="form.kecamatan" label="Kecamatan" :error="form.errors.kecamatan" required/>
                            <CustomInput v-model="form.kelurahan" label="Kelurahan"/>
                        </div>
                        <CustomInput v-model="form.alamat_lengkap" label="Alamat Lengkap" :error="form.errors.alamat_lengkap" required/>

                        <div class="pt-2 flex-row justify-items-start">
                            <CustomCheckbox v-model="form.is_default" label="Jadikan Alamat Utama" color="success"/>
                            <p class="mt-2 ml-9 text-xs text-base-content/50">
                                Alamat ini akan digunakan sebagai alamat default saat membuat pesanan.
                            </p>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <CustomButton type="submit" variant="primary" :disabled="form.processing">
                                {{ form.id_alamat ? 'Perbarui Alamat' : 'Tambah Alamat' }}
                            </CustomButton>
                            <CustomButton v-if="form.id_alamat" type="button" variant="secondary" @click="resetForm">
                                Batal Edit
                            </CustomButton>
                        </div>
                    </form>
                </div>

                <!-- LIST -->
                <div class="p-8 border rounded-xl bg-base-100 border-base-300 shadow-lg">
                    <h2 class="mb-6 text-xs font-black tracking-[0.2em] uppercase text-base-content/50">
                        Daftar Alamat
                    </h2>

                    <div v-if="customer.alamat.length" class="space-y-4">
                        <div
                            v-for="alamat in customer.alamat"
                            :key="alamat.id_alamat"
                            class="p-5 border rounded-lg border-base-300"
                        >
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-black">
                                            {{ alamat.label || 'Alamat' }}
                                        </span>

                                        <span v-if="alamat.is_default" class="badge badge-success badge-sm">
                                            Utama
                                        </span>
                                    </div>
                                    <p class="mt-2 font-bold">{{ alamat.nama_penerima }}</p>
                                    <p>{{ alamat.no_hp }}</p>
                                    <p class="mt-2 text-sm">{{ alamat.alamat_lengkap }}</p>
                                    <p class="text-sm text-base-content/60">
                                        {{ alamat.kelurahan }},
                                        {{ alamat.kecamatan }},
                                        {{ alamat.kota }},
                                        {{ alamat.provinsi }}
                                        {{ alamat.kode_pos }}
                                    </p>
                                </div>

                                <div class="flex gap-2">
                                    <button @click="editAlamat(alamat)" class="btn btn-info btn-sm">
                                        Edit
                                    </button>

                                    <button @click="hapusAlamat(alamat)" class="btn btn-error btn-sm">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-10 text-center text-base-content/50">
                        Belum ada alamat tersimpan.
                    </div>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
