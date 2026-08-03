<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { alertStore } from '@/Utils/alertStore';
import { Truck, Printer, Clock } from 'lucide-vue-next';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';

const props = defineProps({
    pesananList: Array,
    currentUser: Object,
});

const formatTanggal = (tgl) => {
    if (!tgl) return '-';
    const date = new Date(tgl);
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
};

const cleanProductName = (name) => name ? name.replace(/^[A-Za-z]+-\d+-/, '').replace(/-/g, ' ') : '';

// ==========================================
// LOGIC PENGANTARAN & KIRIM REGULER
// ==========================================
const isConfirmKirimOpen = ref(false);
const selectedKirimId = ref(null);
const formKirim = useForm({ nomor_resi: '' });

const closeKirimModal = () => { isConfirmKirimOpen.value = false; formKirim.reset(); };
const executeKirimPesanan = () => {
    formKirim.post(route('produksi.kirim', selectedKirimId.value), {
        onSuccess: () => { alertStore.show('Pesanan masuk ke Histori (Pengantaran)!', 'success'); closeKirimModal(); },
        onError: () => alertStore.show('Gagal mengubah status.', 'error')
    });
};

// ==========================================
// LOGIC BERAT CUSTOM (PRD-0001-SKU-001)
// ==========================================
const isModalBeratOpen = ref(false);
const selectedPengantaran = ref(null);
const formBerat = useForm({ items: [] });

const openModalBerat = (pesanan) => {
    selectedPengantaran.value = pesanan;
    formBerat.items = pesanan.pesanan_item.filter(i => i.id_sku === 'PRD-0001-SKU-001').map(i => ({
        id_pesanan_item: i.id,
        nama_produk: cleanProductName(i.nama_produk_snapshot),
        berat: Number(i.total_berat_snapshot) || 0
    }));
    isModalBeratOpen.value = true;
};
const closeModalBerat = () => { isModalBeratOpen.value = false; formBerat.reset(); };
const submitBerat = () => {
    formBerat.put(route('produksi.update_berat', selectedPengantaran.value.id_pesan), {
        onSuccess: () => { closeModalBerat(); alertStore.show('Berat disimpan!', 'success'); openPengantaranModal(selectedPengantaran.value); }
    });
};

// ==========================================
// LOGIC ONGKIR & EKSPEDISI
// ==========================================
const isPengantaranModalOpen = ref(false);
const layananOptions = ref([]);
const isLoadingOngkir = ref(false);
const formPengantaran = useForm({ id_alamat: '', ekspedisi_nama: '', ekspedisi_layanan: '', harga_ongkir: 0, ekspedisi_estimasi: '', nomor_resi: '' });

const ekspedisiOptions = [ { id: 'Ambil di Toko', nama: 'Ambil di Toko' }, { id: 'Kurir Toko', nama: 'Kurir Lokal / Instan' }, { id: 'jne', nama: 'JNE' }, { id: 'sicepat', nama: 'SiCepat' }, { id: 'jnt', nama: 'J&T' }, { id: 'pos', nama: 'POS' } ];
const manualLayananOptions = [ { id: 'Gojek / Grab', nama: 'Gojek / Grab' }, { id: 'Kurir Toko', nama: 'Kurir Toko' } ];
const isManualEkspedisi = computed(() => ['Ambil di Toko', 'Kurir Toko'].includes(formPengantaran.ekspedisi_nama));

const openPengantaranModal = (pesanan) => {
    formPengantaran.id_alamat = pesanan.id_alamat || '';
    let ekspNameRaw = (pesanan.ekspedisi_nama || '').toLowerCase();

    let matchedCode = 'Kurir Toko';
    if (ekspNameRaw.includes('ambil')) matchedCode = 'Ambil di Toko';
    else if (ekspNameRaw.includes('jne')) matchedCode = 'jne';
    else if (ekspNameRaw.includes('sicepat')) matchedCode = 'sicepat';
    else if (ekspNameRaw.includes('j&t') || ekspNameRaw.includes('jnt')) matchedCode = 'jnt';
    else if (ekspNameRaw.includes('pos')) matchedCode = 'pos';

    formPengantaran.ekspedisi_nama = matchedCode;
    formPengantaran.ekspedisi_layanan = pesanan.ekspedisi_layanan || '';
    formPengantaran.harga_ongkir = pesanan.harga_ongkir || 0;
    formPengantaran.nomor_resi = pesanan.nomor_resi || '';
    isPengantaranModalOpen.value = true;
};
const closePengantaranModal = () => { isPengantaranModalOpen.value = false; formPengantaran.reset(); };

