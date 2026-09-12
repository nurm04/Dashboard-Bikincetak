<script setup>
import { ref, watch } from 'vue'; // 👈 UBAH: Tambah import 'watch'
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import { ArrowLeft, GripVertical, Image as ImageIcon, Plus, Edit, Trash2 } from 'lucide-vue-next';
import { alertStore } from '@/Utils/alertStore';

const props = defineProps({
    banners: Array
});

// ==========================================
// FORM BULK UPDATE (Drag & Drop + Status)
// ==========================================
const formSync = useForm({
    banners: props.banners.map((b, index) => ({
        id: b.id,
        judul: b.judul,
        gambar_url: b.gambar_url,
        link_tujuan: b.link_tujuan,
        urutan: b.urutan === 0 ? index + 1 : b.urutan,
        is_active: b.is_active == 1 || b.is_active === true,
    }))
});

// 👇 TAMBAHAN: Watcher ajaib biar tabel responsif realtime 👇
watch(() => props.banners, (newBanners) => {
    formSync.banners = newBanners.map((b, index) => ({
        id: b.id,
        judul: b.judul,
        gambar_url: b.gambar_url,
        link_tujuan: b.link_tujuan,
        urutan: b.urutan === 0 ? index + 1 : b.urutan,
        is_active: b.is_active == 1 || b.is_active === true,
    }));
}, { deep: true });
// 👆 SAMPAI SINI 👆

const draggingIndex = ref(null);

const onDragStart = (index) => { draggingIndex.value = index; };
const onDragEnter = (index) => {
    if (draggingIndex.value === index || draggingIndex.value === null) return;
    const draggedItem = formSync.banners[draggingIndex.value];
    formSync.banners.splice(draggingIndex.value, 1);
    formSync.banners.splice(index, 0, draggedItem);
    draggingIndex.value = index;
    formSync.banners.forEach((b, idx) => { b.urutan = idx + 1; });
};
const onDragEnd = () => { draggingIndex.value = null; };

const submitSync = () => {
    formSync.post(route('tampilan-web.banner.sync'), {
        onSuccess: () => alertStore.show('Urutan & Tampilan Banner berhasil disimpan!', 'success'),
    });
};

// ==========================================
// MODAL CRUD (Tambah & Edit Banner)
// ==========================================
const isModalOpen = ref(false);
const modalMode = ref('add'); // 'add' atau 'edit'
const selectedId = ref(null);
const imagePreview = ref(null);

const formModal = useForm({
    judul: '',
    link_tujuan: '',
    gambar: null,
});

const openModalAdd = () => {
    modalMode.value = 'add';
    formModal.reset();
    formModal.clearErrors();
    imagePreview.value = null;
    isModalOpen.value = true;
};

const openModalEdit = (banner) => {
    modalMode.value = 'edit';
    selectedId.value = banner.id;
    formModal.judul = banner.judul;
    formModal.link_tujuan = banner.link_tujuan || '';
    formModal.gambar = null;
    formModal.clearErrors();
    imagePreview.value = `/storage/${banner.gambar_url}`;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    formModal.reset();
};

const onImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        formModal.gambar = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submitModal = () => {
    if (modalMode.value === 'add') {
        formModal.post(route('tampilan-web.banner.store'), {
            onSuccess: () => {
                alertStore.show('Banner berhasil ditambahkan!', 'success');
                closeModal();
                // (router.reload dihapus karena watcher otomatis ngurusin)
            }
        });
    } else {
        formModal.post(route('tampilan-web.banner.update', selectedId.value), {
            onSuccess: () => {
                alertStore.show('Banner berhasil diperbarui!', 'success');
                closeModal();
            }
        });
    }
};

const deleteBanner = (id) => {
    if (confirm('Yakin ingin menghapus banner ini?')) {
        router.delete(route('tampilan-web.banner.destroy', id), {
            onSuccess: () => alertStore.show('Banner berhasil dihapus!', 'success')
        });
    }
};
</script>

