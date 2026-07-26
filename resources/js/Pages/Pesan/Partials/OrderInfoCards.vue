<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { alertStore } from '@/Utils/alertStore';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomInputNumber from '@/Components/Form/CustomInputNumber.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';

const props = defineProps({
    pesanan: Object,
    total_tagihan: Number,
    total_dibayar: Number,
    enumPembayaran: Array,
});

const emit = defineEmits(['openResiModal']);

const formatTanggal = (tgl) => {
    if (!tgl) return '-';

    const date = new Date(tgl);

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day} ${hours}:${minutes}`;
};

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka || 0);
};

const formatEnum = (text) => {
    if (!text) return '';
    return text.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const totalBeratPesanan = computed(() => {
    if (!props.pesanan || !props.pesanan.pesanan_item) return 0;
    return props.pesanan.pesanan_item.reduce((sum, item) => sum + (Number(item.total_berat_snapshot) || 0), 0);
});

// --- LOGIKA UPDATE STATUS PEMBAYARAN ---
const formPembayaran = useForm({ status_pembayaran: '', nominal_bayar: null });
const showBayarSebagianModal = ref(false);

const sudahAdaPembayaran = computed(() => (props.total_dibayar ?? 0) > 0);
const sudahLunas = computed(() => (props.total_dibayar ?? 0) >= (props.total_tagihan ?? 0));

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

// --- LOGIKA GANTI ALAMAT & ONGKIR ---
const ekspedisiOptions = [
    { id: 'Ambil di Toko', nama: 'Ambil di Toko (Rp 0)' },
    { id: 'Kurir Toko', nama: 'Kurir Lokal / Instan' },
    { id: 'jne', nama: 'JNE' },
    { id: 'sicepat', nama: 'SiCepat' },
    { id: 'jnt', nama: 'J&T' },
    { id: 'pos', nama: 'POS Indonesia' },
];

const manualLayananOptions = [
    { id: 'Gojek / Grab (Instan)', nama: 'Gojek / Grab (Instan)' },
    { id: 'Lalamove / Deliveree', nama: 'Lalamove / Deliveree' },
    { id: 'Kurir Toko (Motor)', nama: 'Kurir Toko (Motor)' },
    { id: 'Kurir Toko (Mobil)', nama: 'Kurir Toko (Mobil)' },
    { id: 'Titip Travel / Bus', nama: 'Titip Travel / Bus' },
    { id: 'Lainnya', nama: 'Lainnya' },
];

const layananOptions = ref([]);
const isLoadingOngkir = ref(false);
const modalAlamat = ref(null);

const formAlamat = useForm({
    id_alamat: '',
    ekspedisi_nama: '',
    ekspedisi_layanan: '',
    harga_ongkir: 0
});

const isManualEkspedisi = computed(() => ['Ambil di Toko', 'Kurir Toko'].includes(formAlamat.ekspedisi_nama));

const daftarAlamat = computed(() => {
    const alamats = props.pesanan?.customer?.alamat || [];
    return [...alamats].sort((a, b) => b.is_default - a.is_default);
});

// Buka Modal & Set Data Awal
const openAlamatModal = () => {
    formAlamat.id_alamat = props.pesanan?.id_alamat || '';

    // Parsing nama ekspedisi dari database ("JNE" / "JALUR NUGRAHA EKAKURIR (JNE)") jadi value kode kurir
    let ekspNameRaw = (props.pesanan?.ekspedisi_nama || '').toLowerCase();
    let matchedCode = 'Kurir Toko';

    if (ekspNameRaw.includes('ambil di toko')) matchedCode = 'Ambil di Toko';
    else if (ekspNameRaw.includes('kurir')) matchedCode = 'Kurir Toko';
    else if (ekspNameRaw.includes('jne')) matchedCode = 'jne';
    else if (ekspNameRaw.includes('sicepat')) matchedCode = 'sicepat';
    else if (ekspNameRaw.includes('j&t') || ekspNameRaw.includes('jnt')) matchedCode = 'jnt';
    else if (ekspNameRaw.includes('pos')) matchedCode = 'pos';

    formAlamat.ekspedisi_nama = matchedCode;
    formAlamat.ekspedisi_layanan = props.pesanan?.ekspedisi_layanan || '';
    formAlamat.harga_ongkir = props.pesanan?.harga_ongkir || 0;

    layananOptions.value = []; // Reset opsi layanan
    modalAlamat.value.showModal();
};

const closeModelAlamat = () => {
    modalAlamat.value.close();
};

const fetchOngkir = async () => {
    if (!formAlamat.id_alamat || !props.pesanan?.pesanan_item || isManualEkspedisi.value) return;

    isLoadingOngkir.value = true;
    layananOptions.value = [];
    formAlamat.ekspedisi_layanan = '';
    formAlamat.harga_ongkir = 0;

    try {
        // Format item agar sesuai dengan requirement payload API
        const itemsPayload = props.pesanan.pesanan_item.map(item => ({
            id_sku: item.id_sku,
            jumlah: item.jumlah,
            finishings: item.pesanan_item_finishing
        }));

        const response = await axios.post('/ongkir/calculate', {
            id_alamat: formAlamat.id_alamat,
            courier: formAlamat.ekspedisi_nama,
            items: itemsPayload
        });

        const data = response.data;
        let costs = [];

        if (data?.data && Array.isArray(data.data)) {
            costs = data.data.map(i => ({ id: i.service, nama: `${i.service} (${i.etd || '-'} Hari) - Rp ${formatRupiah(i.cost)}`, cost: i.cost }));
        } else if (data?.rajaongkir?.results?.[0]?.costs) {
            costs = data.rajaongkir.results[0].costs.map(s => ({ id: s.service, nama: `${s.service} (${s.cost[0]?.etd || '-'} Hari) - Rp ${formatRupiah(s.cost[0]?.value)}`, cost: s.cost[0]?.value }));
        }

        if (costs.length > 0) {
            layananOptions.value = costs;
        } else {
            alertStore.show('Layanan kurir tidak tersedia untuk rute tersebut.', 'error');
            formAlamat.ekspedisi_nama = 'Kurir Toko';
        }
    } catch (error) {
        alertStore.show('Gagal menghubungi server ongkir.', 'error');
        formAlamat.ekspedisi_nama = 'Kurir Toko';
    } finally {
        isLoadingOngkir.value = false;
    }
};

// Hitung Ongkir Otomatis Jika Alamat Berubah
watch(() => formAlamat.id_alamat, (newAlamat, oldAlamat) => {
    if (oldAlamat && newAlamat !== oldAlamat && !isManualEkspedisi.value) {
        fetchOngkir();
    }
});

// Trigger Perubahan Kurir
watch(() => formAlamat.ekspedisi_nama, async (newCourier, oldCourier) => {
    if (!oldCourier) return; // Prevent fire on open modal

    if (newCourier === 'Ambil di Toko') {
        layananOptions.value = [];
        formAlamat.ekspedisi_layanan = 'Ambil Sendiri';
        formAlamat.harga_ongkir = 0;
    } else if (newCourier === 'Kurir Toko') {
        layananOptions.value = [];
        formAlamat.ekspedisi_layanan = '';
        formAlamat.harga_ongkir = 0;
    } else {
        if (!formAlamat.id_alamat) return;
        await fetchOngkir();
    }
});

watch(() => formAlamat.ekspedisi_layanan, (newLayanan) => {
    if (!isManualEkspedisi.value && newLayanan) {
        const selected = layananOptions.value.find(l => l.id === newLayanan);
        if (selected) formAlamat.harga_ongkir = selected.cost;
    }
});

const submitAlamat = () => {
    if (formAlamat.ekspedisi_nama !== 'Ambil di Toko' && !formAlamat.ekspedisi_layanan) {
        alertStore.show('Pilih Layanan Pengiriman terlebih dahulu!', 'error');
        return;
    }

    const namaEkspedisiAsli = ekspedisiOptions.find(e => e.id === formAlamat.ekspedisi_nama)?.nama || formAlamat.ekspedisi_nama;
    const finalEkspedisiNama = isManualEkspedisi.value ? formAlamat.ekspedisi_nama : namaEkspedisiAsli.toUpperCase();

    const payload = {
        id_alamat: formAlamat.id_alamat,
        ekspedisi_nama: finalEkspedisiNama,
        ekspedisi_layanan: formAlamat.ekspedisi_nama === 'Ambil di Toko' ? 'Ambil Sendiri' : formAlamat.ekspedisi_layanan,
        harga_ongkir: formAlamat.ekspedisi_nama === 'Ambil di Toko' ? 0 : formAlamat.harga_ongkir,
    };

    formAlamat.transform(() => payload).put(route('pesan.updateAlamat', props.pesanan.id_pesan), {
        preserveScroll: true,
        onSuccess: () => {
            modalAlamat.value.close();
            alertStore.show('Alamat & Pengiriman berhasil diubah!', 'success');
        }
    });
};

// --- PEWARNAAN CLASS ---
const statusOperasionalClass = (status) => {
    switch (status) {
        case 'menunggu_diproses': return 'text-info border-info/30 bg-info/10';
        case 'proses_pengerjaan': return 'text-warning border-warning/30 bg-warning/10';
        case 'proses_pengantaran': return 'text-accent border-accent/30 bg-accent/10';
        case 'selesai': return 'text-success border-success/30 bg-success/10';
        case 'batal': return 'text-error border-error/30 bg-error/10 line-through opacity-70';
        default: return 'text-base-content border-base-300 bg-base-200';
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
    <!-- DESAIN BARU: 1 Container Grid Utama -->
    <div class="flex flex-col mb-8 overflow-hidden border shadow-sm lg:flex-row bg-base-100 rounded-3xl border-base-300 divide-y lg:divide-y-0 lg:divide-x divide-base-200">
        <!-- KELOMPOK 1: PELANGGAN -->
        <div class="flex flex-col flex-1 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-black uppercase tracking-widest opacity-40">Pelanggan</span>
            </div>
            <div class="grow">
                <h3 class="text-sm font-black uppercase truncate text-base-content">{{ pesanan.customer?.user?.name || 'Walk-in / Umum' }}</h3>
                <p class="text-[10px] font-mono opacity-50 mt-0.5">{{ pesanan.id_customer }}</p>

                <div v-if="pesanan.alamat" class="pt-3 mt-3 border-t border-dashed border-base-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[9px] font-bold tracking-widest uppercase opacity-40">Alamat Kirim</span>
                        <button
                            v-if="pesanan.customer && !['proses_pengantaran', 'selesai', 'batal'].includes(pesanan.status_operasional)"
                            @click="openAlamatModal"
                            class="flex items-center gap-1 px-2 py-1 text-[9px] font-bold tracking-widest uppercase transition-all rounded-md text-base-content/50 hover:bg-base-200 hover:text-base-content"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3"><path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z" /><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" /></svg>
                            Ubah
                        </button>
                    </div>
                    <div class="space-y-1.5">
                        <p v-if="pesanan.alamat.no_hp" class="text-[11px] font-bold opacity-80">{{ pesanan.alamat.no_hp }}</p>
                        <p class="text-[11px] leading-relaxed opacity-70 line-clamp-2" :title="pesanan.alamat.alamat_lengkap">
                            {{ pesanan.alamat.alamat_lengkap || 'Alamat lengkap tidak tersedia' }}
                        </p>
                        <p v-if="pesanan.alamat.kota" class="text-[9px] font-black uppercase opacity-40">
                            {{ pesanan.alamat.kecamatan }}, {{ pesanan.alamat.kota }}, {{ pesanan.alamat.provinsi }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- KELOMPOK 2: PENGIRIMAN -->
        <div class="flex flex-col flex-1 p-5">
            <div class="mb-3">
                <span class="text-[10px] font-black uppercase tracking-widest opacity-40">Pengiriman</span>
            </div>
            <div class="flex flex-col grow">
                <template v-if="pesanan.ekspedisi_nama && pesanan.ekspedisi_nama !== 'Ambil di Toko'">
                    <h3 class="text-sm font-black uppercase text-base-content">{{ pesanan.ekspedisi_nama }}</h3>
                    <p class="text-[11px] font-bold opacity-60 mt-0.5">{{ pesanan.ekspedisi_layanan }} • {{ totalBeratPesanan }}g</p>

                    <div class="pt-3 mt-auto border-t border-dashed border-base-200">
                        <span class="block mb-1 text-[9px] font-black tracking-widest uppercase opacity-40">Nomor Resi</span>
                        <div @click="emit('openResiModal')" class="flex items-center justify-between p-2 transition-colors border cursor-pointer border-base-200 rounded-xl bg-base-100 hover:border-primary group">
                            <span class="font-mono text-xs font-bold truncate transition-colors text-base-content group-hover:text-primary">
                                {{ pesanan.nomor_resi || 'Belum Diinput' }}
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 transition-colors opacity-30 group-hover:text-primary shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M2.695 14.763l-1.262 3.155a.5.5 0 00.65.65l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z" /></svg>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="flex items-center justify-center p-4 mt-2 border border-dashed rounded-xl border-base-300 bg-base-200/30 grow">
                        <p class="text-xs font-black tracking-widest uppercase opacity-40">Ambil di Toko</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- KELOMPOK 3: STATUS -->
        <div class="flex flex-col flex-1 p-5">
            <div class="mb-3">
                <span class="text-[10px] font-black uppercase tracking-widest opacity-40">Status</span>
            </div>
            <div class="flex flex-col gap-4 grow">
                <div>
                    <span class="text-[9px] font-bold uppercase opacity-40 block mb-1.5 tracking-widest">Operasional</span>
                    <div class="inline-flex px-3 py-1.5 text-[10px] font-black uppercase tracking-widest border rounded-xl shadow-sm"
                         :class="statusOperasionalClass(pesanan.status_operasional)">
                        {{ formatEnum(pesanan.status_operasional) }}
                    </div>
                </div>
                <div class="pt-4 border-t border-dashed border-base-200">
                    <span class="text-[9px] font-bold uppercase opacity-40 block mb-1.5 tracking-widest">Pembayaran</span>
                    <select
                        class="w-full select select-bordered select-sm text-[10px] font-black uppercase tracking-wider outline-none focus:outline-none"
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

        <!-- KELOMPOK 4: WAKTU & TIMELINE -->
        <div class="flex flex-col flex-1 p-5">
            <div class="mb-3">
                <span class="text-[10px] font-black uppercase tracking-widest opacity-40">Waktu & Timeline</span>
            </div>
            <div class="flex flex-col gap-4 grow">
                <div>
                    <span class="text-[9px] font-bold uppercase opacity-40 block mb-0.5 tracking-widest">Dibuat Pada</span>
                    <span class="font-mono text-xs font-bold text-base-content">{{ formatTanggal(pesanan.tanggal_pesan) }}</span>
                </div>
                <div class="pt-4 mt-auto border-t border-dashed border-base-200">
                    <template v-if="pesanan.status_operasional === 'selesai'">
                        <span class="block mb-1 text-[9px] font-bold tracking-widest opacity-50 uppercase">Selesai / Diterima</span>
                        <div class="font-mono text-xs font-black text-success">{{ formatTanggal(pesanan.tanggal_selesai) }}</div>
                    </template>
                    <template v-else-if="pesanan.waktu_deadline">
                        <span class="block mb-1 text-[9px] font-bold tracking-widest opacity-50 uppercase">Deadline Cetak</span>
                        <div class="font-mono text-xs font-black text-warning">{{ formatTanggal(pesanan.waktu_deadline) }}</div>
                    </template>
                    <template v-else>
                        <span class="block mb-1 text-[9px] font-bold tracking-widest opacity-50 uppercase">Deadline Cetak</span>
                        <span class="text-[10px] font-bold italic opacity-40">Menunggu Pembayaran</span>
                    </template>
                </div>
            </div>
        </div>
    </div>


    <!-- MODAL GANTI ALAMAT & PENGIRIMAN -->
    <dialog ref="modalAlamat" class="modal modal-bottom sm:modal-middle">
        <div class="p-0 overflow-hidden border shadow-2xl modal-box bg-base-100 rounded-3xl border-base-200 w-11/12 max-w-4xl flex flex-col max-h-[90vh]">

            <!-- HEADER MODAL (STICKY) -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-200 bg-base-100 shrink-0">
                <div>
                    <h3 class="text-lg font-black tracking-tight text-base-content">Ubah Alamat & Pengiriman</h3>
                    <p class="text-xs font-medium opacity-60 mt-0.5">Pilih tujuan dan kalkulasi ulang layanan pengiriman.</p>
                </div>
                <button type="button" @click="closeModelAlamat()" class="btn btn-sm btn-circle btn-ghost opacity-50 hover:opacity-100">✕</button>
            </div>

            <!-- BODY MODAL (SCROLLABLE AREA) -->
            <div class="flex-1 overflow-hidden bg-base-100">
                <div class="flex flex-col h-full lg:flex-row divide-y lg:divide-y-0 lg:divide-x divide-base-200">

                    <!-- KIRI: DAFTAR ALAMAT -->
                    <div class="flex flex-col flex-1 h-full p-6 lg:w-3/5">
                        <div class="flex items-center gap-2 mb-4 shrink-0">
                            <div class="flex items-center justify-center w-6 h-6 text-xs font-black rounded-full bg-primary/10 text-primary">1</div>
                            <h4 class="text-xs font-bold tracking-widest uppercase text-base-content/70">Pilih Alamat Tujuan</h4>
                        </div>

                        <!-- Area Scroll Khusus Alamat -->
                        <div class="flex-1 pr-2 space-y-3 overflow-y-auto custom-scrollbar">
                            <label v-for="al in daftarAlamat" :key="al.id_alamat"
                                   class="flex items-start gap-4 p-4 transition-all border cursor-pointer rounded-2xl group"
                                   :class="formAlamat.id_alamat === al.id_alamat ? 'border-primary bg-primary/5 shadow-sm ring-1 ring-primary/20' : 'border-base-200 hover:border-base-300 hover:bg-base-200/30'">

                                <input type="radio" :value="al.id_alamat" v-model="formAlamat.id_alamat" class="mt-1 radio radio-primary radio-sm shrink-0" />

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-xs font-black tracking-wider uppercase truncate text-base-content">
                                            {{ al.label || 'Alamat' }}
                                        </p>
                                        <span v-if="al.is_default" class="text-[9px] bg-primary/20 text-primary px-2 py-0.5 rounded-full uppercase font-bold tracking-widest shrink-0">Utama</span>
                                    </div>
                                    <p class="text-[11px] font-bold text-base-content/80 mb-1 truncate">{{ al.nama_penerima }} <span class="font-normal opacity-50">|</span> {{ al.no_hp }}</p>
                                    <p class="text-[10px] leading-relaxed text-base-content/60 line-clamp-2" :title="al.alamat_lengkap">
                                        {{ al.alamat_lengkap }}, {{ al.kecamatan }}, {{ al.kota }}, {{ al.provinsi }} {{ al.kode_pos }}
                                    </p>
                                </div>
                            </label>

                            <!-- Empty State -->
                            <div v-if="daftarAlamat.length === 0" class="flex flex-col items-center justify-center h-full gap-2 py-10 opacity-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span class="text-xs font-bold tracking-wider uppercase">Belum ada alamat terdaftar</span>
                            </div>
                        </div>
                    </div>

                    <!-- KANAN: EKSPEDISI & ONGKIR -->
                    <div class="flex flex-col flex-1 h-full p-6 lg:w-2/5 bg-base-200/30">
                        <div class="flex items-center gap-2 mb-4 shrink-0">
                            <div class="flex items-center justify-center w-6 h-6 text-xs font-black rounded-full bg-primary/10 text-primary">2</div>
                            <h4 class="text-xs font-bold tracking-widest uppercase text-base-content/70">Opsi Pengiriman</h4>
                        </div>

                        <div class="flex flex-col flex-1 space-y-4">
                            <CustomSelect v-model="formAlamat.ekspedisi_nama" label="Kurir / Ekspedisi" :options="ekspedisiOptions" valueKey="id" labelKey="nama" />

                            <div v-if="formAlamat.ekspedisi_nama !== 'Ambil di Toko'" class="space-y-4">
                                <template v-if="isManualEkspedisi">
                                    <CustomSelect v-model="formAlamat.ekspedisi_layanan" label="Layanan Lokal" :options="manualLayananOptions" valueKey="id" labelKey="nama" placeholder="Pilih Instan/Lokal..." />
                                </template>

                                <template v-else>
                                    <div v-if="isLoadingOngkir" class="flex flex-col gap-1.5">
                                        <label class="text-[10px] font-bold uppercase opacity-70 ml-1 block">Layanan Ongkir</label>
                                        <div class="flex items-center justify-center gap-2 h-11 border border-base-300 bg-base-100 rounded-xl text-[11px] font-bold text-primary animate-pulse">
                                            <span class="loading loading-spinner loading-xs"></span> Mengkalkulasi Tarif...
                                        </div>
                                    </div>
                                    <CustomSelect v-else v-model="formAlamat.ekspedisi_layanan" label="Layanan Ongkir" :options="layananOptions" valueKey="id" labelKey="nama" placeholder="Pilih Layanan Ekspedisi..." />
                                </template>

                                <!-- Form Biaya Ongkir Custom Styling -->
                                <div class="pt-2">
                                    <label class="text-[10px] font-bold uppercase opacity-70 ml-1 block mb-1.5">Total Biaya Ongkir</label>
                                    <div class="relative flex items-center">
                                        <span class="absolute text-xs font-black left-4 text-base-content/50">Rp</span>
                                        <input
                                            type="number"
                                            v-model="formAlamat.harga_ongkir"
                                            class="w-full h-12 pl-10 pr-4 text-sm font-black transition-all border outline-none bg-base-100 rounded-xl border-base-300 focus:border-primary focus:ring-1 focus:ring-primary/50"
                                            placeholder="0"
                                            :readonly="!isManualEkspedisi"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Layout Jika Ambil Di Toko -->
                            <div v-else class="flex flex-col items-center justify-center flex-1 p-6 border border-dashed rounded-2xl border-base-300 opacity-60 bg-base-100/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mb-3 text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-center leading-relaxed">Pesanan akan diambil<br>langsung di Toko</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER MODAL (ACTION BUTTONS) -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t shrink-0 border-base-200 bg-base-100">
                <button type="button" @click="closeModelAlamat()" class="px-6 font-bold tracking-wider uppercase btn btn-sm btn-ghost rounded-xl text-[10px]">Batal</button>
                <button @click="submitAlamat" class="btn btn-sm btn-primary rounded-xl font-black tracking-widest text-[10px] uppercase shadow-md shadow-primary/20 px-8 h-10" :disabled="formAlamat.processing || !formAlamat.id_alamat || daftarAlamat.length === 0 || isLoadingOngkir">
                    <span v-if="formAlamat.processing" class="loading loading-spinner loading-xs"></span>
                    Simpan Perubahan
                </button>
            </div>
        </div>

        <!-- Backdrop Blur -->
        <form method="dialog" class="backdrop-blur-sm modal-backdrop bg-base-300/60"><button @click="modalAlamat.value.close()">close</button></form>
    </dialog>

    <!-- MODAL PEMBAYARAN SEBAGIAN (DP) -->
    <dialog class="modal" :class="{ 'modal-open': showBayarSebagianModal }">
        <div class="border shadow-2xl modal-box bg-base-100 rounded-3xl border-base-200">
            <h3 class="text-lg font-black tracking-tight text-base-content">Pembayaran Sebagian (DP)</h3>
            <div class="mt-4 space-y-4">
                <div>
                    <CustomInput label="Total Tagihan" :model-value="formatRupiah(total_tagihan)" disabled />
                </div>
                <div>
                    <CustomInputNumber v-model="formPembayaran.nominal_bayar" label="Nominal Bayar Sekarang" type="number" placeholder="Masukkan nominal DP/Cicilan" :error="formPembayaran.errors.nominal_bayar" />
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
