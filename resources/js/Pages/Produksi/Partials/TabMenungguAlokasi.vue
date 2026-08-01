<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import { Clock, Plus, Trash2, Inbox, Printer } from 'lucide-vue-next';

const props = defineProps({
    pesananList: Array,
    vendors: Array,
    currentUser: Object,
});

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

// Alokasi Logic
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
        skema: [{ tipe_pengerjaan: 'sendiri', id_vendor: null, qty_dikerjakan: item.jumlah, instruksi_pengerjaan: '' }]
    }));
    isAlokasiModalOpen.value = true;
};
const closeAlokasiModal = () => { isAlokasiModalOpen.value = false; selectedOrderAlokasi.value = null; alokasiForm.reset(); };

// Helper untuk limitasi Qty
const getTeralokasiQty = (item) => item.skema.reduce((acc, curr) => acc + (Number(curr.qty_dikerjakan) || 0), 0);
const getSisaQty = (item) => item.total_qty - getTeralokasiQty(item);
const getMaxQty = (item, skemaIndex) => {
    const otherSkemaTotal = item.skema.reduce((acc, curr, idx) => idx === skemaIndex ? acc : acc + (Number(curr.qty_dikerjakan) || 0), 0);
    return item.total_qty - otherSkemaTotal;
};

// Memaksa input tidak melebihi sisa (aktif saat user mengetik)
const enforceMaxQty = (item, skemaIndex) => {
    const maxAllowed = getMaxQty(item, skemaIndex);
    if (item.skema[skemaIndex].qty_dikerjakan > maxAllowed) {
        item.skema[skemaIndex].qty_dikerjakan = maxAllowed > 0 ? maxAllowed : 0;
    }
};

