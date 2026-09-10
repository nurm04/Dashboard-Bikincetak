<style scoped>
.custom-scrollbar::-webkit-scrollbar { display: none; }
.custom-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import { Clock, Plus, Trash2, Inbox, Printer } from 'lucide-vue-next';

// 👇 IMPORT KOMPONEN TABEL 👇
import CustomTable from '@/Components/CustomTable.vue';
import CustomTableAction from '@/Components/CustomTableAction.vue';

const props = defineProps({
    pesananList: Array,
    vendors: Array,
    stafs: Array,
    currentUser: Object,
});

const headers = ['ID Pesanan', 'Customer', 'Deadline', 'Status', 'Aksi'];

// Helpers
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

const getCurrentStafId = () => {
    if (props.currentUser?.staf?.id_staf) return props.currentUser.staf.id_staf;
    if (props.currentUser?.id_staf) return props.currentUser.id_staf;
    if (props.stafs && props.currentUser) {
        const found = props.stafs.find(s => s.user_id === props.currentUser.id || s.id_user === props.currentUser.id);
        if (found) return found.id_staf;
    }
    return null;
};

// ==========================================
// LOGIC MODAL DETAIL PESANAN
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
// LOGIC MODAL ALOKASI
// ==========================================
const isAlokasiModalOpen = ref(false);
const selectedOrderAlokasi = ref(null);
const alokasiForm = useForm({ alokasi: [] });

const openAlokasiModal = (pesanan) => {
    selectedOrderAlokasi.value = pesanan;
    alokasiForm.alokasi = pesanan.pesanan_item.map(item => ({
        id_pesanan_item: item.id,
        nama_produk: item.nama_produk_snapshot,
        total_qty: item.jumlah,
        is_desain: item.id_sku?.startsWith('PRD-0002') || false,
        skema: [{
            tipe_pengerjaan: 'sendiri',
            id_vendor: null,
            id_staf_pelaksana: getCurrentStafId(),
            qty_dikerjakan: item.jumlah,
            instruksi_pengerjaan: ''
        }]
    }));
    isAlokasiModalOpen.value = true;
};
const closeAlokasiModal = () => { isAlokasiModalOpen.value = false; selectedOrderAlokasi.value = null; alokasiForm.reset(); };

const getTeralokasiQty = (item) => item.skema.reduce((acc, curr) => acc + (Number(curr.qty_dikerjakan) || 0), 0);
const getSisaQty = (item) => item.total_qty - getTeralokasiQty(item);
const getMaxQty = (item, skemaIndex) => {
    const otherSkemaTotal = item.skema.reduce((acc, curr, idx) => idx === skemaIndex ? acc : acc + (Number(curr.qty_dikerjakan) || 0), 0);
    return item.total_qty - otherSkemaTotal;
};

const enforceMaxQty = (item, skemaIndex) => {
    const maxAllowed = getMaxQty(item, skemaIndex);
    if (item.skema[skemaIndex].qty_dikerjakan > maxAllowed) {
        item.skema[skemaIndex].qty_dikerjakan = maxAllowed > 0 ? maxAllowed : 0;
    }
};

const addSkema = (itemIndex) => {
    const item = alokasiForm.alokasi[itemIndex];
    const sisa = getSisaQty(item);
    if (sisa > 0) {
        item.skema.push({ tipe_pengerjaan: 'vendor', id_vendor: null, id_staf_pelaksana: null, qty_dikerjakan: sisa, instruksi_pengerjaan: '' });
    } else {
        alertStore.show('Seluruh Qty pesanan sudah dialokasikan! Kurangi Qty pelaksana lain terlebih dahulu.', 'warning');
    }
};
const removeSkema = (itemIdx, skemaIdx) => alokasiForm.alokasi[itemIdx].skema.splice(skemaIdx, 1);

