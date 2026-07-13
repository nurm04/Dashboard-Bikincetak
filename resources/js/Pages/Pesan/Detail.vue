<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/CustomInput.vue';
import { alertStore } from '@/Utils/alertStore';

const props = defineProps({
    pesanan: Object,
    total_tagihan: Number,
    kode_unik: Number,
    total_transfer: Number,
    total_dibayar: Number,
    sisa_tagihan: Number,
});

const modalResi = ref(null);

const formResi = useForm({
    nomor_resi: props.pesanan?.nomor_resi || '',
});

const openModalResi = () => {
    formResi.nomor_resi = props.pesanan?.nomor_resi || '';
    formResi.clearErrors();
    modalResi.value.showModal();
};

const closeModalResi = () => {
    modalResi.value.close();
};

const submitResi = () => {
    formResi.put(route('pesan.updateResi', props.pesanan.id_pesan), {
        preserveScroll: true,
        onSuccess: () => {
            closeModalResi();
            alertStore.show('Nomor Resi berhasil disimpan!', 'success');
        },
        onError: () => {
            alertStore.show('Gagal menyimpan Nomor Resi!', 'error');
        }
    });
};

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka || 0);
};

const formatTanggal = (tgl) => {
    if (!tgl) return '-';
    return String(tgl).replace('T', ' ').substring(0, 16);
};

const totalBeratPesanan = computed(() => {
    if (!props.pesanan || !props.pesanan.pesanan_item) return 0;
    return props.pesanan.pesanan_item.reduce((sum, item) => sum + (Number(item.total_berat_snapshot) || 0), 0);
});

const statusOperasionalClass = (status) => {
    switch (status) {
        case 'menunggu_diproses': return 'bg-info/10 text-info border-info/20';
        case 'proses_pengerjaan': return 'bg-warning/10 text-warning border-warning/20';
        case 'proses_pengantaran': return 'bg-accent/10 text-accent border-accent/20';
        case 'selesai': return 'bg-success/10 text-success border-success/20';
        case 'batal': return 'opacity-60 bg-error/10 text-error border-error/20 line-through';
        default: return 'bg-base-300 text-base-content';
    }
};

const statusPembayaranClass = (status) => {
    switch (status) {
        case 'lunas': return 'bg-success/10 text-success border-success/20';
        case 'dibayar_sebagian': return 'bg-warning/10 text-warning border-warning/20';
        case 'belum_lunas': return 'bg-error/10 text-error border-error/20';
        default: return 'bg-base-300 text-base-content';
    }
};
</script>