const addSkema = (itemIndex) => {
    const item = alokasiForm.alokasi[itemIndex];
    const sisa = getSisaQty(item);

    // Hanya bisa tambah pelaksana kalau masih ada sisa quantity
    if (sisa > 0) {
        item.skema.push({ tipe_pengerjaan: 'vendor', id_vendor: null, qty_dikerjakan: sisa, instruksi_pengerjaan: '' });
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
        <div v-if="pesananList.length === 0" class="flex flex-col items-center justify-center py-20 text-center border rounded-xl border-base-200 bg-base-100">
            <Inbox class="w-12 h-12 mb-4 text-base-content/20" />
            <h3 class="text-base font-semibold text-base-content">Tidak Ada Antrean</h3>
            <p class="mt-1 text-sm text-base-content/50">Semua pesanan baru sudah dialokasikan.</p>
        </div>

        <div v-for="pesanan in pesananList" :key="pesanan.id_pesan" class="overflow-hidden border shadow-sm rounded-xl border-base-200 bg-base-100 animate-in fade-in slide-in-from-bottom-2">
            <div class="flex flex-col items-start justify-between gap-4 p-5 border-b sm:flex-row sm:items-center border-base-200 bg-base-50/30">
                <div class="flex items-center gap-4">
                    <div v-if="currentUser?.role !== 'vendor'" class="px-3 py-1.5 border rounded-md border-base-300 bg-base-100 flex flex-col items-center justify-center">
                        <span class="text-[10px] font-medium text-base-content/50 uppercase">ID Pesan</span>
                        <span class="text-sm font-bold text-base-content">{{ pesanan.id_pesan }}</span>
                    </div>
                    <div>
                        <h3 v-if="currentUser?.role !== 'vendor'" class="text-base font-semibold text-base-content">{{ pesanan.customer?.user?.name }}</h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="flex items-center gap-1.5 text-xs font-medium px-2 py-0.5 rounded-full border border-base-300 text-base-content/70">
                                <span class="w-1.5 h-1.5 rounded-full bg-base-content/40"></span> Menunggu Alokasi
                            </span>
                        </div>
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
                        <a :href="route('pesan.cetakLabel', pesanan.id_pesan)" target="_blank" class="font-medium btn btn-xs btn-outline hover:bg-base-200 hover:text-base-content hover:border-base-300 border-base-300 text-base-content/70"><Printer class="w-3 h-3" /> Label</a>
                        <a :href="route('pesan.cetakNota', pesanan.id_pesan)" target="_blank" class="font-medium btn btn-xs btn-outline hover:bg-base-200 hover:text-base-content hover:border-base-300 border-base-300 text-base-content/70"><Printer class="w-3 h-3" /> Nota</a>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs border-b-2 text-base-content/50 border-base-300">
                        <tr>
                            <th class="w-1/3 pb-3 font-medium">Item Produk</th>
                            <th class="pb-3 font-medium">Spesifikasi / Catatan</th>
                            <th class="w-24 pb-3 font-medium text-right">Kuantitas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-100">
                        <tr v-for="item in pesanan.pesanan_item" :key="item.id" class="group">
                            <td class="py-4 font-medium align-top">
                                <span class="font-semibold capitalize">{{ cleanProductName(item.nama_produk_snapshot) }}</span>
                                <div v-if="getValidAttributes(item.atribut_custom_snapshot).length > 0" class="mt-1 text-[10px] font-bold text-primary flex flex-wrap gap-1">
                                    <span v-for="(attr, idx) in getValidAttributes(item.atribut_custom_snapshot)" :key="attr.key">
                                        <span v-if="idx > 0" class="mx-1 opacity-40 text-base-content">|</span><span class="opacity-70">{{ attr.key }}:</span> {{ attr.value }}
                                    </span>
                                </div>
                                <div v-if="item.id_sku?.startsWith('PRD-0002')" class="ml-2 inline-flex mt-1.5 items-center gap-1 text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Auto In-House</div>
                                <div v-if="item.pesanan_item_finishing?.length" class="flex flex-col gap-0.5 mt-2 mb-2">
                                    <div v-for="(fin, fIdx) in item.pesanan_item_finishing" :key="'fin'+fIdx" class="flex items-start gap-1"><span class="mt-px opacity-50">▸</span><span class="font-medium text-base-content">{{ fin.nama_finishing_snapshot }}</span></div>
                                </div>
                            </td>
                            <td class="py-4 text-xs align-top text-base-content/70">
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
                            <td class="py-4 font-semibold text-right align-top">{{ item.jumlah }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="flex justify-end mt-5" v-if="currentUser?.role !== 'vendor'">
                    <button v-if="$can('produksi', 'ubah')" @click="openAlokasiModal(pesanan)" class="px-6 font-medium btn btn-sm btn-neutral">Alokasikan Pengerjaan</button>
                </div>
            </div>
        </div>

        <!-- MODAL ALOKASI -->
        <dialog class="modal" :class="{'modal-open': isAlokasiModalOpen}">
            <div class="max-w-4xl p-0 modal-box rounded-xl">
                <div class="flex items-center justify-between p-5 border-b border-base-200">
                    <div>
                        <h3 class="text-base font-semibold">Alokasi Pengerjaan</h3>
                        <p class="text-sm text-base-content/50 mt-0.5">Tentukan pelaksana (In-house/Vendor) untuk pesanan {{ selectedOrderAlokasi?.id_pesan }}</p>
                    </div>
                    <button @click="closeAlokasiModal" class="text-base-content/40 hover:text-base-content">✕</button>
                </div>
                <div class="p-5 max-h-[70vh] overflow-y-auto">
                    <div v-if="alokasiForm.alokasi.filter(i => !i.is_desain).length === 0" class="py-6 text-sm text-center text-base-content/60">
                        Semua item dalam pesanan ini akan otomatis dialokasikan ke pengerjaan In-House.<br>Silakan klik "Simpan Alokasi" untuk melanjutkan.
                    </div>
                    <form @submit.prevent="submitAlokasi" class="space-y-8">
                        <template v-for="(item, itemIndex) in alokasiForm.alokasi" :key="item.id_pesanan_item">
                            <div v-show="!item.is_desain">
                                <div class="flex items-center justify-between mb-3 border-b border-base-200 pb-2">
                                    <h4 class="text-sm font-bold capitalize">{{ cleanProductName(item.nama_produk) }}</h4>
                                    <div class="flex items-center gap-3 text-xs">
                                        <span class="text-base-content/60">
                                            Teralokasi:
                                            <span class="font-black" :class="getSisaQty(item) === 0 ? 'text-success' : 'text-warning'">
                                                {{ getTeralokasiQty(item) }}
                                            </span> / {{ item.total_qty }}
                                        </span>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div v-for="(skema, skemaIndex) in item.skema" :key="skemaIndex" class="flex flex-col items-start gap-3 sm:flex-row sm:items-end">
                                        <div class="w-full sm:w-1/4">
                                            <label class="block mb-1 text-xs font-medium text-base-content/70">Pelaksana</label>
                                            <select v-model="skema.tipe_pengerjaan" class="w-full font-medium select select-sm select-bordered">
                                                <option value="sendiri">In-House</option>
                                                <option value="vendor">Vendor</option>
                                            </select>
                                        </div>
                                        <div v-if="skema.tipe_pengerjaan === 'vendor'" class="w-full sm:w-1/4">
                                            <label class="block mb-1 text-xs font-medium text-base-content/70">Pilih Vendor</label>
                                            <select v-model="skema.id_vendor" required class="w-full font-medium select select-sm select-bordered">
                                                <option :value="null" disabled>Pilih...</option>
                                                <option v-for="v in vendors" :key="v.id_vendor" :value="v.id_vendor">{{ v.nama_vendor }}</option>
                                            </select>
                                        </div>
                                        <div class="w-full sm:w-24">
                                            <label class="block mb-1 text-xs font-medium text-base-content/70">Qty <span class="text-[10px] opacity-50">(Maks: {{ getMaxQty(item, skemaIndex) }})</span></label>
                                            <input
                                                type="number"
                                                v-model="skema.qty_dikerjakan"
                                                required
                                                min="1"
                                                :max="getMaxQty(item, skemaIndex)"
                                                @input="enforceMaxQty(item, skemaIndex)"
                                                class="w-full input input-sm input-bordered"
                                            />
                                        </div>
                                        <div class="flex-1 w-full">
                                            <label class="block mb-1 text-xs font-medium text-base-content/70">Instruksi</label>
                                            <input type="text" v-model="skema.instruksi_pengerjaan" placeholder="Opsional..." class="w-full input input-sm input-bordered" />
                                        </div>
                                        <div class="pb-0.5" v-if="item.skema.length > 1">
                                            <button type="button" @click="removeSkema(itemIndex, skemaIndex)" class="text-red-500 btn btn-sm btn-square btn-ghost hover:bg-red-50"><Trash2 class="w-4 h-4" /></button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" @click="addSkema(itemIndex)" class="flex items-center gap-1 mt-3 text-xs font-medium text-blue-600 hover:underline">
                                    <Plus class="w-3.5 h-3.5" /> Tambah Pelaksana (Sisa Qty: {{ getSisaQty(item) }})
                                </button>
                                <div v-if="itemIndex !== alokasiForm.alokasi.length - 1" class="mt-6"></div>
                            </div>
                        </template>
                    </form>
                </div>
                <div class="flex justify-end gap-3 p-5 border-t border-base-200 bg-base-50/50 rounded-b-xl">
                    <button type="button" @click="closeAlokasiModal" class="font-medium btn btn-sm btn-ghost">Batal</button>
                    <button type="button" @click="submitAlokasi" :disabled="alokasiForm.processing" class="px-6 font-medium btn btn-sm btn-neutral">Simpan Alokasi</button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop bg-base-content/20"><button @click="closeAlokasiModal">close</button></form>
        </dialog>
    </div>
</template>
