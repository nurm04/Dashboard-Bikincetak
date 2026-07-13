<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/CustomInput.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomSelect from '@/Components/CustomSelect.vue';
import CustomCheckbox from '@/Components/CustomCheckbox.vue';

const props = defineProps({
    customer: Object
});

// State untuk menyimpan masing-masing list daerah
const listProvinsi = ref([]);
const listKota = ref([]);
const listKecamatan = ref([]);

const form = useForm({
    id_alamat: null,
    label: '',
    nama_penerima: '',
    no_hp: '',

    provinsi_id: '',
    kota_id: '',
    kecamatan_id: '',

    provinsi: '',
    kota: '',
    kecamatan: '',

    kode_pos: '',
    alamat_lengkap: '',
    latitude: null,
    longitude: null,
    is_default: false,
});

const normalizeData = (data) => {
    let results = [];

    if (Array.isArray(data)) {
        results = data;
    } else if (data?.data && Array.isArray(data.data)) {
        results = data.data;
    } else if (data?.rajaongkir?.results && Array.isArray(data.rajaongkir.results)) {
        results = data.rajaongkir.results;
    }

    if (!Array.isArray(results)) return [];

    return results.map(item => {
        const namaKota = item.city_name ? `${item.type} ${item.city_name}` : null;
        const itemId = item.id || item.province_id || item.city_id || item.subdistrict_id || item.district_id;
        const itemName = item.name || item.province || namaKota || item.city_name || item.district_name || item.subdistrict_name || item.label;

        return {
            id: itemId,
            name: itemName,
            value: itemId,
            label: itemName
        };
    });
};

// --- KUMPULAN FUNGSI FETCH ---