<template>
    <Head :title="`Detail Pesanan #${pesanan.id_pesan}`" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-xl font-bold leading-tight text-base-content">
                    Detail Pesanan {{ pesanan.id_pesan }}
                </h2>

                <a :href="route('pesan.cetakLabel', pesanan.id_pesan)" target="_blank" class="btn btn-sm btn-outline shadow-sm font-black uppercase tracking-widest text-[11px]">
                    🖨️ Cetak Label
                </a>
            </div>
        </template>

        <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid items-start grid-cols-1 gap-6 lg:grid-cols-12">

                <div class="space-y-6 lg:col-span-8">

                    <div class="border shadow-sm card bg-base-100 border-base-300 rounded-3xl">
                        <div class="p-6 card-body">
                            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                                <div>
                                    <h3 class="text-[10px] font-black uppercase tracking-widest opacity-40 mb-3 flex items-center gap-2">👤 Informasi Pelanggan</h3>
                                    <p class="text-sm font-black uppercase text-base-content">{{ pesanan.customer?.user?.name || 'Walk-in / Umum' }}</p>
                                    <p class="mt-1 font-mono text-xs font-bold opacity-60">{{ pesanan.id_customer }}</p>
                                    <p v-if="pesanan.alamat?.no_hp" class="mt-2 text-xs font-bold opacity-80">📞 {{ pesanan.alamat.no_hp }}</p>
                                </div>

                                <div>
                                    <h3 class="text-[10px] font-black uppercase tracking-widest opacity-40 mb-3 flex items-center gap-2">🚚 Pengiriman</h3>
                                    <template v-if="pesanan.ekspedisi_nama && pesanan.ekspedisi_nama !== 'Ambil di Toko'">
                                        <p class="text-sm font-black uppercase text-base-content">{{ pesanan.ekspedisi_nama }} - {{ pesanan.ekspedisi_layanan }}</p>

                                        <div class="flex flex-col gap-2 mt-2">
                                            <p class="text-xs font-bold opacity-70">⚖️ Berat: {{ totalBeratPesanan }} gr</p>

                                            <div class="mt-4">
                                                <div
                                                    class="relative transition-all cursor-pointer group hover:scale-[1.01]"
                                                    @click="openModalResi"
                                                >
                                                    <CustomInput
                                                        label="Nomor Resi Pengiriman"
                                                        :modelValue="pesanan.nomor_resi || 'Belum diinput'"
                                                        disabled="true"
                                                        class="pointer-events-none opacity-80 group-hover:opacity-100"
                                                    />

                                                    <div class="absolute transition-colors right-3 top-7 text-base-content/40 group-hover:text-primary">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                        </svg>
                                                    </div>
                                                </div>
                                                <p v-if="pesanan.status_operasional === 'proses_pengantaran' && !pesanan.nomor_resi" class="text-[9px] text-error mt-1.5 font-bold flex items-center gap-1 animate-pulse">
                                                    ⚠️ Resi wajib diisi agar pesanan dapat dilacak
                                                </p>
                                            </div>
                                        </div>

                                    </template>
                                    <template v-else>
                                        <p class="text-sm font-bold uppercase opacity-60">Ambil di Toko / Walk-In</p>
                                    </template>
                                </div>

                                <div class="pt-4 border-t border-dashed md:col-span-2 border-base-200">
                                    <h3 class="text-[10px] font-black uppercase tracking-widest opacity-40 mb-2">📍 Alamat Tujuan / Nota</h3>
                                    <p v-if="pesanan.alamat" class="max-w-2xl text-xs font-bold leading-relaxed opacity-80">
                                        <span class="text-base-content">{{ pesanan.alamat.nama_penerima }}</span><br>
                                        {{ pesanan.alamat.alamat_lengkap }}<br>{{ pesanan.alamat.kecamatan }}, {{ pesanan.alamat.kota }}, {{ pesanan.alamat.provinsi }} {{ pesanan.alamat.kode_pos }}
                                    </p>
                                    <p v-else class="font-mono text-xs font-bold opacity-60">ID Alamat: {{ pesanan.id_alamat || '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border shadow-sm card bg-base-100 border-base-300 rounded-3xl">
                        <div class="p-6 card-body">
                            <h3 class="pb-4 mb-2 text-xs font-black tracking-widest uppercase border-b opacity-50 border-base-200">📦 Daftar Item Cetak</h3>

                            <div class="divide-y divide-base-200">
                                <div v-for="(item, index) in pesanan.pesanan_item" :key="item.id" class="flex flex-col justify-between gap-6 py-6 first:pt-4 last:pb-0 md:flex-row">

                                    <div class="flex-1 space-y-3">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span class="text-[10px] font-mono font-black text-primary bg-primary/10 px-2.5 py-1 rounded-lg">ITEM #{{ index + 1 }}</span>
                                            <span class="text-[10px] font-black uppercase text-warning tracking-wider flex items-center gap-1">⏳ SLA: {{ item.estimasi_pengerjaan_snapshot }}</span>
                                        </div>

                                        <h4 class="text-lg font-black tracking-tight uppercase text-base-content">{{ item.nama_produk_snapshot }}</h4>

                                        <div v-if="item.pesanan_item_finishing?.length" class="pl-4 space-y-1 text-xs font-bold border-l-2 border-primary/30 opacity-80">
                                            <p v-for="fin in item.pesanan_item_finishing" :key="fin.id" class="flex justify-between max-w-sm">
                                                <span>+ {{ fin.nama_finishing_snapshot }}</span>
                                                <span class="ml-4 font-mono opacity-50">{{ formatRupiah(fin.harga_finishing_snapshot) }}</span>
                                            </p>
                                        </div>

                                        <div v-if="item.catatan" class="max-w-xl p-3 text-xs font-medium border bg-warning/10 border-warning/20 rounded-xl text-warning-content">
                                            <span class="font-black block text-[9px] uppercase tracking-wider opacity-60 mb-1">Catatan Pelanggan:</span>
                                            "{{ item.catatan }}"
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-end w-full gap-1 p-4 mt-4 text-right bg-base-200/30 md:bg-transparent rounded-2xl md:rounded-none md:w-auto md:mt-0 md:p-0">

                                        <div class="text-[10px] font-bold opacity-70 w-full mb-2">
                                            <div class="flex items-center justify-between gap-4 md:justify-end">
                                                <span>Harga Dasar <span class="opacity-50">(Stlh Diskon)</span>:</span>
                                                <div class="flex items-center gap-2">
                                                    <span v-if="item.total_diskon_snapshot > 0" class="line-through text-[9px] text-error opacity-60">
                                                        {{ formatRupiah(item.harga_dasar_awal_snapshot) }}
                                                    </span>
                                                    <span class="font-mono text-base-content">{{ formatRupiah(item.harga_satuan_snapshot) }}</span>
                                                </div>
                                            </div>

                                            <div v-if="item.rincian_diskon_snapshot && item.rincian_diskon_snapshot.length > 0" class="flex flex-col items-end gap-1 mt-1">
                                                <span v-for="(diskon, dIdx) in item.rincian_diskon_snapshot" :key="dIdx" class="text-[9px] text-success bg-success/10 px-1.5 py-0.5 rounded uppercase font-bold tracking-wider">
                                                    ✨ {{ diskon.nama }}: -{{ formatRupiah(diskon.nominal) }}
                                                </span>
                                            </div>

                                            <div v-if="item.pesanan_item_finishing?.length" class="flex items-center justify-between gap-4 mt-1 md:justify-end">
                                                <span>Total Finishing:</span>
                                                <span class="font-mono text-primary">+ {{ formatRupiah(item.pesanan_item_finishing.reduce((a, b) => a + b.harga_finishing_snapshot, 0)) }}</span>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between w-full pt-2 border-t border-base-200 md:justify-end">
                                            <span class="text-xs font-black opacity-80">
                                                {{ item.jumlah }} pcs × {{ formatRupiah(item.harga_satuan_snapshot + (item.pesanan_item_finishing?.reduce((acc, fin) => acc + fin.harga_finishing_snapshot, 0) || 0)) }}
                                            </span>
                                        </div>

                                        <span v-if="item.harga_pengerjaan_snapshot > 0" class="text-[10px] font-black text-warning bg-warning/10 px-2 py-0.5 rounded block mt-1">
                                            + Biaya SLA: {{ formatRupiah(item.harga_pengerjaan_snapshot) }} (Flat)
                                        </span>

                                        <span class="block mt-2 text-xl font-black tracking-tight text-primary">
                                            {{ formatRupiah(((item.harga_satuan_snapshot + (item.pesanan_item_finishing?.reduce((acc, fin) => acc + fin.harga_finishing_snapshot, 0) || 0)) * item.jumlah) + item.harga_pengerjaan_snapshot) }}
                                        </span>

                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <template v-if="item.file_desain && item.file_desain.length > 0">
                                                <a 
                                                    v-for="(file, fIdx) in item.file_desain" 
                                                    :key="fIdx" 
                                                    :href="'/storage/' + file" 
                                                    target="_blank" 
                                                    class="btn btn-sm btn-primary rounded-xl font-black uppercase tracking-widest text-[10px] shadow-sm hover:scale-105 transition-transform"
                                                >
                                                    📁 File {{ fIdx + 1 }}
                                                </a>
                                            </template>
                                            
                                            <template v-else>
                                                <span class="block text-center md:text-right text-[10px] font-black uppercase text-error/60 tracking-wider">
                                                    ❌ Tidak Ada Desain
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 lg:col-span-4">
                    <div class="border shadow-sm card bg-base-100 border-base-300 rounded-3xl">
                        <div class="p-6 space-y-5 card-body">
                            <h3 class="pb-2 text-xs font-black tracking-widest uppercase border-b opacity-50 border-base-200">Status Pesanan</h3>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[9px] font-black uppercase tracking-widest opacity-40 block mb-1.5">Pembayaran</label>
                                    <span class="px-2.5 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider border block text-center" :class="statusPembayaranClass(pesanan.status_pembayaran)">{{ pesanan.status_pembayaran.replace('_', ' ') }}</span>
                                </div>
                                <div>
                                    <label class="text-[9px] font-black uppercase tracking-widest opacity-40 block mb-1.5">Operasional</label>
                                    <span class="px-2.5 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider border block text-center" :class="statusOperasionalClass(pesanan.status_operasional)">{{ pesanan.status_operasional.replace('_', ' ') }}</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-dashed border-base-200">
                                <div>
                                    <label class="text-[9px] font-black uppercase tracking-widest opacity-40 block">Waktu Order</label>
                                    <span class="text-[11px] font-bold font-mono text-base-content block mt-1">{{ formatTanggal(pesanan.tanggal_pesan) }}</span>
                                </div>
                                <div>
                                    <label class="text-[9px] font-black uppercase tracking-widest opacity-40 block">Waktu Selesai</label>
                                    <span class="text-[11px] font-bold font-mono text-base-content block mt-1">{{ formatTanggal(pesanan.tanggal_selesai) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border shadow-sm card bg-base-100 border-base-300 rounded-3xl">
                        <div class="p-6 space-y-4 card-body">
                            <h3 class="pb-2 text-xs font-black tracking-widest uppercase border-b opacity-50 border-base-200">Rincian Tagihan</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-bold opacity-70">Total Produk</span>
                                    <span class="font-mono font-bold text-base-content">{{ formatRupiah(total_tagihan) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-bold opacity-70">Ongkos Kirim</span>
                                    <span class="font-mono font-bold" :class="pesanan.harga_ongkir > 0 ? 'text-success' : 'text-base-content'">+ {{ formatRupiah(pesanan.harga_ongkir || 0) }}</span>
                                </div>

                                <div v-if="pesanan.diskon_voucher_nominal > 0" class="flex items-center justify-between text-sm">
                                    <span class="font-bold opacity-70">Voucher ({{ pesanan.kode_voucher || 'Promo' }})</span>
                                    <span class="font-mono font-bold text-error">- {{ formatRupiah(pesanan.diskon_voucher_nominal) }}</span>
                                </div>

                                <div v-if="kode_unik" class="flex items-center justify-between text-sm">
                                    <span class="font-bold opacity-70">Kode Unik</span>
                                    <span class="font-mono font-bold text-warning">+ {{ formatRupiah(kode_unik) }}</span>
                                </div>
                            </div>

                            <div class="pt-4 border-t-2 border-dashed border-base-300">
                                <div class="flex items-end justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-widest opacity-40 pb-1">Grand Total</span>
                                    <span class="text-2xl font-black leading-none tracking-tighter text-primary">{{ formatRupiah(total_transfer) }}</span>
                                </div>
                            </div>

                            <div class="pt-4 mt-2 space-y-2 border-t border-base-200">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-bold opacity-70">Telah Dibayar (DP)</span>
                                    <span class="font-mono font-bold text-success">- {{ formatRupiah(total_dibayar) }}</span>
                                </div>
                                <div class="flex items-center justify-between p-2 mt-2 text-sm rounded-lg bg-base-200/50">
                                    <span class="font-black text-error">Sisa Tagihan</span>
                                    <span class="font-mono font-black text-error">{{ formatRupiah(sisa_tagihan) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <dialog ref="modalResi" class="modal modal-bottom sm:modal-middle">
            <div class="border shadow-xl modal-box bg-base-100 rounded-3xl border-base-200">
                <h3 class="text-lg font-black tracking-tight text-base-content">Update Nomor Resi</h3>
                <p class="py-2 text-xs font-medium opacity-60">Masukkan nomor resi yang valid dari pihak ekspedisi untuk dikirimkan ke pelanggan.</p>

                <div class="mt-4">
                    <CustomInput
                        label="Nomor Resi"
                        v-model="formResi.nomor_resi"
                        placeholder="Contoh: JD0123456789"
                        :error="formResi.errors.nomor_resi"
                        :disabled="formResi.processing"
                    />
                </div>

                <div class="mt-6 modal-action">
                    <button type="button" @click="closeModalResi" class="btn btn-sm btn-ghost rounded-xl font-bold tracking-wider text-[11px] uppercase">
                        Batal
                    </button>
                    <button
                        @click="submitResi"
                        class="btn btn-sm btn-primary rounded-xl font-black tracking-widest text-[11px] uppercase"
                        :disabled="formResi.processing || !formResi.nomor_resi"
                    >
                        <span v-if="formResi.processing" class="loading loading-spinner loading-xs"></span>
                        Simpan Resi
                    </button>
                </div>
            </div>

            <form method="dialog" class="modal-backdrop">
                <button @click="closeModalResi">close</button>
            </form>
        </dialog>
    </StafLayout>
</template>

<style scoped>
.card {
    transition: all 0.2s ease-in-out;
}
.card:hover {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
}
</style>
