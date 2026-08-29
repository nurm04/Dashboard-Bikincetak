<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomTextarea from '@/Components/Form/CustomTextarea.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import { alertStore } from '@/Utils/alertStore';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    sku: Object,
    produk: Object
});

const form = useForm({
    nama_sku: props.sku.nama_sku || '',
    deskripsi: props.sku.deskripsi || '',
    tipe_kalkulasi: props.sku.tipe_kalkulasi || 'standard',
    satuan: props.sku.satuan || '',
    minimum_pesan: props.sku.minimum_pesan || 1,
    kelipatan_pesan: props.sku.kelipatan_pesan || 1, // 👇 TAMBAHAN FIELD KELIPATAN
    harga: props.sku.harga || 0,
    gambar: null, // Akan menampung Array of Files
});

const tipeKalkulasiOptions = [
    { value: 'standard', label: 'Standard (Pcs)' },
    { value: 'cetak_meteran', label: 'Cetak Meteran (P x L)' },
    { value: 'cetak_buku', label: 'Cetak Buku (Hal. & Sisi)' },
];

// ==============================================================================
// 🌟 LOGIC MULTIPLE UPLOAD GAMBAR 🌟
// ==============================================================================
const imagePreviews = ref([]);
const fileInput = ref(null);

const handleFileChange = (e) => {
    const files = e.target.files;

    if (files && files.length > 0) {
        const newFiles = Array.from(files);

        // Gabungkan dengan gambar yang sudah dipilih sebelumnya
        if (form.gambar) {
            form.gambar = [...form.gambar, ...newFiles];
        } else {
            form.gambar = [...newFiles];
        }

        // Bikin URL untuk preview
        const newPreviews = newFiles.map(file => URL.createObjectURL(file));
        imagePreviews.value = [...imagePreviews.value, ...newPreviews];

        // Reset nilai input file agar bisa pilih file yang sama lagi kalau dihapus
        e.target.value = '';
    }
};

const clearSelection = () => {
    form.gambar = null;
    imagePreviews.value = [];
    if (fileInput.value) fileInput.value.value = '';
};

const removeImage = (index) => {
    form.gambar.splice(index, 1);
    imagePreviews.value.splice(index, 1);

    if (form.gambar.length === 0) {
        clearSelection();
    }
};

// ==============================================================================
// 🌟 SUBMIT FORM 🌟
// ==============================================================================
const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('sku.update', props.sku.id_sku), {
        onSuccess: () => alertStore.show('Data SKU berhasil diperbarui!', 'success'),
        onError: () => alertStore.show('Ada kesalahan saat menyimpan data', 'error')
    });
};
</script>