<template>
    <Head title="Atur Banner Slider" />
    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('tampilan-web.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h2 class="text-xl font-semibold leading-tight text-base-content">
                            Atur Banner Slider
                        </h2>
                        <p class="mt-1 text-sm text-base-content/60">Tarik baris untuk mengurutkan banner yang tampil di beranda E-commerce.</p>
                    </div>
                </div>
                <button type="button" @click="openModalAdd" class="btn btn-primary btn-sm rounded-xl">
                    <Plus class="w-4 h-4" /> Tambah Banner
                </button>
            </div>
        </template>

        <div class="max-w-5xl px-4 py-8 mx-auto sm:px-6 lg:px-8">
            <div class="p-6 border shadow-xl sm:p-10 rounded-2xl bg-base-100 border-base-300">
                <form @submit.prevent="submitSync" class="space-y-6">

                    <div class="space-y-3">
                        <div class="flex items-center justify-between ml-1">
                            <h2 class="text-xs font-black text-base-content/50 uppercase tracking-[0.2em]">Daftar Banner Web</h2>
                        </div>

                        <div class="overflow-x-auto border border-base-300 rounded-3xl bg-base-200/30">
                            <table class="w-full text-left border-collapse rounded-3xl">
                                <thead class="bg-base-200/50">
                                    <tr>
                                        <th class="w-10 px-3 py-2 text-[10px] font-black text-center text-base-content/40 uppercase tracking-widest whitespace-nowrap first:rounded-tl-3xl">Urutan</th>
                                        <th class="px-3 py-2 text-[10px] font-black text-base-content/40 uppercase tracking-widest whitespace-nowrap">Preview Gambar</th>
                                        <th class="px-3 py-2 text-[10px] font-black text-base-content/40 uppercase tracking-widest whitespace-nowrap">Detail Banner</th>
                                        <th class="w-24 px-3 py-2 text-[10px] font-black text-center text-base-content/40 uppercase tracking-widest whitespace-nowrap">Tampil di Web</th>
                                        <th class="w-20 px-3 py-2 text-[10px] font-black text-center text-base-content/40 uppercase tracking-widest whitespace-nowrap last:rounded-tr-3xl">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-base-300">

                                    <tr
                                        v-for="(row, index) in formSync.banners"
                                        :key="row.id"
                                        class="transition-colors bg-base-100 group hover:bg-base-200/50 cursor-grab active:cursor-grabbing"
                                        :class="{'opacity-50 scale-[0.99] bg-base-200': draggingIndex === index}"
                                        draggable="true"
                                        @dragstart="onDragStart(index)"
                                        @dragenter.prevent="onDragEnter(index)"
                                        @dragover.prevent
                                        @dragend="onDragEnd"
                                    >
                                        <!-- Drag Handle -->
                                        <td class="px-3 py-3 text-center border-r border-base-300/30">
                                            <div class="flex items-center justify-center gap-1 opacity-40 group-hover:opacity-100 text-base-content">
                                                <GripVertical class="w-4 h-4 shrink-0" />
                                                <span class="w-4 text-xs font-black">{{ row.urutan }}</span>
                                            </div>
                                        </td>

                                        <!-- Preview Gambar -->
                                        <td class="w-48 px-4 py-3 align-middle">
                                            <div class="relative w-full h-16 overflow-hidden border rounded-lg bg-base-200 border-base-300">
                                                <img :src="`/storage/${row.gambar_url}`" class="object-cover w-full h-full" alt="Banner Preview" />
                                            </div>
                                        </td>

                                        <!-- Judul & Link -->
                                        <td class="px-4 py-3 align-middle">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-base-content">{{ row.judul }}</span>
                                                <span class="max-w-xs text-xs truncate text-base-content/60">{{ row.link_tujuan || 'Tidak ada link' }}</span>
                                            </div>
                                        </td>

                                        <!-- Toggle Status -->
                                        <td class="px-4 py-3 text-center align-middle border-l border-base-300/30">
                                            <input
                                                type="checkbox"
                                                v-model="formSync.banners[index].is_active"
                                                class="toggle toggle-sm toggle-success"
                                            />
                                        </td>

                                        <!-- Aksi (Edit / Hapus) -->
                                        <td class="px-3 py-3 text-center align-middle border-l border-base-300/30">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button" @click="openModalEdit(row)" class="p-1.5 transition-colors rounded-lg text-primary hover:bg-primary/10">
                                                    <Edit class="w-4 h-4" />
                                                </button>
                                                <button type="button" @click="deleteBanner(row.id)" class="p-1.5 transition-colors rounded-lg text-error hover:bg-error/10">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr v-if="formSync.banners.length === 0">
                                        <td colspan="5" class="px-4 py-8 text-xs italic text-center text-base-content/30">
                                            Belum ada banner yang ditambahkan.
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tombol Aksi Simpan Urutan -->
                    <div class="flex flex-col items-center gap-4 pt-6 mt-6 border-t border-base-300 sm:flex-row">
                        <CustomButton
                            type="submit"
                            variant="primary"
                            class="flex-1 w-full py-4 sm:w-auto rounded-2xl"
                            :disabled="formSync.processing"
                        >
                            Simpan Perubahan Urutan
                        </CustomButton>

                        <CustomButton
                            type="link"
                            :href="route('tampilan-web.index')"
                            variant="secondary"
                            class="w-full py-4 sm:w-auto rounded-2xl"
                        >
                            Batal
                        </CustomButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- 👇 MODAL TAMBAH/EDIT BANNER 👇 -->
        <dialog class="modal z-100" :class="{'modal-open': isModalOpen}">
            <div class="max-w-lg p-0 modal-box rounded-2xl">
                <div class="flex items-center justify-between p-4 border-b sm:p-5 border-base-200 bg-base-50">
                    <h3 class="text-lg font-bold text-base-content">{{ modalMode === 'add' ? 'Tambah Banner Baru' : 'Edit Banner' }}</h3>
                    <button type="button" @click="closeModal" class="btn btn-sm btn-circle btn-ghost text-base-content/40 hover:text-error">✕</button>
                </div>

                <form @submit.prevent="submitModal" class="p-5 space-y-4">
                    <!-- Judul Banner -->
                    <div class="form-control">
                        <label class="mb-1 label"><span class="text-xs font-bold label-text">Judul Banner</span></label>
                        <input type="text" v-model="formModal.judul" placeholder="Contoh: Promo Spesial 17an" class="w-full input input-bordered rounded-xl" />
                        <span v-if="formModal.errors.judul" class="mt-1 text-xs text-error">{{ formModal.errors.judul }}</span>
                    </div>

                    <!-- Link Tujuan -->
                    <div class="form-control">
                        <label class="mb-1 label"><span class="text-xs font-bold label-text">Link Tujuan (Opsional)</span></label>
                        <input type="text" v-model="formModal.link_tujuan" placeholder="Contoh: /katalog?kategori=sticker" class="w-full input input-bordered rounded-xl" />
                        <span v-if="formModal.errors.link_tujuan" class="mt-1 text-xs text-error">{{ formModal.errors.link_tujuan }}</span>
                        <p class="mt-1 text-[10px] opacity-60">URL halaman yang dibuka saat banner diklik.</p>
                    </div>

                    <!-- Upload Gambar -->
                    <div class="form-control">
                        <label class="mb-1 label"><span class="text-xs font-bold label-text">Gambar Banner (Rekomendasi 1200x400px)</span></label>

                        <!-- Preview Box -->
                        <div v-if="imagePreview" class="relative w-full h-32 mb-2 overflow-hidden border rounded-xl border-base-300">
                            <img :src="imagePreview" class="object-cover w-full h-full" alt="Preview" />
                        </div>

                        <input type="file" @change="onImageChange" accept="image/*" class="w-full file-input file-input-bordered file-input-primary rounded-xl" :required="modalMode === 'add'" />
                        <span v-if="formModal.errors.gambar" class="mt-1 text-xs text-error">{{ formModal.errors.gambar }}</span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-2 pt-4 mt-6 border-t border-base-200">
                        <button type="button" @click="closeModal" class="btn btn-ghost rounded-xl">Batal</button>
                        <button type="submit" :disabled="formModal.processing" class="btn btn-primary rounded-xl">
                            {{ formModal.processing ? 'Menyimpan...' : 'Simpan Banner' }}
                        </button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop bg-base-content/50"><button @click="closeModal">close</button></form>
        </dialog>

    </StafLayout>
</template>

<style scoped>
.cursor-grab {
    cursor: -webkit-grab;
    cursor: grab;
}
.active\:cursor-grabbing:active {
    cursor: -webkit-grabbing;
    cursor: grabbing;
}
</style>
