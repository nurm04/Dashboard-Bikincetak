<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomTextarea from '@/Components/Form/CustomTextarea.vue';
import CustomTableForm from '@/Components/CustomTableForm.vue';
import CustomInputFile from '@/Components/Form/CustomInputFile.vue';
import { ArrowLeft, Save, Globe, Phone, CreditCard, Image as ImageIcon } from 'lucide-vue-next';
import { alertStore } from '@/Utils/alertStore';

const props = defineProps({
    pengaturan: Object
});

const getVal = (key, defaultVal) => {
    if (!props.pengaturan || !props.pengaturan[key]) return defaultVal;
    return props.pengaturan[key].nilai_parsed ?? props.pengaturan[key].nilai ?? defaultVal;
};

// Form State
const form = useForm({
    // Tab 1: Identitas
    nama_website: getVal('nama_website', ''),
    deskripsi_singkat: getVal('deskripsi_singkat', ''),
    keyword_seo: getVal('keyword_seo', ''),

    // Tab 1: Logo
    logo_utama: {
        tipe_file: 'upload',
        file: null,
        link_file: ''
    },

    // Tab 2: Kontak & Sosmed
    informasi_lokasi: getVal('informasi_lokasi', { email: '', alamat_lengkap: '', link_gmaps: '' }),
    daftar_whatsapp: getVal('daftar_whatsapp', []),
    daftar_sosmed: getVal('daftar_sosmed', []),

    // Tab 3: Pembayaran
    teks_info_pembayaran: getVal('teks_info_pembayaran', ''),
    metode_pembayaran: getVal('metode_pembayaran', []),
});

const activeTab = ref('identitas');

// Logic Preview Logo Utama Web
const logoPreviewUrl = computed(() => {
    const fileObj = form.logo_utama.file;
    if (fileObj instanceof File) return URL.createObjectURL(fileObj);

    const oldLogo = getVal('logo_utama', '');
    if (oldLogo) return `/storage/${oldLogo}`;

    return null;
});

// 👇 FUNGSI BARU: Upload Logo Pembayaran di dalam Tabel Array 👇
const handlePaymentIconUpload = (e, row) => {
    const file = e.target.files[0];
    if (file) {
        row.icon_file = file; // Simpan file asli buat dikirim ke Laravel
        row.icon_preview = URL.createObjectURL(file); // Buat URL lokal untuk preview mata
    }
};

const submit = () => {
    form.transform((data) => {
        const payload = { ...data };

        // Ekstrak logo_utama
        if (payload.logo_utama && payload.logo_utama.file instanceof File) {
            payload.logo_utama = payload.logo_utama.file;
        } else {
            delete payload.logo_utama;
        }

        // Inertia otomatis ngurusin nested file di metode_pembayaran kalau ada isinya.
        return payload;
    }).post(route('tampilan-web.pengaturan.update'), {
        preserveScroll: true,
        onSuccess: () => alertStore.show('Pengaturan Umum berhasil diperbarui!', 'success')
    });
};
</script>

