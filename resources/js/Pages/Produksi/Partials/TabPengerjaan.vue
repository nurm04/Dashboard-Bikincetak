<style scoped>
.custom-scrollbar::-webkit-scrollbar { display: none; }
.custom-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import { Clock, CheckCircle, Paperclip, UploadCloud, Printer, Inbox } from 'lucide-vue-next';

import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomInputFile from '@/Components/Form/CustomInputFile.vue';
import CustomTableAction from '@/Components/CustomTableAction.vue';

const props = defineProps({
    pesananList: Array,
    currentUser: Object,
    currentVendorId: String,
});

const headers = ['ID Pesanan', 'Customer', 'Deadline', 'Status', 'Aksi'];
const headersProses = ['Pelaksana', 'Instruksi / Keterangan', 'Qty', 'Status', 'Aksi'];

const formatTanggal = (tgl) => {
    if (!tgl) return '-';
    const date = new Date(tgl);
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
};
const isDeadlinePassed = (dateStr) => new Date(dateStr) < new Date();
const cleanProductName = (name) => name ? name.replace(/^[A-Za-z]+-\d+-/, '').replace(/-/g, ' ') : '';
const parseAtribut = (str) => { if (!str) return null; if (typeof str === 'object') return str; try { return JSON.parse(str); } catch (e) { return null; } };
const getValidAttributes = (str) => { const p = parseAtribut(str); return (!p || typeof p !== 'object') ? [] : Object.entries(p).filter(([_, v]) => v !== null && v !== undefined && v !== '').map(([k, v]) => ({ key: k, value: v })); };
const getFileDisplay = (item) => {
    if (item.file_desain) {
        let fd = typeof item.file_desain === 'string' ? JSON.parse(item.file_desain || '[]') : item.file_desain;
        if (Array.isArray(fd) && fd.length > 0) return { tipe: 'upload', nilai: fd[0] };
        return typeof fd === 'object' && !Array.isArray(fd) ? fd : null;
    }
    if (item.tipe_file) return { tipe: item.tipe_file, nilai: item.tipe_file === 'link' ? item.link_file : (item.file?.name || 'Kosong') };
    return null;
};
const checkAccess = (schedule) => {
    const role = props.currentUser?.role;
    if (role === 'vendor') return (schedule.tipe_pengerjaan === 'vendor' && schedule.id_vendor === props.currentVendorId) ? 'edit' : 'none';
    const isAdmin = role === 'admin' || role === 'administrator';
    return (schedule.status_pengerjaan === 'selesai' && !isAdmin) ? 'view' : 'edit';
};

// ==========================================
// LOGIC MODAL DETAIL & PROGRESS
// ==========================================
const isDetailModalOpen = ref(false);
const selectedPesananDetail = ref(null);

const openDetailModal = (pesanan) => {
    selectedPesananDetail.value = pesanan;
    isDetailModalOpen.value = true;
};
const closeDetailModal = () => {
    isDetailModalOpen.value = false;
    selectedPesananDetail.value = null;
};

// ==========================================
// LOGIC MODAL UPDATE PROGRESS
// ==========================================
const isUpdateModalOpen = ref(false);
const selectedSchedule = ref(null);
const selectedItemUpdate = ref(null);
const isViewOnly = ref(false);

const updateForm = useForm({ deskripsi_pengerjaan: '', total_tagihan_vendor: null, file_nota: null, hasil_desain: null });
const fileNotaObj = ref({ tipe_file: 'upload', file: null, link_file: '' });
const fileHasilObj = ref({ tipe_file: 'upload', file: null, link_file: '' });

const openUpdateModal = (schedule, item) => {
    const access = checkAccess(schedule);
    if (access === 'none') return alertStore.show('Akses Ditolak!', 'error');
    isViewOnly.value = access === 'view';
    selectedSchedule.value = schedule; selectedItemUpdate.value = item;

    updateForm.deskripsi_pengerjaan = schedule.deskripsi_pengerjaan || '';
    updateForm.total_tagihan_vendor = schedule.total_tagihan_vendor || null;
    updateForm.file_nota = null;
    updateForm.hasil_desain = null;

    fileNotaObj.value = { tipe_file: 'upload', file: schedule.file_nota || null, link_file: '' };
    fileHasilObj.value = { tipe_file: 'upload', file: schedule.file_revisi || null, link_file: '' };
    isUpdateModalOpen.value = true;
};
const closeUpdateModal = () => { isUpdateModalOpen.value = false; updateForm.reset(); };

