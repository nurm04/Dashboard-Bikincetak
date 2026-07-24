<script setup>
import { ref, computed } from 'vue';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomTableAction from '@/Components/CustomTableAction.vue';

const props = defineProps({
    items: Array,
    statusOperasional: {
        type: String,
        default: ''
    }
});

const emit = defineEmits([
    'requestEdit',
    'deleteItem',
    'addItem',
    'printLabel'
]);

// Computed untuk mengecek apakah status pesanan masih boleh diedit
const isEditable = computed(() => {
    const statusDilarang = ['proses_pengerjaan', 'proses_pengantaran', 'selesai', 'batal'];
    return !statusDilarang.includes(props.statusOperasional);
});

const headers = ['#', 'Produk & Spesifikasi', 'File & Catatan', 'Harga Satuan', 'Qty', 'Subtotal', 'Aksi'];

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka || 0);
};

const getFileDisplay = (item) => {
    if (item.file_desain) {
        let fileData = item.file_desain;
        if (typeof fileData === 'string') {
            try { fileData = JSON.parse(fileData); } catch(e) { return null; }
        }
        if (Array.isArray(fileData) && fileData.length > 0) return { tipe: 'upload', nilai: fileData[0] };
        if (typeof fileData === 'object' && !Array.isArray(fileData)) return fileData;
        return null;
    }

    if (item.tipe_file) {
        return {
            tipe: item.tipe_file,
            nilai: item.tipe_file === 'link' ? item.link_file : (item.file ? (item.file.name || 'File Uploaded') : 'Kosong')
        };
    }

    return null;
};

const isDeleteModalOpen = ref(false);
const selectedItem = ref(null);

const openDeleteModal = (item) => {
    selectedItem.value = item;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    emit('deleteItem', selectedItem.value.id || selectedItem.value.cart_id);
    isDeleteModalOpen.value = false;
    selectedItem.value = null;
};
</script>

