<script setup>
import { ref } from 'vue';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({ produk: Object, kategoris: Array });
const isEdit = !!props.produk;

const form = useForm({
    nama_produk: props.produk?.nama_produk ?? '',
    id_kategori: props.produk?.id_kategori ?? '',
    gambar: null,
});

const imagePreviews = ref([]);
const fileInput = ref(null);

const handleFileChange = (e) => {
    const files = e.target.files;

    if (files && files.length > 0) {
        const newFiles = Array.from(files);

        if (form.gambar) {
            form.gambar = [...form.gambar, ...newFiles];
        } else {
            form.gambar = [...newFiles];
        }

        const newPreviews = newFiles.map(file => URL.createObjectURL(file));
        imagePreviews.value = [...imagePreviews.value, ...newPreviews];
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

const submit = () => {
    if (isEdit) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(route('produk.update', props.produk.id_produk));
    } else {
        form.post(route('produk.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Produk' : 'Tambah Produk'" />
    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('produk.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        {{ isEdit ? 'Edit produk: ' + props.produk.id_produk : 'Registrasi Produk Baru' }}
                    </h2>
                </div>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 border shadow-2xl rounded-2xl bg-base-100 border-base-300">
                    <form @submit.prevent="submit" class="space-y-6">

                        <CustomInput v-model="form.nama_produk" label="Nama Produk" placeholder="Contoh: Print A0 Hot" :error="form.errors.nama_produk" />

                        <CustomSelect v-model="form.id_kategori" label="Kategori" :options="kategoris" labelKey="nama_kategori" valueKey="id_kategori" :error="form.errors.id_kategori" />

                        <div class="w-full form-control">
                            <label class="ml-1 label">
                                <span class="label-text font-black uppercase text-[10px] tracking-widest text-base-content/70">
                                    Gambar Produk (Bisa Pilih Banyak)
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

                        <div v-if="imagePreviews.length > 0" class="p-4 border border-primary/20 bg-primary/5 rounded-2xl">
                            <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-3">Gambar Siap Diunggah:</p>
                            <div class="flex flex-wrap gap-3">
                                <div v-for="(url, idx) in imagePreviews" :key="idx" class="relative w-20 h-20 overflow-hidden border shadow-sm rounded-xl border-primary/30 group">
                                    <img :src="url" class="block object-cover w-full h-full" alt="Preview Baru" />

                                    <button
                                        type="button"
                                        @click.prevent="removeImage(idx)"
                                        class="absolute z-50 flex items-center justify-center w-6 h-6 text-white transition-all duration-200 scale-75 rounded-full opacity-0 bg-error top-1 right-1 group-hover:opacity-100 group-hover:scale-100 hover:bg-red-600 shadow-md"
                                        title="Hapus gambar ini"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="isEdit && props.produk?.gambar?.length" class="p-4 border rounded-2xl bg-base-200/50 border-base-300">
                            <p class="text-[10px] font-black uppercase tracking-widest text-base-content/50 mb-3">Gambar Saat Ini Tersimpan:</p>
                            <div class="flex flex-wrap gap-3">
                                <div v-for="(img, idx) in props.produk.gambar" :key="idx" class="relative w-20 h-20 overflow-hidden border shadow-sm rounded-xl border-base-300">
                                    <img :src="`/storage/${img}`" class="block object-cover w-full h-full" alt="Gambar Tersimpan" />
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-6">
                            <CustomButton type="submit" class="flex-1" :disabled="form.processing">Simpan Produk</CustomButton>
                            <CustomButton type="link" :href="route('produk.index')" variant="secondary">Batal</CustomButton>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
