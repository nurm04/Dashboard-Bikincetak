<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import StafLayout from '@/Layouts/StafLayout.vue';
import { ArrowLeft, Printer, CheckCircle2, FileText, Package, User, MapPin, Truck } from 'lucide-vue-next';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';

const props = defineProps({
    penawaran: Object,
});

// ==========================================
// AKSI UPDATE STATUS & BUAT SO
// ==========================================
const showConfirm = ref(false);
const confirmAction = ref('');
const confirmTitle = ref('');
const confirmMessage = ref('');
const isProcessing = ref(false);

const openConfirm = (action) => {
    confirmAction.value = action;

    if (action === 'terkirim') {
        confirmTitle.value = 'Tandai Terkirim?';
        confirmMessage.value = 'Status penawaran akan diubah menjadi Terkirim. Pastikan Anda sudah memberikan dokumen PDF ini ke pelanggan.';
    } else if (action === 'disetujui') {
        confirmTitle.value = 'Setujui Penawaran (ACC)?';
        confirmMessage.value = 'Pelanggan telah menyetujui penawaran ini? Setelah di-ACC, penawaran ini dapat dikonversi menjadi Pesanan Resmi (Sales Order).';
    } else if (action === 'ditolak') {
        confirmTitle.value = 'Tolak Penawaran?';
        confirmMessage.value = 'Penawaran ini akan dibatalkan/ditolak oleh pelanggan. Lanjutkan?';
    } else if (action === 'buat_so') {
        // 👇 TAMBAHAN UNTUK BUAT SO 👇
        confirmTitle.value = 'Buat Sales Order (SO)?';
        confirmMessage.value = 'Penawaran ini akan dikonversi menjadi Pesanan Resmi. Data tidak bisa dikembalikan ke awal. Lanjutkan?';
    }

    showConfirm.value = true;
};

const executeUpdateStatus = () => {
    // 👇 LOGIKA BUAT SO YANG UDAH DIBIKIN JUJUR 👇
    if (confirmAction.value === 'buat_so') {
        isProcessing.value = true;
        router.post(route('penawaran.convert-to-so', props.penawaran.id_penawaran), {}, {
            onSuccess: (page) => {
                showConfirm.value = false;
                isProcessing.value = false;

                // CEK JUJUR: Apakah backend ngirim pesan error flash?
                if (page.props.flash && page.props.flash.error) {
                    alertStore.show(page.props.flash.error, 'error'); // Tampilkan error aslinya warna merah!
                } else {
                    alertStore.show('Sales Order berhasil dibuat!', 'success');
                }
            },
            onError: () => {
                alertStore.show('Gagal koneksi ke server!', 'error');
                showConfirm.value = false;
                isProcessing.value = false;
            }
        });
        return;
    }

    // Logika update status biasa (Draft, Terkirim, Ditolak)
    isProcessing.value = true;
    router.patch(route('penawaran.update-status', props.penawaran.id_penawaran), {
        status_penawaran: confirmAction.value
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            showConfirm.value = false;
            isProcessing.value = false;
            if (page.props.flash && page.props.flash.error) {
                alertStore.show(page.props.flash.error, 'error');
            } else {
                alertStore.show('Status penawaran berhasil diubah!', 'success');
            }
        },
        onError: () => {
            alertStore.show('Gagal mengubah status penawaran!', 'error');
            showConfirm.value = false;
            isProcessing.value = false;
        }
    });
};

// ==========================================
// FORM SELECT STATUS
// ==========================================
const formStatus = useForm({
    status_penawaran: props.penawaran.status_penawaran
});

const updateStatus = () => {
    formStatus.patch(route('penawaran.update-status', props.penawaran.id_penawaran), {
        preserveScroll: true,
        onSuccess: () => {
            alertStore.show('Status penawaran berhasil diubah!', 'success');
        },
        onError: () => {
            alertStore.show('Gagal mengubah status penawaran!', 'error');
            formStatus.status_penawaran = props.penawaran.status_penawaran; // Kembalikan nilai awal kalau gagal
        }
    });
};