const fetchProvinsi = async () => {
    try {
        const response = await fetch('/shipping/provinces', {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        console.log('Data Provinsi: ', data);
        listProvinsi.value = normalizeData(data);
    } catch (error) {
        console.error('Gagal fetch provinsi', error);
    }
};

const fetchKota = async (provinsiId) => {
    try {
        const response = await fetch(`/shipping/cities/${provinsiId}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        listKota.value = normalizeData(data);
    } catch (error) {
        console.error('Gagal fetch kota', error);
    }
};

const fetchKecamatan = async (kotaId) => {
    try {
        const response = await fetch(`/shipping/districts/${kotaId}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        listKecamatan.value = normalizeData(data);
    } catch (error) {
        console.error('Gagal fetch kecamatan', error);
    }
};

// --- KUMPULAN EVENT HANDLER SAAT DROPDOWN DIPILIH ---

const onProvinsiChange = async (selectedId) => {
    const selected = listProvinsi.value.find(p => String(p.id) === String(selectedId));
    form.provinsi = selected ? selected.name : '';

    form.kota_id = ''; form.kota = ''; listKota.value = [];
    form.kecamatan_id = ''; form.kecamatan = ''; listKecamatan.value = [];

    if (selectedId) await fetchKota(selectedId);
};

const onKotaChange = async (selectedId) => {
    const selected = listKota.value.find(p => String(p.id) === String(selectedId));
    form.kota = selected ? selected.name : '';

    form.kecamatan_id = ''; form.kecamatan = ''; listKecamatan.value = [];

    if (selectedId) await fetchKecamatan(selectedId);
};

const onKecamatanChange = async (selectedId) => {
    const selected = listKecamatan.value.find(p => String(p.id) === String(selectedId));
    form.kecamatan = selected ? selected.name : '';
};

onMounted(() => {
    fetchProvinsi();
});

const resetForm = () => {
    form.reset();
    form.id_alamat = null;
    form.is_default = false;

    listKota.value = [];
    listKecamatan.value = [];
};

const editAlamat = async (alamat) => {
    form.id_alamat = alamat.id_alamat;
    form.label = alamat.label ?? '';
    form.nama_penerima = alamat.nama_penerima ?? '';
    form.no_hp = alamat.no_hp ?? '';
    form.provinsi_id = alamat.provinsi_id ?? '';
    form.kota_id = alamat.kota_id ?? '';
    form.kecamatan_id = alamat.kecamatan_id ?? '';
    form.provinsi = alamat.provinsi ?? '';
    form.kota = alamat.kota ?? '';
    form.kecamatan = alamat.kecamatan ?? '';
    form.kode_pos = alamat.kode_pos ?? '';
    form.alamat_lengkap = alamat.alamat_lengkap ?? '';
    form.latitude = alamat.latitude ?? '';
    form.longitude = alamat.longitude ?? '';
    form.is_default = alamat.is_default;

    if (form.provinsi_id) await fetchKota(form.provinsi_id);
    if (form.kota_id) await fetchKecamatan(form.kota_id);
};

const submit = () => {
    if (form.id_alamat) {
        form.put(route('alamat.update', form.id_alamat), {
            preserveScroll: true,
            onSuccess: () => {
                alertStore.show('Alamat berhasil diperbarui','success');
                resetForm();
            }
        });
        return;
    }

    form.post(route('alamat.store', props.customer.id_customer), {
        preserveScroll: true,
        onSuccess: () => {
            alertStore.show('Alamat berhasil ditambahkan','success');
            resetForm();
        }
    });
};

const hapusAlamat = (alamat) => {
    if (!confirm('Hapus alamat ini?')) return;

    form.delete(route('alamat.destroy', alamat.id_alamat), {
        preserveScroll: true,
        onSuccess: () => {
            alertStore.show('Alamat berhasil dihapus','success');
        }
    });
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
            <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
                <!-- CUSTOMER INFO -->
                <div class="p-6 border shadow-lg rounded-xl bg-base-100 border-base-300">
                    <h1 class="text-2xl font-black">{{ customer.user.name }}</h1>
                    <p class="mt-1 text-base-content/60">{{ customer.user.email }}</p>
                    <p class="mt-1 text-base-content/60">{{ customer.no_hp }}</p>
                </div>

                <!-- FORM -->
                <div class="p-8 border shadow-lg rounded-xl bg-base-100 border-base-300">
                    <h2 class="mb-6 text-xs font-black tracking-[0.2em] uppercase text-base-content/50">
                        {{ form.id_alamat ? 'Edit Alamat' : 'Tambah Alamat' }}
                    </h2>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <CustomInput v-model="form.label" label="Label" placeholder="Rumah / Kantor"/>
                            <CustomInput v-model="form.nama_penerima" label="Nama Penerima" :error="form.errors.nama_penerima" required/>
                            <CustomInput v-model="form.no_hp" label="No HP" :error="form.errors.no_hp" required/>
                            <CustomInput v-model="form.kode_pos" label="Kode Pos" :error="form.errors.kode_pos" required/>
                            <CustomSelect
                                v-model="form.provinsi_id"
                                label="Provinsi"
                                :options="listProvinsi"
                                labelKey="label"
                                valueKey="value"
                                placeholder="Pilih Provinsi..."
                                :error="form.errors.provinsi_id"
                                @update:modelValue="onProvinsiChange"
                            />
                            <CustomSelect
                                v-model="form.kota_id"
                                label="Kota / Kabupaten"
                                :options="listKota"
                                labelKey="label"
                                valueKey="value"
                                placeholder="Pilih Kota..."
                                :error="form.errors.kota_id"
                                :disabled="!form.provinsi_id"
                                @update:modelValue="onKotaChange"
                            />
                            <CustomSelect
                                v-model="form.kecamatan_id"
                                label="Kecamatan"
                                :options="listKecamatan"
                                labelKey="label"
                                valueKey="value"
                                placeholder="Pilih Kecamatan..."
                                :error="form.errors.kecamatan_id"
                                :disabled="!form.kota_id"
                                @update:modelValue="onKecamatanChange"
                            />
                        </div>

                        <CustomInput v-model="form.alamat_lengkap" label="Alamat Lengkap" :error="form.errors.alamat_lengkap" required/>

                        <div class="flex-row pt-2 justify-items-start">
                            <CustomCheckbox v-model="form.is_default" label="Jadikan Alamat Utama" color="success"/>
                            <p class="mt-2 text-xs ml-9 text-base-content/50">
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

                <!-- LIST ALAMAT -->
                <div class="p-8 border shadow-lg rounded-xl bg-base-100 border-base-300">
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
                                        {{ alamat.kecamatan }},
                                        {{ alamat.kota }},
                                        {{ alamat.provinsi }}
                                        {{ alamat.kode_pos }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="editAlamat(alamat)" class="btn btn-info btn-sm">Edit</button>
                                    <button @click="hapusAlamat(alamat)" class="btn btn-error btn-sm">Hapus</button>
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