<template>
    <Head title="Pengaturan Umum Web" />
    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('tampilan-web.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h2 class="text-xl font-semibold leading-tight text-base-content">Pengaturan Umum Web</h2>
                        <p class="mt-1 text-sm text-base-content/60">Atur Identitas, SEO, Kontak, dan Metode Pembayaran website.</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-6xl px-4 py-8 mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row">

                <!-- SIDEBAR TABS -->
                <div class="w-full lg:w-64 shrink-0">
                    <div class="flex flex-row gap-1 p-2 overflow-x-auto border bg-base-100/50 rounded-3xl border-base-300 lg:flex-col">
                        <button @click="activeTab = 'identitas'" :class="['flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-sm transition-all whitespace-nowrap', activeTab === 'identitas' ? 'bg-primary text-primary-content shadow-md' : 'text-base-content/60 hover:bg-base-200']"><Globe class="w-4 h-4" /> Identitas & SEO</button>
                        <button @click="activeTab = 'kontak'" :class="['flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-sm transition-all whitespace-nowrap', activeTab === 'kontak' ? 'bg-primary text-primary-content shadow-md' : 'text-base-content/60 hover:bg-base-200']"><Phone class="w-4 h-4" /> Kontak & Sosmed</button>
                        <button @click="activeTab = 'pembayaran'" :class="['flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-sm transition-all whitespace-nowrap', activeTab === 'pembayaran' ? 'bg-primary text-primary-content shadow-md' : 'text-base-content/60 hover:bg-base-200']"><CreditCard class="w-4 h-4" /> Pembayaran</button>
                    </div>
                </div>

                <!-- KONTEN FORM -->
                <div class="flex-1 min-w-0">
                    <div class="p-6 border shadow-xl sm:p-8 rounded-3xl bg-base-100 border-base-300">
                        <form @submit.prevent="submit" class="space-y-8">

                            <!-- TAB 1: IDENTITAS & SEO -->
                            <div v-show="activeTab === 'identitas'" class="space-y-6 animate-fade-in">
                                <h3 class="flex items-center gap-2 pb-2 text-sm font-black tracking-widest uppercase border-b border-base-300 text-primary">
                                    <Globe class="w-4 h-4" /> Identitas Website
                                </h3>

                                <div class="grid grid-cols-1 gap-6">
                                    <CustomInput v-model="form.nama_website" label="Nama Website (Title SEO)" placeholder="BikinCetak - Platform..." required />
                                    <CustomTextarea v-model="form.deskripsi_singkat" label="Deskripsi Singkat (Meta Description)" placeholder="Tulis deksripsi web untuk muncul di Google..." rows="3" required />
                                    <CustomInput v-model="form.keyword_seo" label="Kata Kunci SEO (Keywords)" placeholder="percetakan online, digital printing..." />
                                </div>

                                <div class="w-full mt-2">
                                    <label class="block mb-2 ml-1 text-xs font-bold text-base-content/70">Logo Utama Web</label>
                                    <div class="flex flex-col items-start gap-4 sm:flex-row">
                                        <div class="flex items-center justify-center w-32 h-32 p-3 bg-white border shadow-sm shrink-0 rounded-2xl border-base-300">
                                            <img v-if="logoPreviewUrl" :src="logoPreviewUrl" class="object-contain w-full h-full" alt="Logo Web" />
                                            <ImageIcon v-else class="w-8 h-8 text-base-content/20" />
                                        </div>
                                        <div class="w-full max-w-sm">
                                            <CustomInputFile
                                                v-model="form.logo_utama"
                                                label="Upload Logo Baru (PNG Transparan)"
                                                :showTipeFile="false"
                                            />
                                            <p class="mt-2 text-[10px] ml-2 opacity-50 font-medium">Kosongkan jika tidak ingin mengubah logo saat ini.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: KONTAK & SOSMED -->
                            <div v-show="activeTab === 'kontak'" class="space-y-8 animate-fade-in">
                                <h3 class="flex items-center gap-2 pb-2 text-sm font-black tracking-widest uppercase border-b border-base-300 text-primary">
                                    <Phone class="w-4 h-4" /> Informasi Lokasi & Kontak
                                </h3>

                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <CustomInput v-model="form.informasi_lokasi.email" type="email" label="Email Perusahaan" placeholder="info@bikincetak.co.id" />
                                    <CustomInput v-model="form.informasi_lokasi.link_gmaps" label="Link Google Maps" placeholder="https://maps.google.com/..." />
                                </div>
                                <CustomTextarea v-model="form.informasi_lokasi.alamat_lengkap" label="Alamat Lengkap (Tampil di Footer)" placeholder="Jl. Percetakan Negara..." rows="2" />

                                <div class="divider opacity-20"></div>

                                <CustomTableForm
                                    v-model="form.daftar_whatsapp"
                                    :headers="['Nama CS', 'Nomor WA', 'Pesan Default (Opsional)']"
                                    label="Daftar WhatsApp CS"
                                    @add="form.daftar_whatsapp.push({ id: Date.now(), nama: '', nomor: '', pesan_default: '', is_active: true })"
                                >
                                    <template #row="{ row }">
                                        <td class="px-2 py-2 w-[25%]"><input v-model="row.nama" type="text" class="w-full text-xs font-bold bg-transparent" placeholder="Nama CS"/></td>
                                        <td class="px-2 py-2 w-[25%]"><input v-model="row.nomor" type="text" class="w-full text-xs bg-transparent" placeholder="628..."/></td>
                                        <td class="px-2 py-2 w-[50%]"><input v-model="row.pesan_default" type="text" class="w-full text-xs bg-transparent" placeholder="Halo Admin..."/></td>
                                    </template>
                                </CustomTableForm>

                                <CustomTableForm
                                    v-model="form.daftar_sosmed"
                                    :headers="['Platform', 'URL Sosmed', 'Ikon']"
                                    label="Daftar Sosial Media"
                                    @add="form.daftar_sosmed.push({ platform: '', url: '', icon: '', is_active: true })"
                                >
                                    <template #row="{ row }">
                                        <td class="px-2 py-2 w-[30%]"><input v-model="row.platform" type="text" class="w-full text-xs font-bold bg-transparent" placeholder="Instagram"/></td>
                                        <td class="px-2 py-2 w-[50%]"><input v-model="row.url" type="text" class="w-full text-xs bg-transparent" placeholder="https://..."/></td>
                                        <td class="px-2 py-2 w-[20%]"><input v-model="row.icon" type="text" class="w-full text-xs bg-transparent" placeholder="instagram / tiktok"/></td>
                                    </template>
                                </CustomTableForm>
                            </div>

                            <!-- TAB 3: PEMBAYARAN -->
                            <div v-show="activeTab === 'pembayaran'" class="space-y-8 animate-fade-in">
                                <h3 class="flex items-center gap-2 pb-2 text-sm font-black tracking-widest uppercase border-b border-base-300 text-primary">
                                    <CreditCard class="w-4 h-4" /> Informasi Pembayaran
                                </h3>

                                <CustomTextarea v-model="form.teks_info_pembayaran" label="Teks Info Pembayaran (Footer)" placeholder="Menerima pembayaran via BCA dan QRIS..." rows="2" />

                                <CustomTableForm
                                    v-model="form.metode_pembayaran"
                                    :headers="['Nama Metode / Bank', 'No Rekening', 'Atas Nama', 'Upload Icon / Logo']"
                                    label="Daftar Metode Pembayaran"
                                    @add="form.metode_pembayaran.push({ id: Date.now(), nama_metode: '', no_rekening: '', atas_nama: '', icon_url: '', icon_file: null, icon_preview: null, is_active: true })"
                                >
                                    <template #row="{ row }">
                                        <td class="px-2 py-2 w-[20%]"><input v-model="row.nama_metode" type="text" class="w-full text-xs font-bold bg-transparent" placeholder="Bank BCA"/></td>
                                        <td class="px-2 py-2 w-[20%]"><input v-model="row.no_rekening" type="text" class="w-full text-xs bg-transparent" placeholder="123456... / -"/></td>
                                        <td class="px-2 py-2 w-[20%]"><input v-model="row.atas_nama" type="text" class="w-full text-xs bg-transparent" placeholder="A.N Perusahaan"/></td>

                                        <!-- 👇 UBAH INPUT TEKS JADI KOMPONEN UPLOAD + PREVIEW 👇 -->
                                        <td class="px-2 py-2 w-[40%]">
                                            <div class="flex items-center gap-3">
                                                <!-- Kotak Preview Kecil -->
                                                <div class="flex items-center justify-center w-8 h-8 p-1 overflow-hidden bg-white border rounded-lg shrink-0 border-base-300">
                                                    <img v-if="row.icon_preview" :src="row.icon_preview" class="object-contain w-full h-full" alt="Baru"/>
                                                    <img v-else-if="row.icon_url" :src="row.icon_url.startsWith('/') || row.icon_url.startsWith('http') ? row.icon_url : '/storage/' + row.icon_url" class="object-contain w-full h-full" alt="Lama" />
                                                    <ImageIcon v-else class="w-4 h-4 text-base-content/30" />
                                                </div>

                                                <!-- Input File -->
                                                <div class="flex-1 min-w-0">
                                                    <input
                                                        type="file"
                                                        @change="(e) => handlePaymentIconUpload(e, row)"
                                                        accept="image/png, image/jpeg, image/jpg, image/webp"
                                                        class="w-full text-[10px] file-input file-input-bordered file-input-xs file-input-primary bg-base-200"
                                                    />
                                                </div>
                                            </div>
                                        </td>
                                    </template>
                                </CustomTableForm>
                            </div>

                            <!-- TOMBOL SUBMIT -->
                            <div class="flex justify-end pt-6 mt-6 border-t border-base-300">
                                <CustomButton type="submit" variant="primary" class="w-full py-4 sm:w-auto min-w-50 rounded-2xl" :disabled="form.processing">
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Semua Pengaturan' }}
                                </CustomButton>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </StafLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