// ==========================================
// UTILITIES FORMATTING
// ==========================================
const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const safeJSONParse = (data) => {
    if (!data) return null;
    try {
        return typeof data === 'string' ? JSON.parse(data) : data;
    } catch (e) {
        return null;
    }
};

// ==========================================
// KALKULASI TOTAL
// ==========================================
const hitungTotalItem = (item) => {
    const qty = Number(item.jumlah) || 1;
    const hargaSatuan = Number(item.harga_satuan_snapshot) || 0;
    const hargaPengerjaan = Number(item.harga_pengerjaan_snapshot) || 0;

    let totalFinishing = 0;
    if (item.penawaran_item_finishing && item.penawaran_item_finishing.length > 0) {
        item.penawaran_item_finishing.forEach(f => {
            totalFinishing += (Number(f.harga_finishing_snapshot) || 0) * qty;
        });
    }

    return (hargaSatuan * qty) + totalFinishing + hargaPengerjaan;
};

const subtotalProduk = computed(() => {
    if (!props.penawaran.penawaran_item) return 0;
    return props.penawaran.penawaran_item.reduce((total, item) => total + hitungTotalItem(item), 0);
});

const grandTotal = computed(() => {
    const ongkir = Number(props.penawaran.harga_ongkir) || 0;
    return subtotalProduk.value + ongkir;
});

</script>

