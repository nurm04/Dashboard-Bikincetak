<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomTextarea from '@/Components/Form/CustomTextarea.vue';
import CustomTable from '@/Components/CustomTable.vue'; // 👈 IMPORT
import CustomTableAction from '@/Components/CustomTableAction.vue'; // 👈 IMPORT
import { ArrowLeft, FileText, Plus, Edit, Trash2, CheckCircle2, XCircle } from 'lucide-vue-next';
import { alertStore } from '@/Utils/alertStore';

const props = defineProps({
    halaman_statis: Array
});

// State untuk Modal Form
const isModalOpen = ref(false);
const modalMode = ref('tambah'); // 'tambah' atau 'edit'
const selectedId = ref(null);

const form = useForm({
    judul: '',
    tipe: '',
    konten: '',
    is_active: true
});

const openModal = (mode, item = null) => {
    modalMode.value = mode;
    form.clearErrors();

    if (mode === 'edit' && item) {
        selectedId.value = item.id;
        form.judul = item.judul;
        form.tipe = item.tipe;
        form.konten = item.konten;
        form.is_active = item.is_active === 1 || item.is_active === true;
    } else {
        selectedId.value = null;
        form.reset();
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    if (modalMode.value === 'tambah') {
        form.post(route('tampilan-web.halaman-statis.store'), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                alertStore.show('Halaman Statis berhasil ditambahkan!', 'success');
            }
        });
    } else {
        form.put(route('tampilan-web.halaman-statis.update', selectedId.value), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                alertStore.show('Halaman Statis berhasil diperbarui!', 'success');
            }
        });
    }
};

const hapus = (id) => {
    if (confirm('Yakin ingin menghapus halaman statis ini? Data yang dihapus tidak bisa dikembalikan.')) {
        router.delete(route('tampilan-web.halaman-statis.destroy', id), {
            preserveScroll: true,
            onSuccess: () => alertStore.show('Halaman Statis berhasil dihapus!', 'success')
        });
    }
};

// 👇 Siapkan Header untuk CustomTable
const tableHeaders = ['Judul Halaman', 'Tipe / Slug', 'Status', 'Aksi'];
</script>

<template>
    <Head title="Halaman Statis" />
    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('tampilan-web.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h2 class="text-xl font-semibold leading-tight text-base-content">Halaman Statis</h2>
                        <p class="mt-1 text-sm text-base-content/60">Kelola teks halaman informasi seperti FAQ, Syarat, dan Profil.</p>
                    </div>
                </div>
                <button type="button" @click="openModal('tambah')" class="btn btn-primary btn-sm rounded-xl">
                    <Plus class="w-4 h-4" /> Tambah Banner
                </button>
            </div>
        </template>

        <div class="max-w-6xl px-4 py-8 mx-auto sm:px-6 lg:px-8">
            <!-- 👇 GUNAKAN CUSTOM TABLE 👇 -->
            <CustomTable :headers="tableHeaders">
                <tr v-for="item in halaman_statis" :key="item.id">
                    <td class="w-1/3">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-primary/10 text-primary shrink-0">
                                <FileText class="w-5 h-5" />
                            </div>
                            <div>
                                <p class="font-bold text-base-content">{{ item.judul }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="text-xs font-bold uppercase text-base-content/70">{{ item.tipe }}</p>
                        <p class="text-[10px] text-base-content/40 mt-0.5">/{{ item.slug }}</p>
                    </td>
                    <td>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold uppercase rounded-lg"
                             :class="item.is_active ? 'bg-success/10 text-success' : 'bg-base-300/50 text-base-content/50'">
                            <CheckCircle2 v-if="item.is_active" class="w-3.5 h-3.5" />
                            <XCircle v-else class="w-3.5 h-3.5" />
                            {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                        </div>
                    </td>
                    <td class="w-20 text-center">
                        <!-- 👇 GUNAKAN CUSTOM TABLE ACTION 👇 -->
                        <CustomTableAction>
                            <button @click="openModal('edit', item)" class="flex items-center w-full gap-3 px-4 py-2.5 text-sm font-medium text-left transition-colors hover:bg-base-200 hover:text-primary">
                                <Edit class="w-4 h-4 opacity-70" /> Edit Halaman
                            </button>
                            <button @click="hapus(item.id)" class="flex items-center w-full gap-3 px-4 py-2.5 text-sm font-medium text-left transition-colors text-error hover:bg-error/10">
                                <Trash2 class="w-4 h-4 opacity-70" /> Hapus
                            </button>
                        </CustomTableAction>
                    </td>
                </tr>
                <tr v-if="!halaman_statis || halaman_statis.length === 0">
                    <td colspan="4" class="px-6 py-12 text-sm font-medium text-center opacity-50">
                        Belum ada halaman statis yang dibuat.
                    </td>
                </tr>
            </CustomTable>
        </div>

        <!-- MODAL FORM -->
        <dialog class="modal modal-bottom sm:modal-middle" :class="{ 'modal-open': isModalOpen }">
            <div class="modal-box sm:max-w-2xl bg-base-100 rounded-t-3xl sm:rounded-3xl">
                <h3 class="flex items-center gap-2 mb-6 text-lg font-black tracking-tight text-base-content">
                    <FileText class="w-5 h-5 text-primary" />
                    {{ modalMode === 'tambah' ? 'Tambah Halaman Baru' : 'Edit Halaman' }}
                </h3>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <CustomInput
                            v-model="form.judul"
                            label="Judul Halaman"
                            placeholder="Contoh: Syarat & Ketentuan"
                            :error="form.errors.judul"
                            required
                        />
                        <CustomInput
                            v-model="form.tipe"
                            label="Tipe Halaman (Kategori)"
                            placeholder="Contoh: syarat, faq, profil"
                            :error="form.errors.tipe"
                            required
                        />
                    </div>

                    <div class="w-full">
                        <div class="flex items-center justify-between mb-1 ml-1">
                            <label class="text-xs font-bold text-base-content/70">Konten Halaman</label>
                            <span class="text-[9px] font-bold text-primary/60 uppercase tracking-widest">Bisa pakai Tag HTML</span>
                        </div>
                        <CustomTextarea
                            v-model="form.konten"
                            placeholder="Tulis konten halaman di sini... (Anda bisa menggunakan tag HTML seperti <b>, <i>, <br>)"
                            :rows="10"
                            :error="form.errors.konten"
                            required
                        />
                    </div>

                    <div class="flex items-center gap-3 p-4 border rounded-xl bg-base-200/50 border-base-300">
                        <input type="checkbox" v-model="form.is_active" class="toggle toggle-primary toggle-sm" />
                        <div>
                            <p class="text-sm font-bold leading-none text-base-content">Halaman Aktif</p>
                            <p class="text-[10px] mt-1 font-medium opacity-60">Matikan jika halaman belum siap dipublikasi ke website.</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t modal-action border-base-300">
                        <button type="button" @click="closeModal" class="btn btn-ghost rounded-xl">Batal</button>
                        <CustomButton type="submit" variant="primary" class="rounded-xl min-w-30" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Halaman' }}
                        </CustomButton>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button @click="closeModal">close</button>
            </form>
        </dialog>
    </StafLayout>
</template>