<template>
    <Head :title="`Edit SKU ${sku.id_sku}`" />

    <StafLayout>
        <template #header>
            <div class="flex items-center w-full gap-4">
                <Link :href="route('produk.detailSku', sku.id_produk)" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                    <ArrowLeft class="w-4 h-4" />
                </Link>
                <h2 class="text-xl font-semibold leading-tight uppercase text-base-content">
                    Edit SKU: {{ sku.id_sku }}
                </h2>
            </div>
        </template>

        <div class="max-w-4xl px-4 py-8 mx-auto sm:px-6 lg:px-8">
            <div class="p-6 space-y-6 border shadow-xl bg-base-100 rounded-2xl border-base-300 md:p-10">

                <div class="mb-4">
                    <h3 class="text-xl font-black tracking-tight uppercase">Detail Varian</h3>
                    <p class="text-sm opacity-50">Ubah detail spesifikasi dasar untuk SKU ini.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <CustomInput v-model="form.nama_sku" label="Nama SKU Lengkap" :error="form.errors.nama_sku" readonly />
                    </div>

                    <div class="md:col-span-2">
                        <CustomSelect
                            v-model="form.tipe_kalkulasi"
                            label="Tipe Kalkulasi Harga"
                            :options="tipeKalkulasiOptions"
                            valueKey="value" labelKey="label"
                            :error="form.errors.tipe_kalkulasi"
                        />
                    </div>

                    <!-- MINIMAL PESAN & KELIPATAN BERSEBELAHAN -->
                    <CustomInput v-model="form.minimum_pesan" type="number" label="Minimal Pesan" :error="form.errors.minimum_pesan" />
                    <CustomInput v-model="form.kelipatan_pesan" type="number" label="Kelipatan Pesanan" :error="form.errors.kelipatan_pesan" />

                    <div class="md:col-span-2">
                        <CustomInput v-model="form.satuan" label="Satuan (Pcs/Lbr)" :error="form.errors.satuan" />
                    </div>

                    <div class="md:col-span-2">
                        <CustomInput v-model="form.harga" type="number" label="Harga Dasar Awal (Rp)" :error="form.errors.harga" />
                    </div>

                    <div class="md:col-span-2">
                        <CustomTextarea
                            v-model="form.deskripsi"
                            label="Deskripsi / Keterangan (Muncul di Frontstore)"
                            :error="form.errors.deskripsi"
                            rows="6"
                        />
                    </div>

                    <!-- ============================================================================== -->
                    <!-- 🌟 AREA UPLOAD MULTIPLE GAMBAR 🌟 -->
                    <!-- ============================================================================== -->
                    <div class="w-full pt-4 border-t md:col-span-2 form-control border-base-300">
                        <label class="ml-1 label">
                            <span class="label-text font-black uppercase text-[10px] tracking-widest text-base-content/70">
                                Gambar Spesifik SKU (Bisa Pilih Banyak)
                            </span>
                            <button
                                v-if="imagePreviews.length > 0"
                                type="button"
                                @click="clearSelection"
                                class="text-[10px] font-bold text-error hover:underline uppercase tracking-wider"
                            >
                                Hapus Semua
                            </button>
                        </label>

                        <div class="relative w-full overflow-hidden transition-all border-2 border-dashed rounded-2xl bg-base-200 border-base-300 hover:border-primary hover:bg-base-200/50 group">
                            <input
                                ref="fileInput"
                                type="file"
                                multiple
                                @change="handleFileChange"
                                class="absolute inset-0 z-40 w-full h-full opacity-0 cursor-pointer"
                                accept="image/*"
                            />
                            <div class="flex flex-col items-center justify-center p-8 text-center pointer-events-none">
                                <svg class="w-10 h-10 mb-3 transition-colors text-base-content/30 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <p class="text-sm font-bold text-base-content/70">
                                    <span class="text-primary">Klik untuk unggah</span> atau seret file ke sini
                                </p>
                                <p class="mt-1 text-xs font-medium text-base-content/40">PNG, JPG, JPEG, WEBP (Maks. 2MB)</p>
                            </div>
                        </div>
                        <p v-if="form.errors.gambar" class="mt-2 ml-1 text-[10px] font-bold text-error uppercase tracking-wider">
                            {{ form.errors.gambar }}
                        </p>
                    </div>

                    <!-- PREVIEW GAMBAR BARU -->
                    <div v-if="imagePreviews.length > 0" class="p-4 border md:col-span-2 border-primary/20 bg-primary/5 rounded-2xl">
                        <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-3">Gambar Siap Diunggah:</p>
                        <div class="flex flex-wrap gap-3">
                            <div v-for="(url, idx) in imagePreviews" :key="idx" class="relative w-20 h-20 overflow-hidden border shadow-sm rounded-xl border-primary/30 group">
                                <img :src="url" class="block object-cover w-full h-full" alt="Preview Baru" />

                                <button
                                    type="button"
                                    @click.prevent="removeImage(idx)"
                                    class="absolute z-50 flex items-center justify-center w-6 h-6 text-white transition-all duration-200 scale-75 rounded-full shadow-md opacity-0 bg-error top-1 right-1 group-hover:opacity-100 group-hover:scale-100 hover:bg-red-600"
                                    title="Hapus gambar ini"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- PREVIEW GAMBAR LAMA DI DATABASE -->
                    <div v-else-if="props.sku?.gambar && props.sku?.gambar?.length" class="p-4 border md:col-span-2 rounded-2xl bg-base-200/50 border-base-300">
                        <p class="text-[10px] font-black uppercase tracking-widest text-base-content/50 mb-3">Gambar Tersimpan Saat Ini:</p>
                        <div class="flex flex-wrap gap-3">
                            <template v-if="Array.isArray(props.sku.gambar)">
                                <div v-for="(img, idx) in props.sku.gambar" :key="idx" class="relative w-20 h-20 overflow-hidden border shadow-sm rounded-xl border-base-300">
                                    <img :src="`/storage/${img}`" class="block object-cover w-full h-full" alt="Gambar Tersimpan" />
                                </div>
                            </template>
                            <template v-else-if="typeof props.sku.gambar === 'string' && props.sku.gambar.startsWith('[')">
                                <div v-for="(img, idx) in JSON.parse(props.sku.gambar)" :key="idx" class="relative w-20 h-20 overflow-hidden border shadow-sm rounded-xl border-base-300">
                                    <img :src="`/storage/${img}`" class="block object-cover w-full h-full" alt="Gambar Tersimpan" />
                                </div>
                            </template>
                            <template v-else>
                                <div class="relative w-20 h-20 overflow-hidden border shadow-sm rounded-xl border-base-300">
                                    <img :src="`/storage/${props.sku.gambar}`" class="block object-cover w-full h-full" alt="Gambar Tersimpan" />
                                </div>
                            </template>
                        </div>
                    </div>

                </div>

                <div class="flex flex-col gap-4 pt-8 mt-4 border-t border-base-300 sm:flex-row">
                    <CustomButton variant="primary" class="w-full px-10 py-4 font-black tracking-widest uppercase sm:w-auto rounded-xl" @click="submit" :disabled="form.processing">
                        Simpan Perubahan
                    </CustomButton>
                    <CustomButton variant="secondary" type="button" class="w-full px-10 py-4 font-bold tracking-widest uppercase sm:w-auto rounded-xl" @click="() => window.history.back()">
                        Batal
                    </CustomButton>
                </div>

            </div>
        </div>
    </StafLayout>
</template>
