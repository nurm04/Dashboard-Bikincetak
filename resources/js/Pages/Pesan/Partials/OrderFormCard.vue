<script setup>
import { ref, watch, computed, onMounted, nextTick, defineAsyncComponent } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomTextarea from '@/Components/Form/CustomTextarea.vue';
import CustomSelectSearch from '@/Components/Form/CustomSelectSearch.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomInputFile from '@/Components/Form/CustomInputFile.vue';
import { alertStore } from '@/Utils/alertStore';
import CustomInputNumber from '@/Components/Form/CustomInputNumber.vue';

const props = defineProps({
    editData: Object,
    isPosMode: { type: Boolean, default: false }
});
const emit = defineEmits(['cancel', 'submit']);

// ==== REGISTRASI KOMPONEN DINAMIS ====
const FormCetakBuku = defineAsyncComponent(() => import('./FormCetakBuku.vue'));
const FormCetakMeteran = defineAsyncComponent(() => import('./FormCetakMeteran.vue'));

const formKalkulatorMap = {
    'cetak_buku': FormCetakBuku,
    'cetak_meteran': FormCetakMeteran,
};

const activeFormKalkulator = computed(() => formKalkulatorMap[tipeKalkulasi.value] || null);
const biayaTambahanCustom = ref(0);
// =====================================

const listProduks = ref([]);
const detailProduk = ref(null);
const isFetching = ref(false);
const activeCustomer = ref(null);

const form = ref({
    id_produk: '',
    id_sku: '',
    estimasi_pengerjaan: 'Reguler',
    finishings: {},
    desainPayload: { tipe_file: 'upload', file: null, link_file: '' },
    jumlah: 1,
    catatan: '',
    custom_nama_produk: '',
    custom_harga_satuan: 0,
    custom_sla_price: 0, // Ditambahkan untuk menampung harga pengerjaan custom
    custom_attributes: {},
});

const isCustomProduct = computed(() => form.value.id_produk === 'PRD-0001');
const isJasaDesain = computed(() => form.value.id_produk === 'PRD-0002');

onMounted(() => {
    activeCustomer.value = JSON.parse(localStorage.getItem('pos_active_customer'));
    fetchAllProduks();
});

const fetchAllProduks = async () => {
    try { listProduks.value = (await axios.get('/api/items')).data.data; }
    catch (e) { alertStore.show('Gagal ambil data produk', 'error'); }
};