const submitUpdate = () => {
    updateForm.post(route('produksi.selesaikan', selectedSchedule.value.id), {
        forceFormData: true,
        onSuccess: () => { closeUpdateModal(); alertStore.show('Progress diperbarui!', 'success'); },
        onError: () => alertStore.show('Gagal memperbarui progress. Periksa file Anda.', 'error')
    });
};
</script>

<template>
    <div class="space-y-6">

        <!-- 👇 TABEL LIST PESANAN 👇 -->
        <CustomTable :headers="headers" :pagination="false">
            <tr v-for="pesanan in pesananList" :key="pesanan.id_pesan" class="transition-colors hover:bg-base-200/50">
                <!-- 1. ID Pesanan -->
                <td class="px-4 py-4 font-mono text-xs font-bold whitespace-nowrap text-primary">
                    {{ pesanan.id_pesan }}
                </td>

                <!-- 2. Customer -->
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="font-bold text-base-content">{{ pesanan.customer?.user?.name || 'Walk-in / Umum' }}</div>
                    <div class="text-[10px] text-base-content/50">{{ pesanan.customer?.id_customer || '-' }}</div>
                </td>

                <!-- 3. Deadline -->
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-1.5">
                        <Clock class="w-3.5 h-3.5 opacity-50" />
                        <span class="font-black tracking-tight" :class="isDeadlinePassed(pesanan.waktu_deadline) ? 'text-error' : 'text-base-content'">
                            {{ formatTanggal(pesanan.waktu_deadline) }}
                        </span>
                    </div>
                </td>

                <!-- 4. Status -->
                <td class="px-4 py-4 whitespace-nowrap">
                    <span class="flex items-center gap-1.5 text-[10px] sm:text-xs font-bold px-2.5 py-1 rounded-full border border-blue-200 text-blue-600 bg-blue-50 w-fit">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span> Sedang Diproses
                    </span>
                </td>

                <!-- 5. Aksi Pop-up -->
                <td class="px-4 py-4 text-center whitespace-nowrap">
                    <CustomTableAction v-slot="{ close }">
                        <div class="px-4 py-2 text-[10px] font-black text-base-content/40 uppercase tracking-widest border-b border-base-300/50 mb-1 text-left">
                            Menu Produksi
                        </div>

                        <!-- Aksi: Detail Produk & Update Progress -->
                        <button @click="openDetailModal(pesanan); close()" class="flex items-center w-full text-left whitespace-nowrap px-4 py-2.5 text-sm font-bold text-base-content hover:bg-base-200 transition-colors">
                            <svg class="w-4 h-4 mr-3 shrink-0 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Detail & Pengerjaan
                        </button>

                        <div class="my-1 border-t border-base-300/50" v-if="currentUser?.role !== 'vendor'"></div>

                        <!-- Aksi: Cetak Label -->
                        <a v-if="currentUser?.role !== 'vendor'" :href="route('pesan.cetakLabel', pesanan.id_pesan)" target="_blank" @click="close()" class="flex items-center w-full text-left whitespace-nowrap px-4 py-2.5 text-sm font-bold text-info hover:bg-info/10 transition-colors">
                            <Printer class="w-4 h-4 mr-3 shrink-0" /> Cetak Label
                        </a>

                        <!-- Aksi: Cetak Nota -->
                        <a v-if="currentUser?.role !== 'vendor'" :href="route('pesan.cetakNota', pesanan.id_pesan)" target="_blank" @click="close()" class="flex items-center w-full text-left whitespace-nowrap px-4 py-2.5 text-sm font-bold text-warning hover:bg-warning/10 transition-colors">
                            <Printer class="w-4 h-4 mr-3 shrink-0" /> Cetak Nota
                        </a>
                    </CustomTableAction>
                </td>
            </tr>

            <!-- Jika Data Kosong -->
            <tr v-if="pesananList.length === 0">
                <td colspan="5" class="px-6 py-20 text-center">
                    <div class="flex flex-col items-center justify-center opacity-30">
                        <Inbox class="w-12 h-12 mb-4" />
                        <h3 class="text-base font-semibold text-base-content">Tidak Ada Pekerjaan</h3>
                        <p class="mt-1 text-sm text-base-content/50">Belum ada pesanan yang sedang diproses saat ini.</p>
                    </div>
                </td>
            </tr>
        </CustomTable>

        <!-- 👇 MODAL DETAIL PRODUK & PENGERJAAN 👇 -->
        <dialog class="modal" :class="{'modal-open': isDetailModalOpen}">
            <div class="max-w-5xl p-0 modal-box rounded-2xl">
                <div class="flex items-center justify-between p-4 border-b sm:p-5 border-base-200 bg-base-50">
                    <div>
                        <h3 class="text-base font-bold text-base-content">Detail Item & Status Pengerjaan</h3>
                        <p class="text-[11px] sm:text-sm font-medium text-base-content/50 mt-0.5">ID Transaksi: <span class="font-bold text-primary">{{ selectedPesananDetail?.id_pesan }}</span></p>
                    </div>
                    <button @click="closeDetailModal" class="btn btn-sm btn-circle btn-ghost text-base-content/40 hover:text-error">✕</button>
                </div>

                <div class="p-4 sm:p-5 max-h-[70vh] overflow-y-auto custom-scrollbar space-y-6">
                    <div v-for="item in selectedPesananDetail?.pesanan_item" :key="item.id" class="overflow-hidden border shadow-sm rounded-xl border-base-200 bg-base-100">

                        <!-- Bagian Detail Produk (Atas) -->
                        <div class="flex flex-col gap-4 p-4 border-b bg-base-50/30 border-base-200 sm:flex-row">
                            <div class="sm:w-2/5">
                                <span class="text-[9px] sm:text-[10px] font-black text-base-content/50 uppercase tracking-widest block mb-1.5">Item Produk</span>
                                <h4 class="text-sm font-black capitalize sm:text-base text-base-content">{{ cleanProductName(item.nama_produk_snapshot) }}</h4>

                                <div v-if="getValidAttributes(item.atribut_custom_snapshot).length > 0" class="mt-1 text-[10px] font-bold text-primary flex flex-wrap gap-1 mb-2">
                                    <span v-for="(attr, idx) in getValidAttributes(item.atribut_custom_snapshot)" :key="attr.key">
                                        <span v-if="idx > 0" class="mx-1 opacity-40 text-base-content">|</span>
                                        <span class="opacity-70">{{ attr.key }}:</span> {{ attr.value }}
                                    </span>
                                </div>

                                <div v-if="item.pesanan_item_finishing?.length" class="flex flex-col gap-0.5 mb-2 mt-1">
                                    <div v-for="(fin, fIdx) in item.pesanan_item_finishing" :key="'fin'+fIdx" class="flex items-start gap-1">
                                        <span class="mt-px text-xs opacity-50">▸</span>
                                        <span class="text-xs font-medium text-base-content">{{ fin.nama_finishing_snapshot }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="sm:flex-1">
                                <span class="text-[9px] sm:text-[10px] font-black text-base-content/50 uppercase tracking-widest block mb-1.5">Spesifikasi / Catatan</span>
                                <div v-if="getFileDisplay(item)" class="mb-2">
                                    <template v-if="getFileDisplay(item).tipe === 'upload'">
                                        <a v-if="item.file_desain" :href="'/storage/' + getFileDisplay(item).nilai" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 hover:underline bg-blue-50 px-2 py-0.5 rounded border border-blue-100">📁 Download File</a>
                                        <span v-else class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 max-w-40 truncate">📁 {{ getFileDisplay(item).nilai }}</span>
                                    </template>
                                    <template v-else-if="getFileDisplay(item).tipe === 'link'">
                                        <a :href="getFileDisplay(item).nilai.startsWith('http') ? getFileDisplay(item).nilai : 'https://' + getFileDisplay(item).nilai" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-purple-600 hover:underline bg-purple-50 px-2 py-0.5 rounded border border-purple-100">🔗 GDrive Link</a>
                                    </template>
                                </div>
                                <div v-else class="mb-2 inline-flex items-center gap-1 text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-100">❌ File Belum Ada</div>
                                <p class="text-[11px] italic opacity-80 leading-tight border-l-2 border-base-300 pl-2 mt-1">"{{ item.catatan ?? "Tidak ada Catatan" }}"</p>
                            </div>

                            <div class="sm:w-24 sm:text-right">
                                <span class="text-[9px] sm:text-[10px] font-black text-base-content/50 uppercase tracking-widest block mb-1.5">Qty</span>
                                <span class="text-lg font-black text-base-content">{{ item.jumlah }}</span>
                            </div>
                        </div>

                        <!-- Bagian Progress Pekerjaan (Bawah) -->
                        <div class="p-0 overflow-x-auto sm:p-2 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:bg-base-300 [&::-webkit-scrollbar-thumb]:rounded-full pb-2">
                            <div class="min-w-150">
                                <CustomTable :headers="headersProses" class="bg-transparent border-none shadow-none">
                                    <tr v-for="schedule in item.pesanan_item_produksi" :key="schedule.id" class="border-b border-base-200/50 hover:bg-base-200/30">
                                        <td class="px-4 py-3 text-xs font-medium whitespace-nowrap">
                                            {{ schedule.tipe_pengerjaan === 'sendiri' ? 'In-House' : (schedule.vendor?.nama_vendor || 'Vendor') }}
                                            <div v-if="schedule.file_revisi" class="mt-1">
                                                <a :href="'/storage/' + schedule.file_revisi" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 hover:underline bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">
                                                    ✅ Hasil File
                                                </a>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-base-content/70 whitespace-nowrap">{{ schedule.instruksi_pengerjaan || '-' }}</td>
                                        <td class="px-4 py-3 text-xs font-semibold text-center whitespace-nowrap">{{ schedule.qty_dikerjakan }}</td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <button v-if="schedule.status_pengerjaan === 'selesai'" @click="openUpdateModal(schedule, item)" class="inline-flex gap-1.5 text-xs font-medium text-green-600 hover:underline">
                                                <CheckCircle class="w-3.5 h-3.5" /> Selesai
                                            </button>
                                            <span v-else class="inline-flex gap-1.5 text-xs font-medium text-base-content/50"><span class="w-1.5 h-1.5 rounded-full bg-base-content/30"></span> Proses</span>
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <button v-if="schedule.status_pengerjaan !== 'selesai'" @click="openUpdateModal(schedule, item)" class="text-xs font-medium text-blue-600 hover:underline" :disabled="checkAccess(schedule) !== 'edit'">
                                                Update
                                            </button>
                                        </td>
                                    </tr>
                                </CustomTable>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop bg-base-content/20"><button @click="closeDetailModal">close</button></form>
        </dialog>

        <!-- 👇 MODAL UPDATE PROGRESS 👇 -->
        <dialog class="modal" :class="{'modal-open': isUpdateModalOpen}">
            <div class="max-w-lg p-0 modal-box rounded-2xl z-100">
                <!-- Header Modal -->
                <div class="flex items-start justify-between p-4 border-b sm:items-center sm:p-5 border-base-200">
                    <div>
                        <h3 class="text-base font-bold text-base-content">
                            {{ isViewOnly ? 'Rincian Laporan Pengerjaan' : (selectedSchedule?.status_pengerjaan === 'selesai' ? 'Edit Laporan Pengerjaan' : 'Perbarui Status Pengerjaan') }}
                        </h3>
                        <p class="text-[11px] sm:text-sm font-medium text-base-content/50 mt-0.5">
                            {{ isViewOnly ? 'Berikut adalah hasil laporan untuk item ini.' : 'Tandai tugas ini sebagai selesai dan isi laporan pengerjaan.' }}
                        </p>
                    </div>
                    <button type="button" @click="closeUpdateModal" class="btn btn-sm btn-circle btn-ghost text-base-content/40 hover:text-error">✕</button>
                </div>

                <form @submit.prevent="submitUpdate">
                    <!-- Body Modal -->
                    <div class="p-4 sm:p-5 max-h-[70vh] overflow-y-auto space-y-5 custom-scrollbar">
                        <div>
                            <label class="block mb-1.5 text-[11px] font-black uppercase tracking-widest text-base-content/50">Laporan Pengerjaan <span v-if="!isViewOnly" class="text-error">*</span></label>
                            <textarea v-model="updateForm.deskripsi_pengerjaan" :disabled="isViewOnly" class="w-full h-24 font-medium rounded-xl textarea textarea-bordered disabled:bg-base-200 disabled:text-base-content/70 disabled:cursor-not-allowed" placeholder="Tulis rincian hasil pengerjaan..."></textarea>
                        </div>

                        <div v-if="selectedSchedule?.tipe_pengerjaan === 'sendiri' && selectedItemUpdate?.id_sku?.startsWith('PRD-0002')" class="p-4 space-y-4 border bg-base-50/50 rounded-xl border-base-200">
                            <div class="flex items-center gap-2 text-xs font-black tracking-widest uppercase text-base-content/70">
                                <UploadCloud class="w-4 h-4 text-base-content/50" /> File Hasil Produksi / Desain
                            </div>
                            <div>
                                <div v-if="isViewOnly">
                                    <a v-if="selectedSchedule?.file_revisi" :href="'/storage/' + selectedSchedule.file_revisi" target="_blank" class="flex justify-center w-full gap-2 font-bold tracking-wide uppercase btn btn-sm btn-outline border-emerald-200 text-emerald-700 hover:bg-emerald-50 hover:border-emerald-300 rounded-xl">
                                        <span>✅</span> Download File Hasil Desain
                                    </a>
                                    <p v-else class="py-2 text-xs italic text-center text-base-content/50">Tidak ada file hasil yang dilampirkan.</p>
                                </div>
                                <CustomInputFile
                                    v-else
                                    v-model="fileHasilObj"
                                    @update:modelValue="updateForm.hasil_desain = $event.file"
                                    :error="updateForm.errors?.hasil_desain"
                                    :disabled="isViewOnly"
                                    label="Upload File Siap Cetak / Mockup (Opsional)"
                                    :show-tipe-file="false"
                                />
                            </div>
                        </div>

                        <div v-if="selectedSchedule?.tipe_pengerjaan === 'vendor'" class="p-4 space-y-4 border bg-base-50/50 rounded-xl border-base-200">
                            <div class="flex items-center gap-2 text-xs font-black tracking-widest uppercase text-base-content/70">
                                <Paperclip class="w-4 h-4 text-base-content/50" /> Detail Penagihan Vendor
                            </div>
                            <CustomInput v-model="updateForm.total_tagihan_vendor" type="number" label="Nominal Tagihan (Rp)" placeholder="0" :disabled="isViewOnly" />
                            <div>
                                <div v-if="isViewOnly">
                                    <a v-if="selectedSchedule?.file_nota" :href="'/storage/' + selectedSchedule.file_nota" target="_blank" class="flex justify-center w-full gap-2 mt-1 font-bold tracking-wide text-blue-700 uppercase border-blue-200 btn btn-sm btn-outline hover:bg-blue-50 hover:border-blue-300 rounded-xl">
                                        <span>📁</span> Download Nota Vendor
                                    </a>
                                    <p v-else class="py-2 mt-1 text-xs italic text-center text-base-content/50">Tidak ada nota yang dilampirkan.</p>
                                </div>
                                <CustomInputFile
                                    v-else
                                    v-model="fileNotaObj"
                                    @update:modelValue="updateForm.file_nota = $event.file"
                                    :error="updateForm.errors?.file_nota"
                                    :disabled="isViewOnly"
                                    label="File Nota Tagihan"
                                    :show-tipe-file="false"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Footer Modal -->
                    <div class="flex flex-col-reverse gap-3 p-4 border-t sm:p-5 sm:flex-row sm:justify-end border-base-200 bg-base-50/50 rounded-b-2xl">
                        <button type="button" @click="closeUpdateModal" class="w-full font-bold tracking-wider uppercase sm:w-auto btn btn-sm btn-ghost rounded-xl text-[10px] sm:text-xs">
                            {{ isViewOnly ? 'Tutup' : 'Batal' }}
                        </button>
                        <button v-if="!isViewOnly" type="submit" :disabled="updateForm.processing" class="w-full px-8 font-bold tracking-wider uppercase sm:w-auto btn btn-sm btn-neutral rounded-xl text-[10px] sm:text-xs">
                            {{ selectedSchedule?.status_pengerjaan === 'selesai' ? 'Simpan Perubahan' : 'Tandai Selesai' }}
                        </button>
                    </div>
                </form>
            </div>
            <!-- Z-index tinggi agar overlay nutupin modal pertama -->
            <form method="dialog" class="modal-backdrop bg-base-content/50 z-90"><button @click="closeUpdateModal">close</button></form>
        </dialog>

    </div>
</template>