const fetchOngkir = async () => {
    if (!formPengantaran.id_alamat || isManualEkspedisi.value) return;
    isLoadingOngkir.value = true; layananOptions.value = [];
    formPengantaran.ekspedisi_layanan = ''; formPengantaran.harga_ongkir = 0;

    try {
        const itemsPayload = selectedPengantaran.value.pesanan_item.map(item => ({
            id_sku: item.id_sku, jumlah: item.jumlah, finishings: item.pesanan_item_finishing, total_berat: item.total_berat_snapshot
        }));

        const response = await axios.post('/ongkir/calculate', {
            id_alamat: formPengantaran.id_alamat, courier: formPengantaran.ekspedisi_nama, items: itemsPayload
        });

        const data = response.data;
        let costs = [];

        if (data?.data && Array.isArray(data.data)) {
            costs = data.data.map(i => ({ id: i.service, nama: `${i.service} (${i.etd || '-'} Hari) - Rp ${Number(i.cost).toLocaleString('id-ID')}`, cost: i.cost, etd: i.etd || '' }));
        } else if (data?.rajaongkir?.results?.[0]?.costs) {
            costs = data.rajaongkir.results[0].costs.map(s => ({ id: s.service, nama: `${s.service} (${s.cost[0]?.etd || '-'} Hari) - Rp ${Number(s.cost[0]?.value).toLocaleString('id-ID')}`, cost: s.cost[0]?.value, etd: s.cost[0]?.etd || '' }));
        }

        if (costs.length > 0) { layananOptions.value = costs; }
        else { alertStore.show('Layanan kurir tidak tersedia.', 'error'); formPengantaran.ekspedisi_nama = 'Kurir Toko'; }
    } catch (error) {
        alertStore.show('Gagal menghubungi server ongkir.', 'error'); formPengantaran.ekspedisi_nama = 'Kurir Toko';
    } finally {
        isLoadingOngkir.value = false;
    }
};

watch(() => formPengantaran.ekspedisi_nama, async (newCourier, oldCourier) => {
    if (!oldCourier) return;
    if (newCourier === 'Ambil di Toko') {
        layananOptions.value = []; formPengantaran.ekspedisi_layanan = 'Ambil Sendiri'; formPengantaran.harga_ongkir = 0;
    } else if (newCourier === 'Kurir Toko') {
        layananOptions.value = []; formPengantaran.ekspedisi_layanan = ''; formPengantaran.harga_ongkir = 0;
    } else {
        if (!formPengantaran.id_alamat) return;
        await fetchOngkir();
    }
});

watch(() => formPengantaran.ekspedisi_layanan, (newLayanan) => {
    if (!isManualEkspedisi.value && newLayanan) {
        const selected = layananOptions.value.find(l => l.id === newLayanan);
        if (selected) { formPengantaran.harga_ongkir = selected.cost; formPengantaran.ekspedisi_estimasi = selected.etd; }
    }
});

const submitPengantaran = () => {
    if (formPengantaran.ekspedisi_nama !== 'Ambil di Toko' && !formPengantaran.ekspedisi_layanan) {
        return alertStore.show('Pilih Layanan Pengiriman terlebih dahulu!', 'error');
    }
    const namaEkspedisiAsli = ekspedisiOptions.find(e => e.id === formPengantaran.ekspedisi_nama)?.nama || formPengantaran.ekspedisi_nama;
    const finalEkspedisiNama = isManualEkspedisi.value ? formPengantaran.ekspedisi_nama : namaEkspedisiAsli.toUpperCase();

    formPengantaran.transform((data) => ({
        ...data, ekspedisi_nama: finalEkspedisiNama,
        ekspedisi_layanan: data.ekspedisi_nama === 'Ambil di Toko' ? 'Ambil Sendiri' : data.ekspedisi_layanan,
        harga_ongkir: data.ekspedisi_nama === 'Ambil di Toko' ? 0 : data.harga_ongkir,
        ekspedisi_estimasi: data.ekspedisi_nama === 'Ambil di Toko' ? '0' : data.ekspedisi_estimasi,
    })).post(route('produksi.pengantaran.proses', selectedPengantaran.value.id_pesan), {
        onSuccess: () => { closePengantaranModal(); alertStore.show('Pesanan masuk ke Histori!', 'success'); }
    });
};

