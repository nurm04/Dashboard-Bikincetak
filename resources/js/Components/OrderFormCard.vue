<script setup>
import { ref, watch, computed, onMounted, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import CustomInput from '@/Components/CustomInput.vue';
import CustomTextarea from '@/Components/CustomTextarea.vue';
import CustomSelectSearch from '@/Components/CustomSelectSearch.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomInputFile from '@/Components/CustomInputFile.vue';
import { alertStore } from '@/Utils/alertStore';

const props = defineProps({
    editData: Object,
    isPosMode: { type: Boolean, default: false }
});
const emit = defineEmits(['cancel', 'submit']);

const listProduks = ref([]);
const detailProduk = ref(null);
const isFetching = ref(false);
const activeCustomer = ref(null);

const form = ref({
    id_produk: '',
    id_sku: '',
    estimasi_pengerjaan: '',
    finishings: {},
    desainPayload: { tipe_file: 'upload', file: null, link_file: '' },
    jumlah: 1,
    catatan: ''
});

onMounted(() => {
    activeCustomer.value = JSON.parse(localStorage.getItem('pos_active_customer'));
    fetchAllProduks();
});

const fetchAllProduks = async () => {
    try { listProduks.value = (await axios.get('/api/items')).data.data; }
    catch (e) { alertStore.show('Gagal ambil data produk', 'error'); }
};

const fetchDetailProduk = async (id) => {
    if (!id) return;

    try {
        const { data } = await axios.get(`/api/item/${id}`);
        detailProduk.value = data.data;

        if (!props.editData) {
            const firstSku = detailProduk.value?.skus?.[0];
            if (firstSku) {
                form.value.id_sku = firstSku.id_sku;
            }
        }

        if (props.editData && !form.value.id_sku) {
            form.value.id_sku = props.editData.id_sku;
            form.value.jumlah = props.editData.jumlah;
            form.value.catatan = props.editData.catatan;
        }
    } catch (e) {
        alertStore.show('Gagal ambil detail', 'error');
    } finally {
        isFetching.value = false;
    }
};

watch(() => props.editData, async (newVal) => {
    if (!newVal) return;

    form.value = {
        id_produk: '', id_sku: '', estimasi_pengerjaan: 'Reguler', finishings: {},
        desainPayload: { tipe_file: 'upload', file: null, link_file: '' }, jumlah: 1, catatan: ''
    };

    form.value.id_produk = newVal.id_sku.split('-SKU-')[0];
    await fetchDetailProduk(form.value.id_produk);
    await nextTick();

    form.value.id_sku = newVal.id_sku;
    form.value.jumlah = newVal.jumlah;
    form.value.catatan = newVal.catatan;
    form.value.estimasi_pengerjaan = newVal.estimasi_pengerjaan_snapshot;

    if (newVal.pesanan_item_finishing?.length) {
        const skuMaster = detailProduk.value?.skus?.find(s => s.id_sku === newVal.id_sku);
        if(skuMaster){
            newVal.pesanan_item_finishing.forEach(fin => {
                const master = skuMaster.opsi_finishing?.find(f => String(f.id_sku_finishing) === String(fin.id_sku_finishing));
                if (master) {
                    form.value.finishings[master.kategori_finishing] = master.id_sku_finishing;
                }
            });
        }
    }

    form.value.desainPayload = {
        tipe_file: newVal.file_desain?.tipe ?? 'upload',
        file: newVal.file_desain?.nilai ?? null,
        link_file: newVal.file_desain?.tipe === 'link' ? newVal.file_desain?.nilai : ''
    };
}, { immediate: true });

const skuOptions = computed(() => (detailProduk.value?.skus || []).map(s => ({ value: s.id_sku, label: s.nama_sku })));
const selectedSku = computed(() => (detailProduk.value?.skus || []).find(s => s.id_sku === form.value.id_sku) || null);

const finishingPayload = computed(() => {
    const data = [];
    Object.values(form.value.finishings).forEach(idSkuFin => {
        if (!idSkuFin) return;

        const fin = selectedSku.value?.opsi_finishing?.find(f => String(f.id_sku_finishing) === String(idSkuFin));
        if (!fin) return;

        data.push({
            id_sku_finishing: fin.id_sku_finishing,
            nama_finishing_snapshot: `${fin.kategori_finishing}: ${fin.nama_pilihan}`,
            harga_finishing_snapshot: fin.harga_tambahan
        });
    });
    return data;
});

// ========================================================
// 1. HARGA DASAR & DISKON (MENDUKUNG NOMINAL/PERSEN)
// ========================================================
const hargaDasarAwal = computed(() => Number(selectedSku.value?.harga_dasar) || 0);

// Hitung Harga Bertingkat (Grosir) sbg Potongan per pcs
const diskonGrosir = computed(() => {
    if (!selectedSku.value) return 0;
    const tier = [...(selectedSku.value.harga_bertingkat || [])]
        .sort((a, b) => b.min - a.min)
        .find(t => form.value.jumlah >= t.min && (t.max === 0 || t.max === null || form.value.jumlah <= t.max));

    if (!tier) return 0;
    return tier.tipe === 'persen' ? hargaDasarAwal.value * (Number(tier.nilai) / 100) : Number(tier.nilai);
});

// Hitung Diskon Customer Role per pcs
const diskonMember = computed(() => {
    const roleId = activeCustomer.value?.id_role_customer;
    if (!roleId || !selectedSku.value?.diskon_customer) return 0;
    const d = selectedSku.value.diskon_customer.find(d => String(d.id_role_customer) === String(roleId));

    if (!d) return 0;
    return d.tipe === 'persen' ? hargaDasarAwal.value * (Number(d.nilai) / 100) : Number(d.nilai);
});

// Total Potongan per Satuan Pcs
const totalDiskonSatuan = computed(() => diskonGrosir.value + diskonMember.value);

// Harga Satuan Net (Setelah dipotong)
const hargaSatuanSnapshot = computed(() => Math.max(0, hargaDasarAwal.value - totalDiskonSatuan.value));

// ========================================================
// 2. SLA / HARGA PENGERJAAN (MENDUKUNG NOMINAL/PERSEN)
// ========================================================
const pengerjaanOptions = computed(() => {
    const list = (selectedSku.value?.harga_pengerjaan || []).map(p => {
        // Label dinamis: Nampilin % atau Rp di dropdown Kasir
        const labelBiaya = p.tipe === 'persen' ? `${p.nilai}%` : `Rp ${Number(p.nilai).toLocaleString('id-ID')}`;
        return { value: p.pengerjaan, label: `${p.pengerjaan} (+ ${labelBiaya})` };
    });
    return [...list];
});

const totalFinishing = computed(() => {
    return Object.values(form.value.finishings).reduce((acc, idSkuFin) => {
        const fin = selectedSku.value?.opsi_finishing?.find(f => String(f.id_sku_finishing) === String(idSkuFin));
        return acc + (fin ? fin.harga_tambahan : 0);
    }, 0);
});

const totalProduk = computed(() => (hargaSatuanSnapshot.value + totalFinishing.value) * form.value.jumlah);

// Kalkulasi nominal total SLA untuk item ini
const totalSla = computed(() => {
    const p = selectedSku.value?.harga_pengerjaan?.find(o => o.pengerjaan === form.value.estimasi_pengerjaan);
    if (!p) return 0;

    if (p.tipe === 'persen') {
        return totalProduk.value * (Number(p.nilai) / 100);
    } else {
        return Number(p.nilai); // Nominal langsung sbg Add-on biaya
    }
});

const subtotalItem = computed(() => totalProduk.value + totalSla.value);


// ========================================================
// 3. LAIN-LAIN
// ========================================================
const finishingGroups = computed(() => {
    const groups = {};
    selectedSku.value?.opsi_finishing?.forEach(fin => {
        if (!groups[fin.kategori_finishing]) groups[fin.kategori_finishing] = { options: [{ value: '', label: `Tanpa ${fin.kategori_finishing}` }] };

        groups[fin.kategori_finishing].options.push({
            value: fin.id_sku_finishing,
            label: `${fin.nama_pilihan} (+ Rp ${fin.harga_tambahan.toLocaleString('id-ID')})`
        });
    });
    return groups;
});

const currentMinimumOrder = computed(() => {
    let minOrder = selectedSku.value?.minimum_pesan || 1;
    Object.values(form.value.finishings).forEach(idSkuFin => {
        if (!idSkuFin) return;
        const fin = selectedSku.value?.opsi_finishing?.find(f => String(f.id_sku_finishing) === String(idSkuFin));
        if (fin && fin.minimum_pesan > minOrder) minOrder = fin.minimum_pesan;
    });
    return minOrder;
});

watch(currentMinimumOrder, (newMin) => {
    if (form.value.jumlah < newMin) {
        form.value.jumlah = newMin;
        alertStore.show(`Jumlah disesuaikan ke batas minimum order: ${newMin} pcs`, 'info');
    }
});

const isEditMode = computed(() => !!props.editData);

watch(() => form.value.id_sku, (newVal, oldVal) => {
    if (!selectedSku.value) return;

    if (isEditMode.value && !oldVal) return;

    form.value.finishings = {};
    form.value.jumlah = selectedSku.value.minimum_pesan || 1;

    form.value.estimasi_pengerjaan =
        selectedSku.value.harga_pengerjaan?.[0]?.pengerjaan || '';
});

watch(() => form.value.id_produk, (val) => fetchDetailProduk(val));

const handleFormSubmit = () => {
    if (!form.value.id_sku) { alertStore.show('Pilih varian produk!', 'error'); return; }

    const rincianDiskon = [];

    // Simpan history Grosir
    if (diskonGrosir.value > 0) {
        const tier = [...(selectedSku.value.harga_bertingkat || [])]
            .sort((a, b) => b.min - a.min)
            .find(t => form.value.jumlah >= t.min && (t.max === 0 || t.max === null || form.value.jumlah <= t.max));

        rincianDiskon.push({
            nama: tier?.tipe === 'persen' ? `Harga Grosir Qty ${form.value.jumlah} (${tier.nilai}%)` : `Harga Grosir Qty ${form.value.jumlah}`,
            nominal: diskonGrosir.value
        });
    }

    // Simpan history Diskon Member
    if (diskonMember.value > 0) {
        const roleName = activeCustomer.value?.role_customer?.nama_role || 'Member';
        const d = selectedSku.value.diskon_customer.find(d => String(d.id_role_customer) === String(activeCustomer.value?.id_role_customer));
        rincianDiskon.push({
            nama: d.tipe === 'persen' ? `Diskon ${roleName} (${d.nilai}%)` : `Diskon ${roleName} (Nominal)`,
            nominal: diskonMember.value
        });
    }

    const payloadData = {
        id_sku: form.value.id_sku,
        nama_produk_snapshot: selectedSku.value?.nama_sku,
        jumlah: form.value.jumlah,
        catatan: form.value.catatan,
        estimasi_pengerjaan: form.value.estimasi_pengerjaan,

        harga_dasar_awal_snapshot: hargaDasarAwal.value,
        total_diskon_snapshot: totalDiskonSatuan.value,
        rincian_diskon_snapshot: props.isPosMode ? rincianDiskon : JSON.stringify(rincianDiskon),

        harga_satuan_snapshot: hargaSatuanSnapshot.value,
        harga_pengerjaan_snapshot: totalSla.value,

        finishing: props.isPosMode ? finishingPayload.value : JSON.stringify(finishingPayload.value),

        file: form.value.desainPayload.file,
        tipe_file: form.value.desainPayload.tipe_file,
        link_file: form.value.desainPayload.link_file,

        total_produk: totalProduk.value,
        total_sla: totalSla.value,
        subtotal: subtotalItem.value,

        ...(props.isPosMode && {
            master_diskon_customer: selectedSku.value?.diskon_customer || [],
            master_harga_bertingkat: selectedSku.value?.harga_bertingkat || [],
            master_harga_pengerjaan: selectedSku.value?.harga_pengerjaan || [],
        })
    };
    emit('submit', payloadData);
};
</script>

<template>
    <div class="relative overflow-hidden border shadow-xl bg-base-100 border-primary/20 rounded-2xl">
        <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0" style="background-image: radial-gradient(currentColor 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 left-0 w-full h-1.5 bg-primary z-10"></div>
        <div v-if="isFetching || isSubmitting" class="absolute inset-0 z-50 flex items-center justify-center bg-base-100/60 backdrop-blur-sm">
            <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>

        <div class="relative z-10 flex items-center gap-4 px-8 py-6 border-b border-base-200/50 bg-base-100/50 backdrop-blur-sm">
            <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-primary/10 text-primary">
                <svg v-if="editData" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            </div>
            <div>
                <h2 class="text-xl font-black tracking-tight text-base-content">
                    {{ editData ? 'Edit Spesifikasi Item' : 'Tambah Produk Pesanan' }}
                </h2>
                <p class="text-xs font-medium text-base-content/50 mt-0.5">
                    {{ editData ? editData.nama_produk_snapshot : 'Lengkapi data untuk menambahkan item ke tagihan' }}
                </p>
            </div>
        </div>

        <div class="relative z-10 grid grid-cols-1 gap-8 px-8 lg:grid-cols-12 lg:gap-12">

            <div class="space-y-6 lg:col-span-6">
                <h3 class="flex items-center gap-2 mb-2 text-xs font-black tracking-widest uppercase text-primary">Spesifikasi Dasar</h3>
                <div class="space-y-5">
                    <CustomSelectSearch v-model="form.id_produk" label="Pilih Produk Master" :options="listProduks" labelKey="nama_produk" valueKey="id_produk" placeholder="-- Cari Produk --" />

                    <CustomSelectSearch v-model="form.id_sku" label="Varian & Bahan" :options="skuOptions" labelKey="label" valueKey="value" placeholder="-- Pilih Varian --" />

                    <CustomSelectSearch v-model="form.estimasi_pengerjaan" label="Kecepatan Pengerjaan (SLA)" :options="pengerjaanOptions" labelKey="label" valueKey="value" :add-option="false" />

                    <div class="form-control">
                        <CustomInput label="Jumlah Pesanan (Qty)" type="number" v-model="form.jumlah" :min="currentMinimumOrder" />
                        <div v-if="form.jumlah < currentMinimumOrder" class="flex items-center gap-1.5 mt-2 text-[10px] font-bold text-error">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
                            Minimal pemesanan untuk spesifikasi ini: {{ currentMinimumOrder }} pcs
                        </div>
                    </div>

                    <CustomTextarea label="Catatan Internal / Revisi Produksi" v-model="form.catatan" :rows="2" placeholder="Cth: Potong putus keliling..." />
                </div>

                <div class="absolute hidden w-px -translate-x-1/2 lg:block top-8 bottom-8 left-1/2 bg-base-200/80"></div>
            </div>

            <div class="space-y-8 lg:col-span-6">

                <div>
                    <h3 class="flex items-center gap-2 mb-4 text-xs font-black tracking-widest uppercase text-primary">Tambahan Finishing</h3>
                    <div class="space-y-4">
                        <template v-if="Object.keys(finishingGroups).length > 0">
                            <div v-for="(group, name) in finishingGroups" :key="name">
                                <CustomSelectSearch :label="name" v-model="form.finishings[name]" :options="group.options" labelKey="label" valueKey="value" :add-option="false" />
                            </div>
                        </template>
                        <div v-else class="py-2 text-xs italic font-medium text-base-content/40">
                            Pilih varian produk terlebih dahulu.
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="flex items-center gap-2 mb-2 text-xs font-black tracking-widest uppercase text-primary">Desain & Instruksi</h3>
                    <CustomInputFile v-model="form.desainPayload" />
                </div>
            </div>
        </div>

        <div class="relative z-10 flex flex-col items-center justify-between gap-4 px-8 py-5 border-t bg-base-100/90 backdrop-blur-md border-base-200 sm:flex-row">
            <div class="text-center sm:text-left">
                <p class="text-[10px] font-black uppercase tracking-widest text-base-content/40 mb-1">Subtotal Item Ini</p>
                <p class="text-2xl font-black leading-none tracking-tighter text-primary">Rp {{ subtotalItem.toLocaleString('id-ID') }}</p>
            </div>

            <div class="flex items-center w-full gap-3 sm:w-auto">
                <CustomButton variant="secondary" @click.prevent="emit('cancel')" class="w-full px-6 text-xs font-bold tracking-wider uppercase sm:w-auto rounded-xl">
                    Batal
                </CustomButton>
                <CustomButton @click="handleFormSubmit" class="w-full px-8 text-xs font-black tracking-widest uppercase shadow-lg sm:w-auto rounded-xl shadow-primary/20">
                    {{ editData ? 'Simpan Perubahan' : 'Selesai & Tambahkan' }}
                </CustomButton>
            </div>
        </div>

    </div>
</template>
