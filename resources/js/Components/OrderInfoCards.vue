<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import CustomInput from '@/Components/CustomInput.vue';
import CustomInputNumber from '@/Components/CustomInputNumber.vue';
import CustomButton from '@/Components/CustomButton.vue';

const props = defineProps({
    pesanan: Object,
    total_tagihan: Number,
    total_dibayar: Number,
    enumPembayaran: Array,
    enumOperasional: Array,
});

const emit = defineEmits(['openResiModal']);

const formatTanggal = (tgl) => {
    if (!tgl) return '-';
    return String(tgl).replace('T', ' ').substring(0, 16);
};

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka);
};

const formatEnum = (text) => {
    if (!text) return '';
    return text.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const totalBeratPesanan = computed(() => {
    if (!props.pesanan || !props.pesanan.pesanan_item) return 0;
    return props.pesanan.pesanan_item.reduce((sum, item) => sum + (Number(item.total_berat_snapshot) || 0), 0);
});

// --- LOGIKA UPDATE STATUS (OPERASIONAL & PEMBAYARAN) ---
const formOperasional = useForm({ status_operasional: '' });
const formPembayaran = useForm({ status_pembayaran: '', nominal_bayar: null });

const showBayarSebagianModal = ref(false);

const sudahAdaPembayaran = computed(() => (props.total_dibayar ?? 0) > 0);
const sudahLunas = computed(() => (props.total_dibayar ?? 0) >= (props.total_tagihan ?? 0));

const getAllowedOperasional = (statusSaatIni) => {
    if (!props.enumOperasional || props.enumOperasional.length === 0) return [];
    const alurProses = props.enumOperasional;
    const currentIndex = alurProses.indexOf(statusSaatIni);
    const indexBatal = alurProses.length - 1;
    const indexSelesai = alurProses.length - 2;

    if (currentIndex === -1 || currentIndex >= indexSelesai) return [statusSaatIni];
    return [statusSaatIni, alurProses[currentIndex + 1], alurProses[indexBatal]];
};

const updateOperasional = (value) => {
    formOperasional.status_operasional = value;
    formOperasional.put(route('pesan.updateOperasional', props.pesanan.id_pesan), {
        preserveScroll: true,
        onSuccess: () => alertStore.show('Status Operasional berhasil diperbarui!', 'success'),
        onError: () => alertStore.show('Gagal memperbarui status operasional!', 'error')
    });
};

const updatePembayaran = (value) => {
    if (sudahLunas.value) return;

    if (value === 'dibayar_sebagian') {
        formPembayaran.reset();
        formPembayaran.status_pembayaran = 'dibayar_sebagian';
        showBayarSebagianModal.value = true;
        return;
    }

    formPembayaran.status_pembayaran = value;
    formPembayaran.put(route('pesan.updatePembayaran', props.pesanan.id_pesan), {
        preserveScroll: true,
        onSuccess: () => alertStore.show('Status Pembayaran berhasil diperbarui!', 'success'),
        onError: () => alertStore.show('Gagal memperbarui status pembayaran!', 'error')
    });
};

const submitBayarSebagian = () => {
    formPembayaran.put(route('pesan.updatePembayaran', props.pesanan.id_pesan), {
        preserveScroll: true,
        onSuccess: () => {
            resetModalBayar();
            alertStore.show('Pembayaran berhasil dicatat!', 'success');
        },
        onError: () => alertStore.show('Gagal mencatat pembayaran!', 'error')
    });
};

const resetModalBayar = () => {
    formPembayaran.reset();
    formPembayaran.clearErrors();
    showBayarSebagianModal.value = false;
};

// --- LOGIKA GANTI ALAMAT ---
const modalAlamat = ref(null);
const formAlamat = useForm({ id_alamat: props.pesanan?.id_alamat || '' });

const daftarAlamat = computed(() => {
    const alamats = props.pesanan?.customer?.alamat || [];
    return [...alamats].sort((a, b) => b.is_default - a.is_default);
});

const openAlamatModal = () => {
    formAlamat.id_alamat = props.pesanan?.id_alamat || '';
    modalAlamat.value.showModal();
};

const submitAlamat = () => {
    formAlamat.put(route('pesan.updateAlamat', props.pesanan.id_pesan), {
        preserveScroll: true,
        onSuccess: () => {
            modalAlamat.value.close();
            alertStore.show('Alamat pengiriman berhasil diubah!', 'success');
        }
    });
};

// --- PEWARNAAN CLASS ---
const statusOperasionalClass = (status) => {
    switch (status) {
        case 'menunggu_diproses': return 'text-info border-info/30 bg-info/5';
        case 'proses_pengerjaan': return 'text-warning border-warning/30 bg-warning/5';
        case 'proses_pengantaran': return 'text-accent border-accent/30 bg-accent/5';
        case 'selesai': return 'text-success border-success/30 bg-success/5';
        case 'batal': return 'text-error border-error/30 bg-error/5 line-through';
        default: return 'text-base-content border-base-300 bg-base-200/20';
    }
};

const statusPembayaranClass = (status) => {
    switch (status) {
        case 'lunas': return 'text-success border-success/30 bg-success/5';
        case 'dibayar_sebagian': return 'text-warning border-warning/30 bg-warning/5';
        case 'belum_lunas': return 'text-error border-error/30 bg-error/5';
        default: return 'text-base-content border-base-300 bg-base-200/20';
    }
};
</script>

<template>
    <div class="grid grid-cols-1 gap-5 mb-8 md:grid-cols-2 lg:grid-cols-4">

        <!-- CARD 1: PELANGGAN (Sama seperti sebelumnya) -->
        <div class="relative flex flex-col p-5 border shadow-sm bg-base-100 rounded-3xl border-base-200/60 group">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-1.5 text-primary opacity-80">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" /></svg>
                    <span class="text-[10px] font-black uppercase tracking-widest">Pelanggan</span>
                </div>
                <button v-if="pesanan.customer" @click="openAlamatModal" class="flex items-center gap-1 px-2.5 py-1 text-[9px] font-black tracking-widest uppercase transition-all rounded-lg text-primary bg-primary/10 hover:bg-primary hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3"><path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z" /><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" /></svg>
                    Ubah
                </button>
            </div>

            <div>
                <h3 class="text-sm font-black uppercase truncate text-base-content">{{ pesanan.customer?.user?.name || 'Walk-in / Umum' }}</h3>
                <p class="text-[10px] font-mono opacity-50 mt-0.5">{{ pesanan.id_customer }}</p>

                <div v-if="pesanan.alamat" class="pt-3 mt-3 space-y-2 border-t border-dashed border-base-200/50">
                    <p v-if="pesanan.alamat.no_hp" class="text-[11px] font-bold opacity-80 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 opacity-50"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 013.5 2h1.148a1.5 1.5 0 011.465 1.175l.716 3.223a1.5 1.5 0 01-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 006.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 011.767-1.052l3.223.716A1.5 1.5 0 0118 15.352V16.5a1.5 1.5 0 01-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 012.43 8.326 13.019 13.019 0 012 5V3.5z" clip-rule="evenodd" /></svg>
                        {{ pesanan.alamat.no_hp }}
                    </p>
                    <p class="text-[11px] leading-relaxed opacity-70 line-clamp-2 flex items-start gap-2" :title="pesanan.alamat.alamat_lengkap">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 mt-0.5 opacity-50 shrink-0"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" /></svg>
                        {{ pesanan.alamat.alamat_lengkap || 'Alamat lengkap tidak tersedia' }}
                    </p>
                    <p v-if="pesanan.alamat.kota" class="text-[9px] font-black uppercase opacity-40 pl-5.5">
                        {{ pesanan.alamat.kecamatan }}, {{ pesanan.alamat.kota }}, {{ pesanan.alamat.provinsi }}
                    </p>
                </div>
            </div>
        </div>

        <!-- CARD 2: PENGIRIMAN (Sama seperti sebelumnya) -->
        <div class="flex flex-col grow p-5 border shadow-sm bg-base-100 rounded-3xl border-base-200/60">
            <div class="flex items-center gap-1.5 text-info opacity-80 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path d="M6.5 3c-1.051 0-2.093.04-3.125.117A1.49 1.49 0 002 4.607V10.5h9V4.606c0-.771-.59-1.43-1.375-1.489A41.568 41.568 0 006.5 3zM2 12v2.5A1.5 1.5 0 003.5 16h.041a3 3 0 015.918 0h.791a.75.75 0 00.75-.75V12H2z" /><path d="M6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM13.25 5a.75.75 0 00-.75.75v8.514a3.001 3.001 0 014.893 1.44c.37-.275.61-.719.595-1.227a24.905 24.905 0 00-1.784-8.549A1.486 1.486 0 0014.823 5H13.25zM14.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" /></svg>
                <span class="text-[10px] font-black uppercase tracking-widest">Pengiriman</span>
            </div>

            <div class="flex flex-col grow">
                <template v-if="pesanan.ekspedisi_nama && pesanan.ekspedisi_nama !== 'Ambil di Toko'">
                    <h3 class="text-sm font-black uppercase text-base-content">{{ pesanan.ekspedisi_nama }}</h3>
                    <p class="text-[11px] font-bold opacity-60 mt-0.5">{{ pesanan.ekspedisi_layanan }} • {{ totalBeratPesanan }}g</p>

                    <div class="pt-4 mt-auto">
                        <div @click="emit('openResiModal')" class="flex items-center justify-between p-3 transition-colors border cursor-pointer bg-base-200/40 rounded-xl border-base-200 hover:border-info/50 group">
                            <div class="flex flex-col min-w-0">
                                <span class="text-[9px] font-black uppercase opacity-40 tracking-widest mb-0.5">Nomor Resi</span>
                                <span class="font-mono text-xs font-bold truncate transition-colors text-base-content group-hover:text-info">
                                    {{ pesanan.nomor_resi || 'Input Nomor Resi' }}
                                </span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 transition-colors opacity-30 group-hover:text-info shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="flex items-center justify-center grow p-4 mt-2 border border-dashed rounded-xl border-base-200/80 bg-base-200/20">
                        <p class="text-xs font-black tracking-widest uppercase opacity-40">Ambil di Toko</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- CARD 3: STATUS (SEKARANG BISA DIUPDATE LEWAT DROPDOWN SELECT) -->
        <div class="flex flex-col p-5 border shadow-sm bg-base-100 rounded-3xl border-base-200/60">
            <div class="flex items-center gap-1.5 text-warning opacity-80 mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2a.75.75 0 000 1.5h2.75a.75.75 0 00.75-.75v-3.25z" clip-rule="evenodd" /></svg>
                <span class="text-[10px] font-black uppercase tracking-widest">Status Pesanan</span>
            </div>

            <div class="flex flex-col gap-4">
                <div>
                    <span class="text-[9px] font-black uppercase opacity-40 block mb-1.5 tracking-widest">Operasional</span>
                    <select
                        class="w-full select select-bordered select-sm text-[10px] font-black uppercase tracking-wider shadow-sm outline-none focus:outline-none"
                        :class="statusOperasionalClass(pesanan.status_operasional)"
                        :value="pesanan.status_operasional"
                        @change="updateOperasional($event.target.value)"
                        :disabled="formOperasional.processing || pesanan.status_operasional === 'batal' || pesanan.status_operasional === 'selesai'"
                    >
                        <option
                            v-for="status in getAllowedOperasional(pesanan.status_operasional)"
                            :key="status"
                            :value="status"
                            :disabled="status === pesanan.status_operasional"
                        >
                            {{ formatEnum(status) }}
                        </option>
                    </select>
                </div>

                <div class="pt-4 border-t border-base-200/50">
                    <span class="text-[9px] font-black uppercase opacity-40 block mb-1.5 tracking-widest">Pembayaran</span>
                    <select
                        class="w-full select select-bordered select-sm text-[10px] font-black uppercase tracking-wider shadow-sm outline-none focus:outline-none"
                        :class="statusPembayaranClass(pesanan.status_pembayaran)"
                        :value="pesanan.status_pembayaran"
                        @change="updatePembayaran($event.target.value)"
                        :disabled="formPembayaran.processing || sudahLunas || pesanan.status_operasional === 'batal'"
                    >
                        <option
                            v-for="status in enumPembayaran"
                            :key="status"
                            :value="status"
                            :disabled="(status === 'belum_lunas' && sudahAdaPembayaran) || (status === 'dibayar_sebagian' && sudahLunas)"
                        >
                            {{ status.replace(/_/g, ' ').toUpperCase() }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- CARD 4: WAKTU & DEADLINE (DENGAN TANGGAL SELESAI/DITERIMA) -->
        <div class="flex flex-col p-5 border shadow-sm bg-base-100 rounded-3xl border-base-200/60">
            <div class="flex items-center gap-1.5 text-accent opacity-80 mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" /></svg>
                <span class="text-[10px] font-black uppercase tracking-widest">Garis Waktu</span>
            </div>

            <div class="flex flex-col gap-4">
                <div>
                    <span class="text-[9px] font-black uppercase opacity-40 block mb-1 tracking-widest">Dibuat Pada</span>
                    <span class="font-mono text-xs font-bold text-base-content">{{ formatTanggal(pesanan.tanggal_pesan) }}</span>
                </div>

                <div class="pt-4 border-t border-dashed border-base-200/80">
                    <!-- Jika status operasional sudah selesai (Barang diterima pelanggan) -->
                    <template v-if="pesanan.status_operasional === 'selesai'">
                        <span class="flex items-center gap-1.5 mb-1.5 text-[9px] font-black tracking-widest text-success uppercase">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                            Selesai & Diterima
                        </span>
                        <div class="inline-flex px-3 py-1.5 font-mono text-[11px] font-black rounded-lg text-success bg-success/10 border border-success/20">
                            {{ formatTanggal(pesanan.tanggal_selesai) }}
                        </div>
                    </template>
                    <!-- Jika belum selesai tapi sudah punya deadline (Sudah Lunas/DP) -->
                    <template v-else-if="pesanan.waktu_deadline">
                        <span class="flex items-center gap-1.5 mb-1.5 text-[9px] font-black tracking-widest text-warning uppercase">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Deadline Cetak
                        </span>
                        <div class="inline-flex px-3 py-1.5 font-mono text-[11px] font-black rounded-lg text-warning bg-warning/10 border border-warning/20">
                            {{ formatTanggal(pesanan.waktu_deadline) }}
                        </div>
                    </template>
                    <!-- Jika belum dibayar sama sekali -->
                    <template v-else>
                        <span class="text-[9px] font-black uppercase opacity-40 block mb-1 tracking-widest">Deadline Cetak</span>
                        <span class="text-[10px] font-bold italic opacity-50">Menunggu Pembayaran Masuk</span>
                    </template>
                </div>
            </div>
        </div>

    </div>

    <!-- MODAL GANTI ALAMAT (Sama seperti sebelumnya) -->
    <dialog ref="modalAlamat" class="modal modal-bottom sm:modal-middle">
        <div class="border shadow-2xl modal-box bg-base-100 rounded-3xl border-base-200">
            <h3 class="text-lg font-black tracking-tight text-base-content">Pilih Alamat Pengiriman</h3>
            <p class="py-2 text-xs font-medium opacity-60">Pilih salah satu alamat yang terdaftar untuk pelanggan ini.</p>

            <div class="mt-4 space-y-3 max-h-[50vh] overflow-y-auto pr-2 custom-scrollbar">
                <label v-for="al in daftarAlamat" :key="al.id_alamat"
                       class="flex items-start gap-4 p-4 transition-all border cursor-pointer rounded-2xl"
                       :class="formAlamat.id_alamat === al.id_alamat ? 'border-primary bg-primary/5 shadow-sm' : 'border-base-200 hover:border-base-300'">
                    <input type="radio" :value="al.id_alamat" v-model="formAlamat.id_alamat" class="mt-0.5 radio radio-primary radio-sm shrink-0" />
                    <div>
                        <p class="flex items-center gap-2 text-xs font-black uppercase text-base-content">
                            {{ al.label || 'Alamat' }}
                            <span v-if="al.is_default" class="text-[8px] bg-primary text-primary-content px-1.5 py-0.5 rounded uppercase font-bold tracking-widest">Utama</span>
                        </p>
                        <p class="mt-1 text-[11px] font-bold opacity-80">{{ al.nama_penerima }} • {{ al.no_hp }}</p>
                        <p class="mt-1 text-[10px] leading-relaxed opacity-70">{{ al.alamat_lengkap }}, {{ al.kecamatan }}, {{ al.kota }}, {{ al.provinsi }} {{ al.kode_pos }}</p>
                    </div>
                </label>

                <div v-if="daftarAlamat.length === 0" class="py-6 text-xs italic font-bold text-center opacity-50">
                    Pelanggan ini belum memiliki alamat terdaftar.
                </div>
            </div>

            <div class="mt-6 modal-action">
                <button type="button" @click="modalAlamat.value.close()" class="px-5 font-bold tracking-wider uppercase btn btn-sm btn-ghost rounded-xl text-[10px]">Batal</button>
                <button @click="submitAlamat" class="btn btn-sm btn-primary rounded-xl font-black tracking-widest text-[10px] uppercase shadow-md shadow-primary/20 px-6" :disabled="formAlamat.processing || !formAlamat.id_alamat || daftarAlamat.length === 0">
                    <span v-if="formAlamat.processing" class="loading loading-spinner loading-xs"></span>
                    Simpan Alamat
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button @click="modalAlamat.value.close()">close</button></form>
    </dialog>

    <!-- MODAL PEMBAYARAN SEBAGIAN (DP) -->
    <dialog class="modal" :class="{ 'modal-open': showBayarSebagianModal }">
        <div class="border shadow-2xl modal-box bg-base-100 rounded-3xl border-base-200">
            <h3 class="text-lg font-black tracking-tight text-base-content">Pembayaran Sebagian (DP)</h3>

            <div class="mt-4 space-y-4">
                <div>
                    <CustomInput
                        label="Total Tagihan"
                        :model-value="formatRupiah(total_tagihan)"
                        disabled
                    />
                </div>
                <div>
                    <CustomInputNumber
                        v-model="formPembayaran.nominal_bayar"
                        label="Nominal Bayar Sekarang"
                        type="number"
                        placeholder="Masukkan nominal DP/Cicilan"
                        :error="formPembayaran.errors.nominal_bayar"
                    />
                </div>
                <div class="p-3 text-xs border rounded-xl bg-base-200/50 border-base-200">
                    <div class="flex justify-between mb-1">
                        <span class="font-bold opacity-60">Sudah Dibayar Sebelumnya:</span>
                        <span class="font-black text-base-content">{{ formatRupiah(total_dibayar ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-bold opacity-60">Sisa Tagihan Nanti:</span>
                        <span class="font-black text-error">{{ formatRupiah(total_tagihan - (total_dibayar ?? 0) - (formPembayaran.nominal_bayar || 0)) }}</span>
                    </div>
                </div>
            </div>

            <div class="justify-end mt-6 modal-action">
                <CustomButton variant="secondary" @click="resetModalBayar" class="rounded-xl px-5 text-[10px] font-bold tracking-wider uppercase">Batal</CustomButton>
                <CustomButton variant="primary" class="rounded-xl shadow-md shadow-primary/20 text-[10px] font-black tracking-widest uppercase px-6" :disabled="!formPembayaran.nominal_bayar || formPembayaran.processing" @click="submitBayarSebagian">
                    <span v-if="formPembayaran.processing" class="loading loading-spinner loading-xs"></span>
                    Simpan DP
                </CustomButton>
            </div>
        </div>
    </dialog>

</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: oklch(var(--bc) / 0.1); border-radius: 10px; }
select { appearance: none; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 0.5rem center; background-size: 1em; padding-right: 2rem; }
</style>