const submitAlokasi = () => {
    for (const item of alokasiForm.alokasi) {
        const totalInput = getTeralokasiQty(item);
        if (totalInput !== item.total_qty) return alertStore.show(`Total alokasi Qty untuk ${cleanProductName(item.nama_produk)} (${totalInput}) tidak pas dengan pesanan (${item.total_qty})!`, 'warning');
    }
    alokasiForm.post(route('produksi.alokasi', selectedOrderAlokasi.value.id_pesan), {
        onSuccess: () => { closeAlokasiModal(); alertStore.show('Alokasi berhasil disimpan!', 'success'); },
        onError: () => alertStore.show('Terjadi kesalahan saat memproses alokasi.', 'error')
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
                    <span class="flex items-center gap-1.5 text-[10px] sm:text-xs font-bold px-2.5 py-1 rounded-full border border-base-300 text-base-content/70 bg-base-100 w-fit">
                        <span class="w-1.5 h-1.5 rounded-full bg-warning animate-pulse"></span> Menunggu Alokasi
                    </span>
                </td>

                <td>
                    <CustomTableAction v-slot="{ close }">
                        <div class="px-4 py-2 text-[10px] font-black text-base-content/40 uppercase tracking-widest border-b border-base-300/50 mb-1">
                            Menu Produksi
                        </div>

                        <!-- Aksi: Detail Produk -->
                        <button @click="openDetailModal(pesanan); close()" class="flex items-center w-full text-left whitespace-nowrap px-4 py-2.5 text-sm font-bold text-base-content hover:bg-base-200 transition-colors">
                            <svg class="w-4 h-4 mr-3 shrink-0 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Detail Produk
                        </button>

                        <!-- Aksi: Alokasi Pengerjaan -->
                        <button v-if="$can('produksi', 'ubah') && currentUser?.role !== 'vendor'" @click="openAlokasiModal(pesanan); close()" class="flex items-center w-full text-left whitespace-nowrap px-4 py-2.5 text-sm font-bold text-primary hover:bg-primary/10 transition-colors">
                            <svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            Alokasikan Pengerjaan
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
                        <h3 class="text-base font-semibold text-base-content">Tidak Ada Antrean</h3>
                        <p class="mt-1 text-sm text-base-content/50">Semua pesanan baru sudah dialokasikan.</p>
                    </div>
                </td>
            </tr>
        </CustomTable>


        <!-- 👇 MODAL DETAIL PRODUK 👇 -->
        <dialog class="modal" :class="{'modal-open': isDetailModalOpen}">
            <div class="max-w-3xl p-0 modal-box rounded-2xl">
                <div class="flex items-center justify-between p-4 border-b sm:p-5 border-base-200 bg-base-50">
                    <div>
                        <h3 class="text-base font-bold text-base-content">Rincian Item Pesanan</h3>
                        <p class="text-[11px] sm:text-sm font-medium text-base-content/50 mt-0.5">ID Transaksi: <span class="font-bold text-primary">{{ selectedPesananDetail?.id_pesan }}</span></p>
                    </div>
                    <button @click="closeDetailModal" class="btn btn-sm btn-circle btn-ghost text-base-content/40 hover:text-error">✕</button>
                </div>

                <div class="p-4 sm:p-5 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <div class="overflow-x-auto border rounded-xl border-base-200">
                        <table class="w-full text-sm text-left min-w-150">
                            <thead class="text-[10px] uppercase tracking-widest border-b-2 text-base-content/50 border-base-200 bg-base-100">
                                <tr>
                                    <th class="w-1/2 px-4 py-3 font-bold">Item Produk</th>
                                    <th class="px-4 py-3 font-bold">Spesifikasi / Catatan</th>
                                    <th class="w-24 px-4 py-3 font-bold text-center">Kuantitas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-base-200/50 bg-base-100">
                                <tr v-for="item in selectedPesananDetail?.pesanan_item" :key="item.id" class="transition-colors hover:bg-base-50">
                                    <td class="px-4 py-4 font-medium align-top">
                                        <span class="font-bold capitalize text-primary">{{ cleanProductName(item.nama_produk_snapshot) }}</span>
                                        <div v-if="getValidAttributes(item.atribut_custom_snapshot).length > 0" class="mt-1 text-[10px] font-bold flex flex-wrap gap-1">
                                            <span v-for="(attr, idx) in getValidAttributes(item.atribut_custom_snapshot)" :key="attr.key">
                                                <span v-if="idx > 0" class="mx-1 opacity-40 text-base-content">|</span><span class="opacity-70">{{ attr.key }}:</span> {{ attr.value }}
                                            </span>
                                        </div>
                                        <div v-if="item.id_sku?.startsWith('PRD-0002')" class="inline-flex items-center gap-1 mt-2 text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Auto In-House</div>
                                        <div v-if="item.pesanan_item_finishing?.length" class="flex flex-col gap-0.5 mt-2">
                                            <div v-for="(fin, fIdx) in item.pesanan_item_finishing" :key="'fin'+fIdx" class="flex items-start gap-1">
                                                <span class="mt-px opacity-50 text-[10px]">▸</span>
                                                <span class="font-medium text-xs text-base-content/80">{{ fin.nama_finishing_snapshot }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-xs align-top text-base-content/70">
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
                                    </td>
                                    <td class="px-4 py-4 text-lg font-black text-center align-top">{{ item.jumlah }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop bg-base-content/20"><button @click="closeDetailModal">close</button></form>
        </dialog>


        <!-- 👇 MODAL ALOKASI PENGERJAAN 👇 -->
        <dialog class="modal" :class="{'modal-open': isAlokasiModalOpen}">
            <div class="max-w-4xl p-0 modal-box rounded-2xl">
                <div class="flex items-center justify-between p-4 border-b sm:p-5 border-base-200">
                    <div>
                        <h3 class="text-base font-bold text-base-content">Alokasi Pengerjaan</h3>
                        <p class="text-[11px] sm:text-sm font-medium text-base-content/50 mt-0.5">Tentukan pelaksana (In-house/Vendor) untuk pesanan <span class="font-bold">{{ selectedOrderAlokasi?.id_pesan }}</span></p>
                    </div>
                    <button @click="closeAlokasiModal" class="btn btn-sm btn-circle btn-ghost text-base-content/40 hover:text-error">✕</button>
                </div>
                <div class="p-4 sm:p-5 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <div v-if="alokasiForm.alokasi.filter(i => !i.is_desain).length === 0" class="py-6 text-sm text-center text-base-content/60">
                        Semua item dalam pesanan ini akan otomatis dialokasikan ke pengerjaan In-House.<br>Silakan klik "Simpan Alokasi" untuk melanjutkan.
                    </div>
                    <form @submit.prevent="submitAlokasi" class="space-y-8">
                        <template v-for="(item, itemIndex) in alokasiForm.alokasi" :key="item.id_pesanan_item">
                            <div v-show="!item.is_desain">
                                <div class="flex flex-col gap-2 pb-3 mb-4 border-b sm:flex-row sm:items-center sm:justify-between sm:pb-2 border-base-200">
                                    <h4 class="text-sm font-black capitalize sm:text-base">{{ cleanProductName(item.nama_produk) }}</h4>
                                    <div class="flex items-center gap-3 text-xs">
                                        <span class="px-2 py-1 font-medium border rounded-md border-base-300 bg-base-100 text-base-content/60">
                                            Teralokasi:
                                            <span class="font-black" :class="getSisaQty(item) === 0 ? 'text-success' : 'text-warning'">
                                                {{ getTeralokasiQty(item) }}
                                            </span> / {{ item.total_qty }}
                                        </span>
                                    </div>
                                </div>
                                <div class="space-y-4 sm:space-y-3">
                                    <div v-for="(skema, skemaIndex) in item.skema" :key="skemaIndex" class="flex flex-col items-start gap-3 p-3 border sm:p-0 sm:border-none rounded-xl border-base-200 bg-base-50/50 sm:bg-transparent sm:flex-row sm:items-end">

                                        <!-- JENIS PELAKSANA -->
                                        <div class="w-full sm:w-1/4">
                                            <label class="block mb-1 text-[11px] font-black uppercase tracking-widest text-base-content/50">Tipe Pelaksana</label>
                                            <select
                                                v-model="skema.tipe_pengerjaan"
                                                @change="
                                                    skema.id_vendor = null;
                                                    skema.id_staf_pelaksana = skema.tipe_pengerjaan === 'sendiri' ? getCurrentStafId() : null;
                                                "
                                                class="w-full font-bold select select-sm select-bordered rounded-xl"
                                            >
                                                <option value="sendiri">In-House</option>
                                                <option value="vendor">Vendor</option>
                                            </select>
                                        </div>

                                        <!-- JIKA TIPE: VENDOR -->
                                        <div v-if="skema.tipe_pengerjaan === 'vendor'" class="w-full sm:w-1/4">
                                            <label class="block mb-1 text-[11px] font-black uppercase tracking-widest text-base-content/50">Pilih Vendor</label>
                                            <select v-model="skema.id_vendor" required class="w-full font-bold select select-sm select-bordered rounded-xl">
                                                <option :value="null" disabled>Pilih Vendor...</option>
                                                <option v-for="v in vendors" :key="v.id_vendor" :value="v.id_vendor">{{ v.nama_vendor }}</option>
                                            </select>
                                        </div>

                                        <div class="w-full sm:w-24">
                                            <label class="block mb-1 text-[11px] font-black uppercase tracking-widest text-base-content/50">Qty <span class="text-[9px] normal-case tracking-normal opacity-70">(Maks: {{ getMaxQty(item, skemaIndex) }})</span></label>
                                            <input
                                                type="number"
                                                v-model="skema.qty_dikerjakan"
                                                required
                                                min="1"
                                                :max="getMaxQty(item, skemaIndex)"
                                                @input="enforceMaxQty(item, skemaIndex)"
                                                class="w-full font-bold text-center input input-sm input-bordered rounded-xl"
                                            />
                                        </div>
                                        <div class="flex-1 w-full">
                                            <label class="block mb-1 text-[11px] font-black uppercase tracking-widest text-base-content/50">Instruksi</label>
                                            <input type="text" v-model="skema.instruksi_pengerjaan" placeholder="Opsional (Catatan khusus)..." class="w-full font-medium input input-sm input-bordered rounded-xl" />
                                        </div>
                                        <div class="w-full pt-2 border-t sm:border-none sm:pt-0 sm:w-auto border-base-200" v-if="item.skema.length > 1">
                                            <button type="button" @click="removeSkema(itemIndex, skemaIndex)" class="w-full font-bold tracking-wider uppercase text-error btn btn-sm btn-ghost hover:bg-error/10 sm:w-auto sm:btn-square rounded-xl">
                                                <Trash2 class="w-4 h-4 sm:mr-0 mr-1.5" /> <span class="sm:hidden">Hapus Pelaksana</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" @click="addSkema(itemIndex)" class="flex items-center gap-1.5 mt-4 text-[11px] font-black tracking-widest uppercase text-primary hover:text-primary-focus transition-colors">
                                    <Plus class="w-3.5 h-3.5" /> Tambah Pelaksana (Sisa Qty: {{ getSisaQty(item) }})
                                </button>
                                <div v-if="itemIndex !== alokasiForm.alokasi.length - 1" class="mt-8"></div>
                            </div>
                        </template>
                    </form>
                </div>

                <div class="flex flex-col-reverse gap-3 p-4 border-t sm:p-5 sm:flex-row sm:justify-end border-base-200 bg-base-50/50 rounded-b-2xl">
                    <button type="button" @click="closeAlokasiModal" class="w-full font-bold tracking-wider uppercase sm:w-auto btn btn-sm btn-ghost rounded-xl text-[10px] sm:text-xs">Batal</button>
                    <button type="button" @click="submitAlokasi" :disabled="alokasiForm.processing" class="w-full px-8 font-bold tracking-wider uppercase sm:w-auto btn btn-sm btn-neutral rounded-xl text-[10px] sm:text-xs">Simpan Alokasi</button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop bg-base-content/20"><button @click="closeAlokasiModal">close</button></form>
        </dialog>
    </div>
</template>