const fetchDetailProduk = async (id) => {
    if (!id || id === 'PRD-0001') return;

    isFetching.value = true;
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
        desainPayload: { tipe_file: 'upload', file: null, link_file: '' }, jumlah: 1, catatan: '',
        custom_nama_produk: '', custom_harga_satuan: 0, custom_sla_price: 0, custom_attributes: {}
    };

    const isEditingCustom = newVal.id_sku === 'PRD-0001-SKU-001';
    form.value.id_produk = isEditingCustom ? 'PRD-0001' : newVal.id_sku.split('-SKU-')[0];

    if (isEditingCustom) {
        form.value.id_sku = 'PRD-0001-SKU-001';
        form.value.custom_nama_produk = newVal.nama_produk_snapshot;
        form.value.custom_harga_satuan = newVal.harga_satuan_snapshot;
        form.value.custom_sla_price = newVal.harga_pengerjaan_snapshot || 0; // Load SLA produk custom
    } else {
        await fetchDetailProduk(form.value.id_produk);
    }

    await nextTick();

    form.value.id_sku = newVal.id_sku;
    form.value.jumlah = newVal.jumlah;
    form.value.catatan = newVal.catatan;
    form.value.estimasi_pengerjaan = newVal.estimasi_pengerjaan_snapshot || 'Reguler';

    let parsedCustom = {};
    if (newVal.atribut_custom_snapshot) {
        parsedCustom = typeof newVal.atribut_custom_snapshot === 'string'
            ? JSON.parse(newVal.atribut_custom_snapshot)
            : newVal.atribut_custom_snapshot;
    }
    form.value.custom_attributes = parsedCustom || {};

    if (!isEditingCustom && newVal.pesanan_item_finishing?.length) {
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
const tipeKalkulasi = computed(() => selectedSku.value?.tipe_kalkulasi || 'standard');

// Reset tambahan biaya ketika SKU/Tipe diganti
watch(tipeKalkulasi, () => {
    biayaTambahanCustom.value = 0;
});

const finishingPayload = computed(() => {
    if (isCustomProduct.value || isJasaDesain.value) return [];

    const data = [];
    Object.values(form.value.finishings).forEach(idSkuFin => {
        if (!idSkuFin) return;

        const fin = selectedSku.value?.opsi_finishing?.find(f => String(f.id_sku_finishing) === String(idSkuFin));
        if (!fin) return;

        const isKaliQty = fin.kali_jumlah_pesan === true || fin.kali_jumlah_pesan === 1 || fin.kali_jumlah_pesan === '1';

        data.push({
            id_sku_finishing: fin.id_sku_finishing,
            nama_finishing_snapshot: `${fin.kategori_finishing}: ${fin.nama_pilihan}`,
            harga_finishing_snapshot: fin.harga_tambahan,
            tipe: fin.tipe || 'nominal',
            kali_jumlah_pesan: isKaliQty
        });
    });
    return data;
});

const hargaDasarAwal = computed(() => {
    if (isCustomProduct.value) return Number(form.value.custom_harga_satuan) || 0;
    return Number(selectedSku.value?.harga_dasar) || 0;
});

const diskonGrosir = computed(() => {
    if (isCustomProduct.value || !selectedSku.value) return 0;
    const tier = [...(selectedSku.value.harga_bertingkat || [])]
        .sort((a, b) => b.min - a.min)
        .find(t => form.value.jumlah >= t.min && (t.max === 0 || t.max === null || form.value.jumlah <= t.max));

    if (!tier) return 0;
    return tier.tipe === 'persen' ? hargaDasarAwal.value * (Number(tier.nilai) / 100) : Number(tier.nilai);
});

const diskonMember = computed(() => {
    if (isCustomProduct.value) return 0;
    const roleId = activeCustomer.value?.id_role_customer;
    if (!roleId || !selectedSku.value?.diskon_customer) return 0;
    const d = selectedSku.value.diskon_customer.find(d => String(d.id_role_customer) === String(roleId));

    if (!d) return 0;
    return d.tipe === 'persen' ? hargaDasarAwal.value * (Number(d.nilai) / 100) : Number(d.nilai);
});

const totalDiskonSatuan = computed(() => diskonGrosir.value + diskonMember.value);
const hargaSatuanSnapshot = computed(() => Math.max(0, hargaDasarAwal.value - totalDiskonSatuan.value));

const hargaSatuProdukFull = computed(() => {
    // Gabung harga Dasar dengan hasil hitungan dari FormCetakBuku.vue
    return hargaSatuanSnapshot.value + biayaTambahanCustom.value;
});

const pengerjaanOptions = computed(() => {
    if (isCustomProduct.value || isJasaDesain.value) return [];
    const list = (selectedSku.value?.harga_pengerjaan || []).map(p => {
        const labelBiaya = p.tipe === 'persen' ? `${p.nilai}%` : `Rp ${Number(p.nilai).toLocaleString('id-ID')}`;
        return { value: p.pengerjaan, label: `${p.pengerjaan} (+ ${labelBiaya})` };
    });
    if (isCustomSla.value) {
        list.push({ value: form.value.estimasi_pengerjaan, label: `${form.value.estimasi_pengerjaan} (Custom)` });
    }
    return list;
});

const isCustomSla = computed(() => {
    if (isCustomProduct.value || isJasaDesain.value) return false;
    if (!form.value.estimasi_pengerjaan || form.value.estimasi_pengerjaan === 'Reguler') return false;
    const p = selectedSku.value?.harga_pengerjaan?.find(o => o.pengerjaan === form.value.estimasi_pengerjaan);
    return !p;
});

const totalFinishing = computed(() => {
    if (isCustomProduct.value || isJasaDesain.value) return 0;
    let total = 0;

    Object.values(form.value.finishings).forEach(idSkuFin => {
        if (!idSkuFin) return;
        const fin = selectedSku.value?.opsi_finishing?.find(f => String(f.id_sku_finishing) === String(idSkuFin));
        if (!fin) return;

        let biaya = 0;
        const tipeFinishing = fin.tipe || 'nominal'; // Cegah nilai null/kosong

        if (tipeFinishing === 'persen') {
            biaya = hargaSatuProdukFull.value * (Number(fin.harga_tambahan) / 100);
        } else {
            biaya = Number(fin.harga_tambahan) || 0;
        }

        if (fin.kali_jumlah_pesan) {
            biaya = biaya * Number(form.value.jumlah || 1);
        }

        total += biaya;
    });
    return total;
});

const totalHargaProdukUtama = computed(() => hargaSatuProdukFull.value * form.value.jumlah);
const totalProduk = computed(() => totalHargaProdukUtama.value + totalFinishing.value);

const totalSla = computed(() => {
    if (isJasaDesain.value) return 0;

    // Pastikan custom SLA terbaca untuk produk custom (PRD-0001)
    if (isCustomProduct.value) return Number(form.value.custom_sla_price) || 0;

    const p = selectedSku.value?.harga_pengerjaan?.find(o => o.pengerjaan === form.value.estimasi_pengerjaan);

    // Jika SLA Custom, ambil angka murni dari input SLA Custom
    if (!p) {
        return isCustomSla.value ? (Number(form.value.custom_sla_price) || 0) : 0;
    }

    const tipeSla = p.tipe || 'nominal'; // Cegah nilai null/kosong

    if (tipeSla === 'persen') {
        return totalProduk.value * (Number(p.nilai) / 100);
    } else {
        return Number(p.nilai) || 0;
    }
});

const subtotalItem = computed(() => totalProduk.value + totalSla.value);

const finishingGroups = computed(() => {
    if (isCustomProduct.value || isJasaDesain.value) return {};
    const groups = {};
    selectedSku.value?.opsi_finishing?.forEach(fin => {
        if (!groups[fin.kategori_finishing]) {
            groups[fin.kategori_finishing] = { options: [] };
        }

        groups[fin.kategori_finishing].options.push({
            value: fin.id_sku_finishing,
            label: `${fin.nama_pilihan} (+ Rp ${fin.harga_tambahan.toLocaleString('id-ID')})`,
            harga: Number(fin.harga_tambahan)
        });
    });

    for (const cat in groups) {
        const hasZero = groups[cat].options.some(opt => opt.harga === 0);
        if (!hasZero) {
            groups[cat].options.unshift({ value: '', label: `Tanpa ${cat} (+ Rp 0)`, harga: 0 });
        }
    }
    return groups;
});

const currentMinimumOrder = computed(() => {
    if (isCustomProduct.value || isJasaDesain.value) return 1;
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
    if (isCustomProduct.value || isJasaDesain.value) return;
    if (!selectedSku.value) return;

    if (!isEditMode.value) {
        form.value.custom_attributes = {};
    }

    if (isEditMode.value && !oldVal) return;

    form.value.finishings = {};

    const groups = finishingGroups.value;
    for (const cat in groups) {
        const opts = groups[cat].options;
        // Cari opsi yang harganya Rp 0 (kalau 0 semua, otomatis terpilih urutan paling pertama)
        const zeroOpt = opts.find(o => o.harga === 0);
        if (zeroOpt) {
            form.value.finishings[cat] = zeroOpt.value;
        } else {
            form.value.finishings[cat] = opts[0].value;
        }
    }

    form.value.jumlah = selectedSku.value.minimum_pesan || 1;
    form.value.estimasi_pengerjaan = selectedSku.value.harga_pengerjaan?.[0]?.pengerjaan || 'Reguler';
});

watch(() => form.value.id_produk, (val) => {
    if (val === 'PRD-0001') {
        form.value.id_sku = 'PRD-0001-SKU-001';
        form.value.custom_nama_produk = '';
        form.value.custom_harga_satuan = 0;
        form.value.finishings = {};
        form.value.estimasi_pengerjaan = 'Reguler';
        form.value.custom_sla_price = 0; // Reset saat switch mode
        form.value.custom_attributes = {};
        form.value.jumlah = 1;
    } else if (val === 'PRD-0002') {
        form.value.finishings = {};
        form.value.estimasi_pengerjaan = 'Reguler';
        form.value.custom_attributes = {};
        form.value.jumlah = 1;
        fetchDetailProduk(val);
    } else {
        fetchDetailProduk(val);
    }
});

const handleAddCustomSla = (newSlaValue) => {
    if (!newSlaValue || newSlaValue.trim() === '') {
        alertStore.show('Ketik nama estimasi pengerjaan terlebih dahulu!', 'warning');
        return;
    }
    form.value.estimasi_pengerjaan = newSlaValue;
    form.value.custom_sla_price = 0;
};

const handleFormSubmit = () => {
    if (isCustomProduct.value && !form.value.custom_nama_produk) {
        alertStore.show('Nama produk custom wajib diisi!', 'error');
        return;
    }
    if (!isCustomProduct.value && !form.value.id_sku) {
        alertStore.show('Pilih varian produk!', 'error');
        return;
    }

    const rincianDiskon = [];

    if (diskonGrosir.value > 0) {
        const tier = [...(selectedSku.value.harga_bertingkat || [])]
            .sort((a, b) => b.min - a.min)
            .find(t => form.value.jumlah >= t.min && (t.max === 0 || t.max === null || form.value.jumlah <= t.max));

        rincianDiskon.push({
            nama: tier?.tipe === 'persen' ? `Harga Grosir Qty ${form.value.jumlah} (${tier.nilai}%)` : `Harga Grosir Qty ${form.value.jumlah}`,
            nominal: diskonGrosir.value
        });
    }

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
        nama_produk_snapshot: isCustomProduct.value ? form.value.custom_nama_produk : selectedSku.value?.nama_sku,
        jumlah: form.value.jumlah,
        catatan: form.value.catatan,

        estimasi_pengerjaan: form.value.estimasi_pengerjaan,
        tipe_kalkulasi: tipeKalkulasi.value,

        harga_dasar_awal_snapshot: hargaDasarAwal.value,
        total_diskon_snapshot: totalDiskonSatuan.value,
        rincian_diskon_snapshot: props.isPosMode ? rincianDiskon : JSON.stringify(rincianDiskon),

        harga_satuan_snapshot: hargaSatuanSnapshot.value,

        // harga pengerjaan akan mengambil nilai dari "totalSla" di mana untuk produk custom sudah diarahkan membaca custom_sla_price
        harga_pengerjaan_snapshot: totalSla.value,

        finishing: props.isPosMode ? finishingPayload.value : JSON.stringify(finishingPayload.value),

        atribut_custom_snapshot: Object.keys(form.value.custom_attributes).length > 0 ? form.value.custom_attributes : undefined,

        file: isJasaDesain.value ? null : form.value.desainPayload.file,
        tipe_file: isJasaDesain.value ? null : form.value.desainPayload.tipe_file,
        link_file: isJasaDesain.value ? null : form.value.desainPayload.link_file,

        total_produk: totalProduk.value,
        total_sla: totalSla.value,
        subtotal: subtotalItem.value,

        ...(props.isPosMode && {
            master_diskon_customer: isCustomProduct.value ? [] : selectedSku.value?.diskon_customer || [],
            master_harga_bertingkat: isCustomProduct.value ? [] : selectedSku.value?.harga_bertingkat || [],
            master_harga_pengerjaan: isCustomProduct.value ? [] : selectedSku.value?.harga_pengerjaan || [],
        })
    };

    emit('submit', payloadData);
};
</script>