<template>
    <Head :title="`Detail Penawaran - ${penawaran.id_penawaran}`" />

    <StafLayout>
        <template #header>
            <div class="flex flex-col w-full gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('penawaran.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h2 class="flex items-center gap-2 text-xl font-bold leading-tight text-base-content">
                            Detail Dokumen Penawaran {{ penawaran.id_penawaran }}
                        </h2>
                    </div>
                </div>

                <!-- TOMBOL CETAK, BUAT SO & SELECT DROPDOWN UBAH STATUS -->
                <div class="flex items-center gap-3">

                    <!-- 👇 TOMBOL BUAT SO (UDAH DIRAPIHIN) 👇 -->
                    <button
                        v-if="formStatus.status_penawaran === 'disetujui' && !penawaran.id_pesan_terkait"
                        @click="openConfirm('buat_so')"
                        class="btn btn-sm btn-outline btn-success shadow-sm font-black uppercase tracking-widest text-[10px] whitespace-nowrap flex-nowrap"
                    >
                        <CheckCircle2 class="inline-block w-3.5 h-3.5 mr-1" /> BUAT SO
                    </button>

                    <!-- 👇 TOMBOL CETAK PDF 👇 -->
                    <a :href="route('penawaran.cetak', penawaran.id_penawaran)" target="_blank" class="btn btn-sm btn-outline shadow-sm font-black uppercase tracking-widest text-[10px] whitespace-nowrap flex-nowrap">
                        <Printer class="inline-block w-3.5 h-3.5 mr-1" /> CETAK PDF
                    </a>

                    <select
                        v-model="formStatus.status_penawaran"
                        @change="updateStatus"
                        class="text-xs font-black tracking-wider uppercase border-2 shadow-sm select select-bordered select-sm rounded-xl"
                        :class="[
                            formStatus.status_penawaran === 'draft' ? 'border-base-300 text-base-content bg-base-200/50' : '',
                            formStatus.status_penawaran === 'terkirim' ? 'border-info/50 text-info bg-info/10' : '',
                            formStatus.status_penawaran === 'disetujui' ? 'border-success/50 text-success bg-success/10' : '',
                            formStatus.status_penawaran === 'ditolak' ? 'border-error/50 text-error bg-error/10' : '',
                            formStatus.status_penawaran === 'kedaluwarsa' ? 'border-warning/50 text-warning bg-warning/10' : ''
                        ]"
                        :disabled="penawaran.id_pesan_terkait || formStatus.processing"
                    >
                        <option value="draft" class="bg-base-100 text-base-content">DRAFT</option>
                        <option value="terkirim" class="bg-base-100 text-base-content">TERKIRIM</option>
                        <option value="disetujui" class="bg-base-100 text-base-content">DISETUJUI (ACC)</option>
                        <option value="ditolak" class="bg-base-100 text-base-content">DITOLAK</option>
                        <option value="kedaluwarsa" class="bg-base-100 text-base-content">KEDALUWARSA</option>
                    </select>
                    <span v-if="formStatus.processing" class="loading loading-spinner loading-xs text-primary"></span>
                </div>
            </div>
        </template>

        <div class="px-4 py-6 mx-auto max-w-350">
            <div class="grid items-start grid-cols-1 gap-6 lg:grid-cols-12">

                <!-- KOLOM KIRI: INFO & ITEM -->
                <div class="space-y-6 lg:col-span-8 xl:col-span-9">

                    <!-- INFORMASI PELANGGAN & PENGIRIMAN -->
                    <div class="p-6 border shadow-sm bg-base-100 border-base-200/80 rounded-3xl">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Customer -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-2 pb-2 border-b border-base-200/50">
                                    <User class="w-4 h-4 text-primary opacity-80" />
                                    <h3 class="text-[10px] font-black tracking-widest uppercase opacity-50">Informasi Instansi/Pemesan</h3>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-base-content">{{ penawaran.customer?.user?.name || 'Walk-In / Terhapus' }}</div>
                                    <div class="text-sm opacity-70">{{ penawaran.customer?.no_hp || '-' }}</div>
                                </div>
                            </div>

                            <!-- Ekspedisi -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-2 pb-2 border-b border-base-200/50">
                                    <Truck class="w-4 h-4 text-primary opacity-80" />
                                    <h3 class="text-[10px] font-black tracking-widest uppercase opacity-50">Rencana Pengiriman</h3>
                                </div>
                                <div>
                                    <div class="text-sm font-black tracking-wider uppercase text-base-content">{{ penawaran.ekspedisi_nama || 'Belum Termasuk Biaya Kirim' }}</div>

                                    <div v-if="penawaran.ekspedisi_layanan" class="mt-1 text-xs font-bold uppercase opacity-70">{{ penawaran.ekspedisi_layanan }}</div>

                                    <div v-if="penawaran.harga_ongkir > 0" class="inline-block p-2 mt-2 text-xs font-black rounded-lg text-primary bg-primary/10">
                                        Tarif Ongkir: {{ formatRupiah(penawaran.harga_ongkir) }}
                                    </div>

                                    <div class="flex items-start gap-2 p-3 mt-3 bg-base-200/50 rounded-xl">
                                        <MapPin class="w-4 h-4 mt-0.5 opacity-50 shrink-0" />
                                        <div class="text-xs leading-relaxed opacity-80">
                                            <span class="block font-bold text-base-content">{{ penawaran.alamat?.label || 'Alamat Belum Ditentukan' }}</span>
                                            {{ penawaran.alamat?.alamat_lengkap || 'Instansi akan mengambil di toko atau alamat disusulkan.' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DAFTAR ITEM PENAWARAN (UI SAMA PERSIS DENGAN POS KASIR) -->
                    <div class="overflow-hidden border shadow-sm bg-base-100 border-base-200/80 rounded-3xl">
                        <div class="flex items-center gap-2 p-6 pb-4 border-b border-base-200/50">
                            <Package class="w-4 h-4 text-primary opacity-80" />
                            <h3 class="text-[10px] font-black tracking-widest uppercase opacity-50">Daftar Item Cetak</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="table w-full whitespace-nowrap">
                                <thead class="text-[10px] font-black tracking-widest uppercase text-base-content/40 bg-base-200/30">
                                    <tr>
                                        <th class="px-6 py-4">#</th>
                                        <th>Produk & Spesifikasi</th>
                                        <th>Catatan</th>
                                        <th>Harga Satuan</th>
                                        <th class="text-center">Qty</th>
                                        <th class="px-6 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    <tr v-for="(item, index) in penawaran.penawaran_item" :key="item.id" class="border-b hover:bg-base-200/20 border-base-200/50 last:border-0">
                                        <td class="px-6 py-4 font-bold text-base-content/40">{{ index + 1 }}</td>

                                        <!-- Produk & Spesifikasi -->
                                        <td class="py-4">
                                            <div class="font-black text-primary">{{ item.nama_produk_snapshot }}</div>

                                            <!-- Atribut Custom -->
                                            <div v-if="item.atribut_custom_snapshot" class="flex flex-wrap gap-1.5 mt-2">
                                                <span v-for="(val, key) in safeJSONParse(item.atribut_custom_snapshot)" :key="key" class="px-2 py-1 text-[9px] font-bold tracking-wider uppercase border rounded-md bg-base-100 text-base-content/70 border-base-300">
                                                    {{ key }}: {{ val }}
                                                </span>
                                            </div>

                                            <!-- Finishing -->
                                            <div v-if="item.penawaran_item_finishing?.length" class="mt-2 text-[10px] opacity-70">
                                                <div class="font-bold mb-0.5 opacity-60 uppercase tracking-widest">Tambahan Finishing:</div>
                                                <ul class="pl-2 space-y-0.5">
                                                    <li v-for="fin in item.penawaran_item_finishing" :key="fin.id" class="flex items-center gap-1.5">
                                                        <span class="w-1 h-1 rounded-full bg-base-content/40"></span>
                                                        {{ fin.nama_finishing_snapshot }}
                                                        <span v-if="fin.harga_finishing_snapshot > 0" class="font-mono font-bold text-primary">(+ {{ formatRupiah(fin.harga_finishing_snapshot) }})</span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>

                                        <!-- Catatan -->
                                        <td class="py-4 text-xs whitespace-normal min-w-37.5">
                                            <div v-if="item.catatan" class="p-2 italic font-medium border rounded-lg bg-warning/10 text-warning-content border-warning/20">
                                                {{ item.catatan }}
                                            </div>
                                            <span v-else class="italic opacity-30">-</span>
                                        </td>

                                        <!-- Harga Satuan -->
                                        <td class="py-4">
                                            <div class="font-mono text-sm font-bold text-base-content">{{ formatRupiah(item.harga_satuan_snapshot) }}</div>

                                            <div v-if="item.harga_pengerjaan_snapshot > 0" class="text-[10px] font-bold text-info mt-1 uppercase tracking-wider">
                                                SLA ({{ item.estimasi_pengerjaan_snapshot }}): <br>+ {{ formatRupiah(item.harga_pengerjaan_snapshot) }}
                                            </div>

                                            <div v-if="item.total_diskon_snapshot > 0" class="text-[10px] font-bold text-error mt-1.5 border-t border-error/20 pt-1">
                                                <div v-for="(disc, dIdx) in safeJSONParse(item.rincian_diskon_snapshot)" :key="dIdx">
                                                    {{ disc.nama }}: <br>- {{ formatRupiah(disc.nominal) }}
                                                </div>
                                            </div>
                                        </td>

                                        <!-- QTY -->
                                        <td class="py-4 font-black text-center text-base-content">{{ item.jumlah }}</td>

                                        <!-- Subtotal -->
                                        <td class="px-6 py-4 font-mono text-sm font-black text-right text-primary">
                                            {{ formatRupiah(hitungTotalItem(item)) }}
                                        </td>
                                    </tr>
                                    <tr v-if="penawaran.penawaran_item?.length === 0">
                                        <td colspan="6" class="py-8 text-xs font-bold tracking-widest text-center uppercase opacity-30">
                                            Tidak ada item.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- KOLOM KANAN: SUMMARY & AKSI -->
                <div class="space-y-6 lg:col-span-4 xl:col-span-3">

                    <!-- INFORMASI DOKUMEN -->
                    <div class="p-6 border shadow-sm bg-base-100 border-base-200/80 rounded-3xl">
                        <div class="flex items-center gap-2 pb-3 mb-4 border-b border-base-200/50">
                            <FileText class="w-4 h-4 text-primary opacity-80" />
                            <h3 class="text-[10px] font-black tracking-widest uppercase opacity-50">Pengaturan Dokumen</h3>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest opacity-50 block mb-1">Dibuat Pada</label>
                                <div class="text-sm font-bold">{{ formatDate(penawaran.tanggal_penawaran) }}</div>
                            </div>
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest opacity-50 block mb-1">Berlaku Sampai</label>
                                <div class="text-sm font-bold" :class="penawaran.berlaku_sampai ? 'text-error' : ''">
                                    {{ penawaran.berlaku_sampai ? formatDate(penawaran.berlaku_sampai) : 'Tidak Terbatas' }}
                                </div>
                            </div>
                            <div v-if="penawaran.catatan_penawaran">
                                <label class="text-[10px] font-black uppercase tracking-widest opacity-50 block mb-2">Syarat & Ketentuan</label>
                                <div class="text-[11px] leading-relaxed opacity-80 whitespace-pre-wrap p-3 bg-base-200/30 rounded-xl border border-base-200">{{ penawaran.catatan_penawaran }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- RINGKASAN TAGIHAN -->
                    <div class="relative p-6 overflow-hidden border shadow-xl bg-neutral text-neutral-content border-neutral/50 rounded-3xl">
                        <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0" style="background-image: radial-gradient(currentColor 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>

                        <h3 class="text-[10px] font-black tracking-widest uppercase opacity-50 mb-4 border-b border-neutral-content/10 pb-3 relative z-10">Ringkasan Estimasi</h3>

                        <div class="relative z-10 space-y-3 text-sm font-medium">
                            <div class="flex items-center justify-between">
                                <span class="opacity-70">Total Produk</span>
                                <span>{{ formatRupiah(subtotalProduk) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="opacity-70">Biaya Pengiriman</span>
                                <span>{{ penawaran.harga_ongkir > 0 ? '+ ' + formatRupiah(penawaran.harga_ongkir) : 'Rp 0' }}</span>
                            </div>
                        </div>

                        <div class="relative z-10 pt-4 mt-4 border-t border-dashed border-neutral-content/20">
                            <div class="flex-row items-end justify-between">
                                <div>
                                    <div class="text-[10px] font-black tracking-widest uppercase opacity-50 mb-1">Estimasi Total</div>
                                </div>
                                <div class="text-2xl font-black text-primary">{{ formatRupiah(grandTotal) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- TOMBOL AKSI BAWAH (Jika perlu, misal Banner notifikasi SO) -->
                    <div class="space-y-3">
                        <div v-if="penawaran.id_pesan_terkait" class="p-4 mb-4 text-sm font-bold text-center border bg-success/10 text-success border-success/30 rounded-2xl">
                            <CheckCircle2 class="w-6 h-6 mx-auto mb-2" />
                            Penawaran ini sudah diproses menjadi Pesanan Resmi:<br>
                            <Link :href="route('pesan.detail', penawaran.id_pesan_terkait)" class="underline hover:opacity-70">{{ penawaran.id_pesan_terkait }}</Link>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <CustomAlertConfirm
            :show="showConfirm"
            :type="confirmAction === 'ditolak' ? 'danger' : (confirmAction === 'disetujui' || confirmAction === 'buat_so' ? 'success' : 'info')"
            :title="confirmTitle"
            :message="confirmMessage"
            :confirmText="confirmAction === 'ditolak' ? 'Ya, Tolak' : (confirmAction === 'disetujui' ? 'Ya, ACC Penawaran' : (confirmAction === 'buat_so' ? 'Ya, Buat SO' : 'Ya, Tandai Terkirim'))"
            cancelText="Batal"
            :loading="isProcessing"
            @close="showConfirm = false"
            @confirm="executeUpdateStatus"
        />
    </StafLayout>
</template>