<template>
    <CustomAlertConfirm
        :show="isDeleteModalOpen"
        type="error"
        title="Hapus Item Pesanan?"
        :message="`Yakin ingin menghapus produk ${selectedItem?.nama_produk_snapshot || ''} dari daftar?`"
        confirmText="Ya, Hapus"
        @close="isDeleteModalOpen = false"
        @confirm="confirmDelete"
    />
    <div>
        <!-- Header Terpisah -->
        <div class="flex items-center justify-between mb-2 px-3 py-3 rounded-xl bg-base-100">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-primary opacity-80"><path fill-rule="evenodd" d="M10 2a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 2zM10 15a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 15zM10 7a3 3 0 100 6 3 3 0 000-6zM15.657 5.404a.75.75 0 10-1.06-1.06l-1.061 1.06a.75.75 0 001.06 1.06l1.06-1.06zM6.464 14.596a.75.75 0 10-1.06-1.06l-1.06 1.06a.75.75 0 001.06 1.06l1.06-1.06zM18 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 0118 10zM5 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 015 10zM14.596 15.657a.75.75 0 001.06-1.06l-1.06-1.061a.75.75 0 10-1.06 1.06l1.06 1.06zM5.404 6.464a.75.75 0 001.06-1.06l-1.06-1.06a.75.75 0 10-1.061 1.06l1.06 1.06z" clip-rule="evenodd" /></svg>
                <h3 class="text-[10px] font-black tracking-widest uppercase opacity-60">Daftar Item Cetak</h3>
            </div>
            <!-- Tombol Tambah disembunyikan jika pesanan sudah diproses -->
            <CustomButton v-if="$can('pesan', 'ubah') && isEditable" @click="$emit('addItem')" size="sm">+ Tambah Item</CustomButton>
        </div>

        <div class="w-full">
            <CustomTable :headers="headers" class="border-none shadow-none">
                <tr v-if="!items || items.length === 0">
                    <td colspan="7" class="py-12 italic font-bold text-center border-b-0 opacity-40">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span class="text-[10px] uppercase tracking-widest">Belum ada item pesanan.</span>
                        </div>
                    </td>
                </tr>

                <tr v-for="(item, index) in items" :key="item.id || item.cart_id || index" class="transition-colors border-b hover:bg-base-200/30 border-base-200/50">
                    <td class="pt-3 font-mono font-bold text-center opacity-50 align-top">{{ index + 1 }}</td>

                    <!-- KOLOM 1: PRODUK & FINISHING -->
                    <td class="pt-3 align-top">
                        <p class="mb-1.5 text-xs font-black uppercase leading-tight text-primary">{{ item.nama_produk_snapshot }}</p>
                        <div class="flex flex-col gap-0.5 text-[10px] font-medium opacity-70">
                            <div v-for="(fin, fIdx) in (item.pesanan_item_finishing || item.finishing)" :key="'fin'+fIdx" class="flex items-start gap-1">
                                <span class="opacity-50 mt-px">▸</span>
                                <span>{{ fin.nama_finishing_snapshot }} <span class="font-mono">({{ formatRupiah(fin.harga_finishing_snapshot) }})</span></span>
                            </div>
                        </div>
                    </td>

                    <!-- KOLOM 2: FILE & CATATAN -->
                    <td class="pt-3 align-top">
                        <div v-if="getFileDisplay(item)" class="mb-1.5">
                            <template v-if="getFileDisplay(item).tipe === 'upload'">
                                <a v-if="item.file_desain" :href="'/storage/' + getFileDisplay(item).nilai" target="_blank" class="inline-flex items-center gap-1 text-[9px] font-bold text-info hover:underline bg-info/10 px-2 py-0.5 rounded">
                                    📁 Download File
                                </a>
                                <span v-else class="inline-flex items-center gap-1 text-[9px] font-bold text-info bg-info/10 px-2 py-0.5 rounded max-w-40 truncate" :title="getFileDisplay(item).nilai">
                                    📁 {{ getFileDisplay(item).nilai }}
                                </span>
                            </template>
                            <template v-else-if="getFileDisplay(item).tipe === 'link'">
                                <a :href="getFileDisplay(item).nilai.startsWith('http') ? getFileDisplay(item).nilai : 'https://' + getFileDisplay(item).nilai" target="_blank" class="inline-flex items-center gap-1 text-[9px] font-bold text-accent hover:underline bg-accent/10 px-2 py-0.5 rounded max-w-40 truncate">
                                    🔗 GDrive Link
                                </a>
                            </template>
                            <template v-else-if="getFileDisplay(item).tipe === 'email'">
                                <span class="inline-flex items-center gap-1 text-[9px] font-bold text-base-content/60 bg-base-300 px-2 py-0.5 rounded">
                                    📧 Tunggu Email Customer
                                </span>
                            </template>
                        </div>
                        <div v-else class="mb-1.5 inline-flex items-center gap-1 text-[9px] font-bold text-error bg-error/10 px-2 py-0.5 rounded">
                            ❌ File Belum Ada
                        </div>
                        <p v-if="item.catatan" class="text-[9px] italic opacity-70 leading-tight border-l-2 border-base-300 pl-2 mt-1">"{{ item.catatan }}"</p>
                    </td>

                    <!-- KOLOM 3: HARGA SATUAN & DISKON CUSTOMER -->
                    <td class="pt-3 text-right align-top">
                        <div class="flex flex-col items-end gap-1">
                            <div v-if="item.total_diskon_snapshot > 0" class="text-[9px] line-through text-error opacity-60 mb-0.5">
                                {{ formatRupiah(item.harga_dasar_awal_snapshot) }}
                            </div>
                            <div class="font-mono text-xs font-bold text-base-content">
                                {{ formatRupiah(item.harga_satuan_snapshot + ((item.pesanan_item_finishing || item.finishing)?.reduce((acc, fin) => acc + (Number(fin.harga_finishing_snapshot) || 0), 0) || 0)) }}
                            </div>
                            <div v-if="item.rincian_diskon_snapshot?.length" class="flex flex-col items-end gap-0.5 mt-0.5">
                                <span v-for="(diskon, dIdx) in item.rincian_diskon_snapshot" :key="'dsc'+dIdx" class="text-[7px] font-black uppercase tracking-wider text-error opacity-90 bg-error/10 px-1.5 py-0.5 rounded">
                                    {{ diskon.nama }}
                                </span>
                            </div>
                        </div>
                    </td>

                    <!-- KOLOM 4: QTY -->
                    <td class="pt-3 text-center align-top">
                        <span class="text-xs font-black">{{ item.jumlah }}</span>
                    </td>

                    <!-- KOLOM 5: SUBTOTAL & SLA -->
                    <td class="pt-3 text-right align-top">
                        <div class="flex flex-col items-end gap-1">
                            <span class="font-mono text-sm font-black text-primary">
                                {{ formatRupiah(((item.harga_satuan_snapshot + ((item.pesanan_item_finishing || item.finishing)?.reduce((acc, fin) => acc + (Number(fin.harga_finishing_snapshot) || 0), 0) || 0)) * item.jumlah) + (Number(item.harga_pengerjaan_snapshot) || 0)) }}
                            </span>
                            <div v-if="(item.harga_pengerjaan_snapshot || 0) > 0" class="mt-0.5">
                                <span class="text-[7px] font-black uppercase tracking-wider text-warning opacity-90 bg-warning/10 px-1.5 py-0.5 rounded">
                                    + SLA ({{ item.estimasi_pengerjaan_snapshot || item.estimasi_pengerjaan }}): {{ formatRupiah(item.harga_pengerjaan_snapshot) }}
                                </span>
                            </div>
                        </div>
                    </td>

                    <!-- KOLOM 6: AKSI DINAMIS -->
                    <td class="pt-3 text-center align-top">
                        <!-- TAMPILKAN DROPDOWN LENGKAP JIKA STATUS MASIH MENUNGGU -->
                        <template v-if="isEditable">
                            <CustomTableAction v-slot="{ close }">
                                <div class="px-4 py-2 text-[9px] font-black text-base-content/30 uppercase tracking-widest border-b border-base-300/50 mb-1">
                                    Opsi Item
                                </div>
                                <button v-if="$can('pesan', 'ubah')" @click="$emit('requestEdit', item); close()" class="flex items-center w-full px-4 py-2.5 text-xs font-bold text-base-content hover:bg-base-200 transition-colors">
                                    <svg class="w-4 h-4 mr-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    Edit / Ubah File
                                </button>
                                <button v-if="item.id" @click="$emit('printLabel', item.id); close()" class="flex items-center w-full px-4 py-2.5 text-xs font-bold text-info hover:bg-info/10 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                    Cetak Label SPK
                                </button>
                                <div class="my-1 border-t border-base-300/50"></div>
                                <button v-if="$can('pesan', 'hapus')" @click="openDeleteModal(item); close()" class="flex items-center w-full px-4 py-2.5 text-xs font-bold text-error hover:bg-error/10 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Hapus dari Nota
                                </button>
                            </CustomTableAction>
                        </template>

                        <!-- TAMPILKAN TOMBOL CETAK LABEL SAJA JIKA STATUS SUDAH DIPROSES -->
                        <template v-else>
                            <button v-if="item.id" @click="$emit('printLabel', item.id)" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 mx-auto text-[9px] font-black tracking-widest uppercase text-info bg-info/10 hover:bg-info hover:text-info-content transition-colors rounded-lg border border-info/20 tooltip tooltip-left" data-tip="Cetak Label Item SPK">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                Label
                            </button>
                        </template>
                    </td>

                </tr>
            </CustomTable>
        </div>
    </div>
</template>