<template>
    <div class="relative border shadow-xl bg-base-100 border-primary/20 rounded-2xl">

        <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0 rounded-2xl overflow-hidden" style="background-image: radial-gradient(currentColor 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 left-0 w-full h-1.5 bg-primary z-10 rounded-t-2xl"></div>

        <div v-if="isFetching || isSubmitting" class="absolute inset-0 z-50 flex items-center justify-center bg-base-100/60 backdrop-blur-sm rounded-2xl">
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

        <div class="relative z-30 grid grid-cols-1 gap-8 px-8 pt-6 pb-8 lg:grid-cols-12 lg:gap-12">
            <div :class="['space-y-6', isJasaDesain ? 'lg:col-span-12 max-w-2xl' : 'lg:col-span-6']">
                <h3 class="flex items-center gap-2 mb-2 text-xs font-black tracking-widest uppercase text-primary">Spesifikasi Dasar</h3>
                <div class="space-y-5">
                    <CustomSelectSearch v-model="form.id_produk" label="Pilih Produk Master" :options="listProduks" labelKey="nama_produk" valueKey="id_produk" placeholder="-- Cari Produk --" />

                    <template v-if="isCustomProduct">
                        <CustomInput label="Nama Produk / Pesanan" type="text" v-model="form.custom_nama_produk" placeholder="Ketik nama pesanan secara manual..." />
                        <CustomInput label="Harga Satuan (Rp)" type="number" v-model="form.custom_harga_satuan" />

                        <!-- TAMBAHAN: Field Estimasi Pengerjaan & Harga Pengerjaan khusus Custom Product -->
                        <CustomInput label="Estimasi Pengerjaan" type="text" v-model="form.estimasi_pengerjaan" placeholder="Cth: 1 Hari, Kilat, dll..." />
                        <CustomInputNumber label="Harga Pengerjaan (Rp)" v-model="form.custom_sla_price" placeholder="Tarif pengerjaan (opsional)..." />
                    </template>

                    <template v-else>
                        <CustomSelectSearch v-model="form.id_sku" label="Varian & Bahan" :options="skuOptions" labelKey="label" valueKey="value" placeholder="-- Pilih Varian --" />
                        <CustomSelectSearch
                            v-if="!isJasaDesain"
                            v-model="form.estimasi_pengerjaan"
                            label="Kecepatan Pengerjaan (SLA)"
                            :options="pengerjaanOptions"
                            labelKey="label"
                            valueKey="value"
                            :add-option="true"
                            @onCreate="handleAddCustomSla"
                            placeholder="Pilih atau Ketik SLA Custom..."
                        />
                        <div v-if="isCustomSla && !isJasaDesain" class="p-4 mt-3 border border-dashed rounded-xl bg-primary/5 border-primary/30">
                            <h4 class="mb-3 text-[10px] font-bold tracking-widest uppercase text-primary">Tarif Tambahan SLA Custom</h4>
                            <CustomInputNumber
                                label="Nominal Harga SLA (Rp)"
                                v-model="form.custom_sla_price"
                                placeholder="Masukan tarif pengerjaan kilat..."
                            />
                        </div>
                    </template>

                    <template v-if="!isJasaDesain">

                        <!-- KOMPONEN KHUSUS DIPANGGIL DISINI -->
                        <component
                            :is="activeFormKalkulator"
                            v-if="activeFormKalkulator"

                            :finishings="form.finishings"
                            :selectedSku="selectedSku"
                            :initialAttributes="form.custom_attributes"

                            :hargaSatuanSnapshot="hargaSatuanSnapshot"
                            :qty="form.jumlah"

                            @updateAttributes="(val) => form.custom_attributes = val"
                            @updateBiayaTambahan="(val) => biayaTambahanCustom = val"
                        />

                        <div class="form-control">
                            <CustomInputNumber label="Jumlah Pesanan (Qty)" v-model="form.jumlah" :min="currentMinimumOrder" />
                            <div v-if="form.jumlah < currentMinimumOrder" class="flex items-center gap-1.5 mt-2 text-[10px] font-bold text-error">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
                                Minimal pemesanan untuk spesifikasi ini: {{ currentMinimumOrder }} pcs
                            </div>
                        </div>

                        <CustomTextarea label="Catatan Internal / Revisi Produksi" v-model="form.catatan" :rows="2" placeholder="Cth: Potong putus keliling, bahan bawa sendiri, dll..." />
                    </template>
                </div>

                <div v-if="!isJasaDesain" class="absolute hidden w-px -translate-x-1/2 lg:block top-8 bottom-8 left-1/2 bg-base-200/80"></div>
            </div>
            <div v-if="!isJasaDesain" class="space-y-8 lg:col-span-6">
                <div v-if="!isCustomProduct">
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

        <!-- Panel Bawah tetap z-10 (lebih rendah dari z-30 di atas) -->
        <div class="relative z-10 flex flex-col items-center justify-between gap-4 px-8 py-5 border-t bg-base-100/90 backdrop-blur-md border-base-200 sm:flex-row rounded-b-2xl">
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