const handleProsesPengantaran = (pesanan) => {
    selectedPengantaran.value = pesanan;
    pesanan.pesanan_item.some(item => item.id_sku === 'PRD-0001-SKU-001') ? openModalBerat(pesanan) : (selectedKirimId.value = pesanan.id_pesan, formKirim.nomor_resi = pesanan.nomor_resi || '', isConfirmKirimOpen.value = true);
};
</script>

<template>
    <div class="space-y-6">

        <!-- DESAIN EMPTY STATE KONSISTEN -->
        <div v-if="pesananList.length === 0" class="flex flex-col items-center justify-center py-20 mt-4 duration-500 border bg-base-200/20 border-base-300 rounded-3xl animate-in fade-in zoom-in-95">
            <Truck class="w-12 h-12 mb-3 opacity-30 text-base-content" stroke-width="1.5" />
            <h3 class="text-sm font-bold opacity-80 text-base-content">Belum Ada Pesanan Siap Kirim</h3>
            <p class="mt-1 text-xs text-center opacity-50 text-base-content">Pesanan yang semua itemnya selesai akan otomatis muncul di sini.</p>
        </div>

        <div v-for="pesanan in pesananList" :key="pesanan.id_pesan" class="overflow-hidden border shadow-sm rounded-xl border-base-200 bg-base-100 animate-in fade-in slide-in-from-bottom-2">

            <!-- HEADER PESANAN RESPONSIVE -->
            <div class="flex flex-col items-start justify-between gap-4 p-4 border-b sm:p-5 sm:flex-row sm:items-center border-base-200 bg-base-50/30">
                <div class="flex items-start w-full gap-3 sm:items-center sm:w-auto">
                    <!-- shrink-0 agar kotak ID tidak gepeng -->
                    <div v-if="currentUser?.role !== 'vendor'" class="shrink-0 px-3 py-1.5 border rounded-lg border-base-300 bg-base-100 flex flex-col items-center justify-center">
                        <span class="text-[9px] sm:text-[10px] font-black text-base-content/50 uppercase tracking-widest">ID Pesan</span>
                        <span class="text-xs font-black sm:text-sm text-base-content">{{ pesanan.id_pesan }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 v-if="currentUser?.role !== 'vendor'" class="text-sm font-bold truncate sm:text-base text-base-content">{{ pesanan.customer?.user?.name }}</h3>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <span class="flex items-center gap-1.5 text-[10px] sm:text-xs font-bold px-2.5 py-1 rounded-full border border-green-200 text-green-600 bg-green-50">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-600 animate-pulse"></span> Produksi Selesai
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Bagian Kanan Header (Deadline & Tombol) -->
                <div class="flex flex-col w-full gap-3 pt-3 border-t sm:border-t-0 sm:pt-0 border-base-200 sm:w-auto sm:items-end shrink-0">
                    <div class="flex items-center gap-2 text-xs sm:text-sm">
                        <Clock class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-base-content/40" />
                        <span class="font-medium text-base-content/60">Tgl Pesan:</span>
                        <span class="font-black tracking-tight text-base-content">{{ formatTanggal(pesanan.tanggal_pesan) }}</span>
                    </div>
                    <!-- Tombol flex-1 / full width di HP -->
                    <div class="flex items-center w-full gap-2 mt-1 sm:w-auto" v-if="currentUser?.role !== 'vendor'">
                        <a :href="route('pesan.cetakLabel', pesanan.id_pesan)" target="_blank" class="w-full font-bold tracking-wider uppercase sm:w-auto btn btn-xs sm:btn-sm btn-outline hover:bg-base-200 hover:text-base-content hover:border-base-300 border-base-300 text-base-content/70 text-[9px] sm:text-[10px]">
                            <Printer class="w-3.5 h-3.5" /> Cetak Label
                        </a>
                    </div>
                </div>
            </div>

            <!-- BODY KONTEN PENGANTARAN RESPONSIVE -->
            <div class="flex flex-col gap-6 p-4 sm:p-5 md:flex-row">
                <!-- Info Produk Ringkas -->
                <div class="flex-1 space-y-3">
                    <h4 class="text-[9px] sm:text-[10px] font-black text-base-content/50 uppercase tracking-widest block mb-1.5">Produk Siap Kirim</h4>
                    <div class="space-y-2">
                        <div v-for="item in pesanan.pesanan_item" :key="item.id" class="flex items-center justify-between p-3 border shadow-sm bg-base-50/50 rounded-xl border-base-200">
                            <span class="text-xs font-bold capitalize sm:text-sm text-base-content">{{ cleanProductName(item.nama_produk_snapshot) }}</span>
                            <span class="text-[10px] sm:text-xs font-black bg-base-100 px-2.5 py-1 rounded-md border border-base-300">Qty: {{ item.jumlah }}</span>
                        </div>
                    </div>
                </div>

                <!-- Info Ekspedisi Awal -->
                <div class="w-full pt-4 space-y-3 border-t md:w-1/3 md:border-t-0 md:border-l-2 border-base-200 md:pt-0 md:pl-6 shrink-0">
                    <h4 class="text-[9px] sm:text-[10px] font-black text-base-content/50 uppercase tracking-widest block mb-1.5">Informasi Ekspedisi</h4>
                    <div class="p-4 border border-orange-100 bg-orange-50 rounded-xl">
                        <p class="text-[9px] sm:text-[10px] font-black text-orange-600/70 uppercase tracking-widest mb-1">Pilihan Customer</p>
                        <p class="text-sm font-black text-orange-900">{{ pesanan.ekspedisi_nama || 'Kurir Toko' }} <span v-if="pesanan.ekspedisi_layanan">- {{ pesanan.ekspedisi_layanan }}</span></p>
                    </div>
                </div>
            </div>

            <!-- TOMBOL PROSES PENGANTARAN (Dipindah & Responsif) -->
            <div class="flex flex-col gap-4 p-4 border-t sm:p-5 sm:flex-row sm:items-center sm:justify-between border-base-200 bg-base-50/50" v-if="currentUser?.role !== 'vendor'">
                <p class="text-[10px] sm:text-xs font-medium text-base-content/60 leading-relaxed text-center sm:text-left">Klik tombol untuk memasukkan nomor resi atau ongkir aktual sebelum memindahkan pesanan ke histori.</p>
                <button v-if="$can('produksi', 'ubah')" @click="handleProsesPengantaran(pesanan)" class="w-full font-bold tracking-wider uppercase sm:w-auto btn btn-sm btn-neutral rounded-xl text-[10px] sm:text-xs shrink-0">
                    <Truck class="w-4 h-4 sm:mr-1.5" />
                    <span class="sm:hidden">Proses Kirim</span>
                    <span class="hidden sm:inline">Input Resi / Proses Pengantaran</span>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL TENTUKAN BERAT CUSTOM (KONSISTEN) -->
        <!-- ============================================== -->
        <dialog class="modal" :class="{'modal-open': isModalBeratOpen}">
            <div class="flex flex-col max-w-lg p-0 overflow-hidden modal-box rounded-2xl">
                <!-- Header Modal -->
                <div class="flex items-start justify-between p-4 border-b sm:items-center sm:p-5 border-base-200">
                    <div>
                        <h3 class="text-base font-bold text-base-content">Tentukan Berat Item Custom</h3>
                        <p class="text-[11px] sm:text-sm font-medium text-base-content/50 mt-0.5">Berat harus disimpan sebelum cek ongkir.</p>
                    </div>
                    <button @click="closeModalBerat" class="btn btn-sm btn-circle btn-ghost text-base-content/40 hover:text-error">✕</button>
                </div>

                <!-- Body Modal -->
                <div class="p-4 sm:p-5 max-h-[70vh] overflow-y-auto space-y-4 bg-base-50/50 [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:bg-base-300 [&::-webkit-scrollbar-thumb]:rounded-full">
                    <div v-if="selectedPengantaran?.ekspedisi_nama" class="flex items-start gap-3 p-4 mb-2 border border-blue-100 shadow-sm rounded-xl bg-blue-50">
                        <Truck class="w-5 h-5 mt-0.5 shrink-0 text-blue-500" />
                        <div>
                            <p class="text-[10px] font-bold text-blue-500 uppercase tracking-widest mb-1">Ekspedisi Pilihan Customer</p>
                            <p class="text-sm font-black text-blue-900">
                                {{ selectedPengantaran.ekspedisi_nama }}
                                <span v-if="selectedPengantaran.ekspedisi_layanan">- {{ selectedPengantaran.ekspedisi_layanan }}</span>
                            </p>
                            <p v-if="selectedPengantaran.harga_ongkir" class="mt-1 text-xs font-bold text-blue-700">
                                Tarif Ongkir Awal: Rp {{ Number(selectedPengantaran.harga_ongkir).toLocaleString('id-ID') }}
                            </p>
                        </div>
                    </div>

                    <form @submit.prevent="submitBerat" class="space-y-4">
                        <div v-for="(item, index) in formBerat.items" :key="index" class="p-4 border shadow-sm rounded-xl bg-base-100 border-base-200">
                            <div class="mb-3">
                                <h4 class="text-sm font-black capitalize">{{ item.nama_produk }}</h4>
                            </div>
                            <div class="w-full form-control">
                                <label class="block mb-1.5 text-[11px] font-black uppercase tracking-widest text-base-content/50">Total Berat (Gram)</label>
                                <div class="relative flex items-center">
                                    <input type="number" v-model="formBerat.items[index].berat" required min="1" class="w-full pr-10 font-bold input input-bordered rounded-xl" placeholder="Contoh: 150000" />
                                    <span class="absolute text-xs font-black pointer-events-none right-4 text-base-content/40">g</span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer Modal -->
                <div class="flex flex-col-reverse gap-3 p-4 border-t sm:p-5 sm:flex-row sm:justify-end border-base-200 bg-base-50/50 rounded-b-2xl">
                    <button type="button" @click="closeModalBerat" class="w-full font-bold tracking-wider uppercase sm:w-auto btn btn-sm btn-ghost rounded-xl text-[10px] sm:text-xs">Batal</button>
                    <button type="button" @click="submitBerat" :disabled="formBerat.processing" class="w-full px-8 font-bold tracking-wider uppercase sm:w-auto btn btn-sm btn-primary rounded-xl text-[10px] sm:text-xs">
                        <span v-if="formBerat.processing" class="loading loading-spinner loading-xs"></span>
                        Simpan Berat & Lanjut
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop bg-base-content/20"><button @click="closeModalBerat">close</button></form>
        </dialog>

        <!-- ============================================== -->
        <!-- MODAL PENGANTARAN & CEK ONGKIR (KONSISTEN) -->
        <!-- ============================================== -->
        <dialog class="modal" :class="{'modal-open': isPengantaranModalOpen}">
            <div class="flex flex-col max-w-xl p-0 overflow-hidden modal-box rounded-2xl">
                <!-- Header Modal -->
                <div class="flex items-start justify-between p-4 border-b sm:items-center sm:p-5 border-base-200">
                    <div>
                        <h3 class="text-base font-bold text-base-content">Proses Pengantaran</h3>
                        <p class="text-[11px] sm:text-sm font-medium text-base-content/50 mt-0.5">Pilih ekspedisi dan hitung ongkir pesanan <span class="font-bold">{{ selectedPengantaran?.id_pesan }}</span></p>
                    </div>
                    <button @click="closePengantaranModal" class="btn btn-sm btn-circle btn-ghost text-base-content/40 hover:text-error">✕</button>
                </div>

                <!-- Body Modal -->
                <div class="p-4 sm:p-5 max-h-[70vh] overflow-y-auto space-y-6 [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:bg-base-300 [&::-webkit-scrollbar-thumb]:rounded-full">
                    <div class="grid grid-cols-1 gap-4 p-4 border sm:grid-cols-2 bg-base-200/30 rounded-xl border-base-200">

                        <div class="col-span-1 sm:col-span-2">
                            <CustomSelect v-model="formPengantaran.ekspedisi_nama" label="Kurir / Ekspedisi" :options="ekspedisiOptions" valueKey="id" labelKey="nama" class="[&_select]:rounded-xl" />
                        </div>

                        <div v-if="formPengantaran.ekspedisi_nama !== 'Ambil di Toko'" class="col-span-1 space-y-4 sm:col-span-2">
                            <template v-if="isManualEkspedisi">
                                <CustomSelect v-model="formPengantaran.ekspedisi_layanan" label="Layanan Lokal" :options="manualLayananOptions" valueKey="id" labelKey="nama" placeholder="Pilih Instan/Lokal..." class="[&_select]:rounded-xl" />
                            </template>

                            <template v-else>
                                <div v-if="isLoadingOngkir" class="flex items-center justify-center gap-2 h-11 border border-base-300 bg-base-100 rounded-xl text-[11px] font-bold text-primary animate-pulse">
                                    <span class="loading loading-spinner loading-xs"></span> Mengkalkulasi Tarif dari Database...
                                </div>
                                <div v-else class="flex flex-col gap-2">
                                    <CustomSelect v-model="formPengantaran.ekspedisi_layanan" label="Layanan Ongkir" :options="layananOptions" valueKey="id" labelKey="nama" placeholder="Pilih Layanan Ekspedisi..." class="[&_select]:rounded-xl" />
                                    <button type="button" @click="fetchOngkir" class="text-[10px] text-blue-500 font-bold hover:underline self-end">
                                        ↻ Hitung Ulang Tarif
                                    </button>
                                </div>
                            </template>

                            <div>
                                <label class="block mb-1.5 text-[11px] font-black uppercase tracking-widest text-base-content/50">Total Biaya Ongkir</label>
                                <div class="relative flex items-center">
                                    <span class="absolute text-xs font-black left-4 text-base-content/50">Rp</span>
                                    <input
                                        type="number"
                                        v-model="formPengantaran.harga_ongkir"
                                        class="w-full pl-10 pr-4 text-sm font-black transition-all border outline-none h-11 bg-base-100 rounded-xl border-base-300 focus:border-primary"
                                        placeholder="0"
                                        :readonly="!isManualEkspedisi"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Info Pick up Toko -->
                        <div v-else class="flex flex-col items-center justify-center col-span-1 p-4 border border-dashed sm:col-span-2 rounded-xl border-base-300 opacity-60 bg-base-100">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-center leading-relaxed">Pesanan akan diambil<br>langsung di Toko</span>
                        </div>

                        <!-- Resi Input -->
                        <div class="col-span-1 pt-4 border-t sm:col-span-2 border-base-200">
                            <CustomInput
                                v-model="formPengantaran.nomor_resi"
                                label="Nomor Resi / Kurir (Opsional)"
                                placeholder="Contoh: JX1234567890 / Budi Gojek"
                                class="[&_input]:rounded-xl"
                            />
                        </div>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="flex flex-col-reverse gap-3 p-4 border-t sm:p-5 sm:flex-row sm:justify-end border-base-200 bg-base-50/50 rounded-b-2xl">
                    <button type="button" @click="closePengantaranModal" class="w-full font-bold tracking-wider uppercase sm:w-auto btn btn-sm btn-ghost rounded-xl text-[10px] sm:text-xs">Batal</button>
                    <button type="button" @click="submitPengantaran" :disabled="formPengantaran.processing || isLoadingOngkir" class="w-full px-8 font-bold tracking-wider uppercase sm:w-auto btn btn-sm btn-neutral rounded-xl text-[10px] sm:text-xs">
                        <span v-if="formPengantaran.processing" class="loading loading-spinner loading-xs"></span>
                        Simpan & Kirim
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop bg-base-content/20"><button @click="closePengantaranModal">close</button></form>
        </dialog>

        <!-- ============================================== -->
        <!-- MODAL KIRIM REGULER (KONSISTEN) -->
        <!-- ============================================== -->
        <dialog class="modal" :class="{'modal-open': isConfirmKirimOpen}">
            <div class="max-w-sm p-0 modal-box rounded-2xl">
                <!-- Header Modal -->
                <div class="flex items-start justify-between p-4 border-b sm:items-center sm:p-5 border-base-200">
                    <div>
                        <h3 class="text-base font-bold text-base-content">Proses Pengantaran</h3>
                    </div>
                    <button @click="closeKirimModal" class="btn btn-sm btn-circle btn-ghost text-base-content/40 hover:text-error">✕</button>
                </div>

                <!-- Body Modal -->
                <div class="p-4 sm:p-5">
                    <p class="mb-5 text-[11px] sm:text-sm font-medium leading-relaxed text-base-content/60">Pastikan semua item telah dikemas. Silakan masukkan nomor resi atau nama kurir jika ada.</p>

                    <form @submit.prevent="executeKirimPesanan" class="space-y-4">
                        <CustomInput
                            v-model="formKirim.nomor_resi"
                            label="Nomor Resi / Kurir (Opsional)"
                            placeholder="Contoh: JX1234567890 / Budi Gojek"
                            class="[&_input]:rounded-xl"
                        />

                        <!-- Footer Modal -->
                        <div class="flex flex-col-reverse gap-3 pt-5 mt-6 border-t sm:flex-row sm:justify-end border-base-200">
                            <button type="button" @click="closeKirimModal" class="w-full font-bold tracking-wider uppercase sm:w-auto btn btn-sm btn-ghost rounded-xl text-[10px] sm:text-xs">Batal</button>
                            <button type="submit" :disabled="formKirim.processing" class="w-full px-6 font-bold tracking-wider uppercase sm:w-auto btn btn-sm btn-primary rounded-xl text-[10px] sm:text-xs">
                                <span v-if="formKirim.processing" class="loading loading-spinner loading-xs"></span>
                                Kirim Pesanan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop bg-base-content/20"><button @click="closeKirimModal">close</button></form>
        </dialog>

    </div>
</template>
