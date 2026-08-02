<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
// Tambahin Inbox dari lucide buat icon empty state
import { Clock, CheckCircle, Paperclip, UploadCloud, Printer, Inbox } from 'lucide-vue-next';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomInputFile from '@/Components/Form/CustomInputFile.vue';

const props = defineProps({
    pesananList: Array,
    currentUser: Object,
    currentVendorId: String,
});

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

const headersProses = ['Pelaksana', 'Instruksi / Keterangan', 'Qty', 'Status', 'Aksi'];

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

        <!-- DESAIN EMPTY STATE BARU SESUAI GAMBAR -->
        <div v-if="pesananList.length === 0" class="flex flex-col items-center justify-center py-20 mt-4 border bg-base-200/20 border-base-300 rounded-3xl animate-in fade-in zoom-in-95 duration-500">
            <Inbox class="w-12 h-12 mb-3 opacity-30 text-base-content" stroke-width="1.5" />
            <h3 class="text-sm font-bold opacity-80 text-base-content">Tidak Ada Pekerjaan</h3>
            <p class="mt-1 text-xs opacity-50 text-base-content">Belum ada pesanan yang sedang diproses saat ini.</p>
        </div>

        <div v-for="pesanan in pesananList" :key="pesanan.id_pesan" class="overflow-hidden border rounded-xl border-base-200 bg-base-100 shadow-sm animate-in fade-in slide-in-from-bottom-2">
            <div class="flex flex-col items-start justify-between gap-4 p-5 border-b sm:flex-row sm:items-center border-base-200 bg-base-50/30">
                <div class="flex items-center gap-4">
                    <div v-if="currentUser?.role !== 'vendor'" class="px-3 py-1.5 border rounded-md border-base-300 bg-base-100 flex flex-col items-center justify-center">
                        <span class="text-[10px] font-medium text-base-content/50 uppercase">ID Pesan</span>
                        <span class="text-sm font-bold text-base-content">{{ pesanan.id_pesan }}</span>
                    </div>
                    <div>
                        <h3 v-if="currentUser?.role !== 'vendor'" class="text-base font-semibold text-base-content">{{ pesanan.customer?.user?.name }}</h3>
                        <span class="flex items-center gap-1.5 mt-1 text-xs font-medium px-2 py-0.5 rounded-full border border-blue-200 text-blue-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span> Sedang Diproses
                        </span>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:items-end">
                    <div class="flex items-center gap-2 text-sm">
                        <Clock class="w-4 h-4 text-base-content/40" />
                        <span class="text-base-content/60">Deadline:</span>
                        <span class="font-semibold" :class="isDeadlinePassed(pesanan.waktu_deadline) ? 'text-red-600' : 'text-base-content'">
                            {{ formatTanggal(pesanan.waktu_deadline) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 mt-1" v-if="currentUser?.role !== 'vendor'">
                        <a :href="route('pesan.cetakLabel', pesanan.id_pesan)" target="_blank" class="font-medium btn btn-xs btn-outline hover:bg-base-200 hover:text-base-content hover:border-base-300 border-base-300 text-base-content/70">
                            <Printer class="w-3 h-3" /> Label
                        </a>
                        <a :href="route('pesan.cetakNota', pesanan.id_pesan)" target="_blank" class="font-medium btn btn-xs btn-outline hover:bg-base-200 hover:text-base-content hover:border-base-300 border-base-300 text-base-content/70">
                            <Printer class="w-3 h-3" /> Nota
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-5 space-y-6">
                <div v-for="item in pesanan.pesanan_item" :key="item.id" class="overflow-hidden border rounded-lg shadow-sm border-base-200">
                    <div class="flex flex-col gap-4 p-4 border-b bg-base-50/50 border-base-200 sm:flex-row">
                        <div class="sm:w-1/3">
                            <span class="text-[10px] font-bold text-base-content/50 uppercase tracking-widest block mb-1.5">Item Produk</span>
                            <h4 class="text-sm font-semibold capitalize text-base-content">{{ cleanProductName(item.nama_produk_snapshot) }}</h4>

                            <div v-if="getValidAttributes(item.atribut_custom_snapshot).length > 0" class="mt-1 text-[10px] font-bold text-primary leading-relaxed flex flex-wrap gap-1 mb-2">
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
                            <span class="text-[10px] font-bold text-base-content/50 uppercase tracking-widest block mb-1.5">Spesifikasi / Catatan</span>
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
                            <span class="text-[10px] font-bold text-base-content/50 uppercase tracking-widest block mb-1.5">Qty</span>
                            <span class="font-semibold text-base-content">{{ item.jumlah }}</span>
                        </div>
                    </div>

                    <CustomTable :headers="headersProses" class="border-none shadow-none">
                        <tr v-for="schedule in item.pesanan_item_produksi" :key="schedule.id" class="border-b border-base-200/50 hover:bg-base-200/30">
                            <!-- REVISI: Tambah whitespace-nowrap biar tabel gak berantakan di layar kecil -->
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

        <dialog class="modal" :class="{'modal-open': isUpdateModalOpen}">
            <div class="max-w-lg p-6 modal-box rounded-xl">
                <h3 class="mb-1 text-base font-semibold">
                    {{ isViewOnly ? 'Rincian Laporan Pengerjaan' : (selectedSchedule?.status_pengerjaan === 'selesai' ? 'Edit Laporan Pengerjaan' : 'Perbarui Status Pengerjaan') }}
                </h3>
                <p v-if="!isViewOnly" class="mb-6 text-sm text-base-content/50">Tandai tugas ini sebagai selesai dan isi laporan pengerjaan.</p>
                <p v-else class="mb-6 text-sm text-base-content/50">Berikut adalah hasil laporan untuk item ini.</p>

                <form @submit.prevent="submitUpdate" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-base-content mb-1.5">Laporan Pengerjaan <span v-if="!isViewOnly" class="text-red-500">*</span></label>
                        <textarea v-model="updateForm.deskripsi_pengerjaan" :disabled="isViewOnly" class="w-full h-24 textarea textarea-bordered disabled:bg-base-200 disabled:text-base-content/70 disabled:cursor-not-allowed" placeholder="Tulis rincian hasil pengerjaan..."></textarea>
                    </div>

                    <div v-if="selectedSchedule?.tipe_pengerjaan === 'sendiri' && selectedItemUpdate?.id_sku?.startsWith('PRD-0002')" class="p-4 space-y-4 border rounded-lg border-base-200 bg-base-50/50">
                        <div class="flex items-center gap-2 text-sm font-medium text-base-content">
                            <UploadCloud class="w-4 h-4 text-base-content/50" /> File Hasil Produksi / Desain
                        </div>
                        <div>
                            <div v-if="isViewOnly">
                                <a v-if="selectedSchedule?.file_revisi" :href="'/storage/' + selectedSchedule.file_revisi" target="_blank" class="flex justify-center w-full gap-2 font-medium btn btn-sm btn-outline border-emerald-200 text-emerald-700 hover:bg-emerald-50 hover:border-emerald-300">
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

                    <div v-if="selectedSchedule?.tipe_pengerjaan === 'vendor'" class="p-4 space-y-4 border rounded-lg border-base-200 bg-base-50/50">
                        <div class="flex items-center gap-2 text-sm font-medium text-base-content">
                            <Paperclip class="w-4 h-4 text-base-content/50" /> Detail Penagihan Vendor
                        </div>
                        <CustomInput v-model="updateForm.total_tagihan_vendor" type="number" label="Nominal Tagihan (Rp)" placeholder="0" :disabled="isViewOnly" />
                        <div>
                            <div v-if="isViewOnly">
                                <a v-if="selectedSchedule?.file_nota" :href="'/storage/' + selectedSchedule.file_nota" target="_blank" class="flex justify-center w-full gap-2 mt-1 font-medium text-blue-700 border-blue-200 btn btn-sm btn-outline hover:bg-blue-50 hover:border-blue-300">
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

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="closeUpdateModal" class="font-medium btn btn-sm btn-ghost">
                            {{ isViewOnly ? 'Tutup' : 'Batal' }}
                        </button>
                        <button v-if="!isViewOnly" type="submit" :disabled="updateForm.processing" class="px-6 font-medium btn btn-sm btn-neutral">
                            {{ selectedSchedule?.status_pengerjaan === 'selesai' ? 'Simpan Perubahan' : 'Tandai Selesai' }}
                        </button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop bg-base-content/20"><button @click="closeUpdateModal">close</button></form>
        </dialog>
    </div>
</template>
