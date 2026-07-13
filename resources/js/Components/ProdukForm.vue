<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import CustomSelect from '@/Components/CustomSelect.vue';
import CustomInputNumber from '@/Components/CustomInputNumber.vue';
import CustomTextarea from '@/Components/CustomTextarea.vue';

const initDB = () => new Promise((resolve, reject) => {
    const req = indexedDB.open('POS_DB', 1);
    req.onupgradeneeded = () => req.result.createObjectStore('cart_files');
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
});

const saveFilesToDB = async (id, files) => {
    const db = await initDB();
    return new Promise((resolve) => {
        const tx = db.transaction('cart_files', 'readwrite');
        tx.objectStore('cart_files').put(files, id);
        tx.oncomplete = resolve;
    });
};

const props = defineProps({
    produk: {
        type: Object,
        required: true
    }
});

const activeCustomer = ref(null);
onMounted(() => {
    activeCustomer.value = JSON.parse(localStorage.getItem('pos_active_customer')) || null;
});

const selectedSkuId = ref(props.produk.produk_sku?.[0]?.id_sku || '');
const selectedPengerjaanId = ref('');
const qty = ref(1);
const catatan = ref('');
const fileDesain = ref([]);
const selectedFinishings = ref({});

const selectedSku = computed(() => {
    return props.produk.produk_sku?.find(s => s.id_sku === selectedSkuId.value) || null;
});

const sortedHargaBertingkat = computed(() => {
    if (!selectedSku.value || !selectedSku.value.harga_bertingkat) return [];
    return [...selectedSku.value.harga_bertingkat].sort((a, b) => a.min - b.min);
});

const pengerjaanOptions = computed(() => {
    if (!selectedSku.value || !selectedSku.value.harga_pengerjaan) return [];

    const list = selectedSku.value.harga_pengerjaan.map(p => ({
        id: p.id,
        pengerjaan: p.pengerjaan,
        harga: p.harga,
        label: `${p.pengerjaan} (+ ${formatRupiah(p.harga)} Flat)`
    }));

    list.unshift({
        id: '',
        pengerjaan: 'Reguler',
        harga: 0,
        label: 'Tanpa SLA / Pengerjaan Reguler'
    });

    return list;
});

const finishingGroups = computed(() => {
    if (!selectedSku.value || !selectedSku.value.sku_finishing) return {};

    const groups = {};
    selectedSku.value.sku_finishing.forEach(fin => {
        const masterFinishing = fin.pilihan_finishing?.finishing;
        const groupId = masterFinishing?.id_finishing || 'lainnya';
        const groupName = masterFinishing?.nama_finishing || 'Finishing Tambahan';

        if (!groups[groupId]) {
            groups[groupId] = {
                nama: groupName,
                options: [
                    { id: '', nama_pilihan: `Tanpa ${groupName}`, harga_tambahan: 0 }
                ]
            };
        }

        groups[groupId].options.push({
            id: fin.id,
            nama_pilihan: `${fin.pilihan_finishing?.nama_pilihan} (+ ${formatRupiah(fin.harga_tambahan)}/pcs)`,
            harga_tambahan: fin.harga_tambahan
        });
    });
    return groups;
});

const currentMinimumOrder = computed(() => {
    let currentMin = selectedSku.value?.minimum_pesan || 1;

    Object.values(selectedFinishings.value).forEach(finId => {
        if (!finId) return;
        const fin = selectedSku.value?.sku_finishing?.find(f => f.id === finId);
        if (fin && fin.minimum_pesan > currentMin) {
            currentMin = fin.minimum_pesan;
        }
    });

    return currentMin;
});

watch(currentMinimumOrder, (newMin) => {
    if (qty.value < newMin) {
        qty.value = newMin;
        alertStore.show(`Jumlah pesanan disesuaikan ke batas minimum order: ${newMin} pcs`, 'info');
    }
});

watch(selectedSkuId, () => {
    selectedFinishings.value = {};
    selectedPengerjaanId.value = '';

    if (selectedSku.value?.harga_pengerjaan?.length > 0) {
        selectedPengerjaanId.value = selectedSku.value.harga_pengerjaan[0].id;
    }

    qty.value = currentMinimumOrder.value;
}, { immediate: true });

const diskonRoleAktif = computed(() => {
    if (!activeCustomer.value || !activeCustomer.value.id_role_customer) return null;
    if (!selectedSku.value || !selectedSku.value.diskon_customer) return null;

    return selectedSku.value.diskon_customer.find(
        d => String(d.id_role_customer) === String(activeCustomer.value.id_role_customer)
    ) || null;
});

const hargaDasarAwal = computed(() => {
    if (!selectedSku.value) return 0;
    let base = selectedSku.value.harga;

    if (selectedSku.value.harga_bertingkat?.length > 0) {
        const tier = [...selectedSku.value.harga_bertingkat]
            .sort((a, b) => b.min - a.min)
            .find(t => qty.value >= t.min && (t.max === 0 || t.max === null || qty.value <= t.max));
        if (tier) base = tier.harga;
    }
    return base;
});

const rincianDiskonArray = computed(() => {
    const rincian = [];

    if (diskonRoleAktif.value) {
        let nominalPotongan = 0;
        let namaPotongan = '';
        const roleName = activeCustomer.value?.role_customer?.nama_role || 'Member';

        if (diskonRoleAktif.value.tipe === 'persen') {
            nominalPotongan = hargaDasarAwal.value * (Number(diskonRoleAktif.value.nilai) / 100);
            namaPotongan = `Diskon ${roleName} (${diskonRoleAktif.value.nilai}%)`;
        } else if (diskonRoleAktif.value.tipe === 'nominal') {
            nominalPotongan = Number(diskonRoleAktif.value.nilai);
            namaPotongan = `Diskon ${roleName} (Nominal)`;
        }

        if (nominalPotongan > 0) {
            rincian.push({
                nama: namaPotongan,
                nominal: nominalPotongan
            });
        }
    }

    return rincian;
});

const totalDiskonNominal = computed(() => {
    return rincianDiskonArray.value.reduce((acc, curr) => acc + curr.nominal, 0);
});

const hargaDasar = computed(() => {
    let base = hargaDasarAwal.value - totalDiskonNominal.value;
    return base > 0 ? base : 0;
});

const hargaPengerjaan = computed(() => {
    if (!selectedPengerjaanId.value) return 0;
    const p = pengerjaanOptions.value.find(opt => opt.id === selectedPengerjaanId.value);
    return p ? p.harga : 0;
});

const hargaFinishing = computed(() => {
    if (!selectedSku.value || Object.keys(selectedFinishings.value).length === 0) return 0;

    let total = 0;
    Object.values(selectedFinishings.value).forEach(finId => {
        if (!finId) return;
        const fin = selectedSku.value.sku_finishing?.find(f => f.id === finId);
        if (fin) total += fin.harga_tambahan;
    });
    return total;
});

const grandTotal = computed(() => {
    const totalProdukDanFinishing = (hargaDasar.value + hargaFinishing.value) * qty.value;
    return totalProdukDanFinishing + hargaPengerjaan.value;
});

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka);
};

const handleFileUpload = (e) => {
    const files = Array.from(e.target.files);
    fileDesain.value = [...fileDesain.value, ...files];
};

const removeFile = (index) => {
    fileDesain.value.splice(index, 1);
};

const editCartItemId = ref(new URLSearchParams(window.location.search).get('edit_item_id') || null);

const tambahKeKeranjang = async () => {
    if (!selectedSku.value) {
        alertStore.show('Pilih varian/bahan terlebih dahulu!', 'error');
        return;
    }

    if (qty.value < currentMinimumOrder.value) {
        alertStore.show(`Jumlah pesanan kurang dari minimum order (${currentMinimumOrder.value} pcs)!`, 'error');
        return;
    }

    const selectedFinishingData = [];
    Object.entries(selectedFinishings.value).forEach(([groupId, finId]) => {
        if (!finId) return;
        const fin = selectedSku.value.sku_finishing?.find(f => f.id === finId);
        if (fin) {
            const masterName = fin.pilihan_finishing?.finishing?.nama_finishing || 'Finishing';
            selectedFinishingData.push({
                id_sku_finishing: fin.id,
                nama_finishing_snapshot: `${masterName.toUpperCase()}: ${fin.pilihan_finishing?.nama_pilihan}`,
                harga_finishing_snapshot: fin.harga_tambahan
            });
        }
    });

    const selectedPengerjaanObj = pengerjaanOptions.value.find(opt => opt.id === selectedPengerjaanId.value);
    const pengerjaanSnapshot = selectedPengerjaanObj ? selectedPengerjaanObj.pengerjaan : 'Reguler';

    let cart = JSON.parse(localStorage.getItem('pos_cart')) || [];

    const newItem = {
        cart_item_id: editCartItemId.value ? editCartItemId.value : 'item_' + Date.now(),
        id_sku: selectedSku.value.id_sku,
        nama_produk_snapshot: selectedSku.value.nama_sku,
        jumlah: qty.value,
        harga_satuan_snapshot: hargaDasar.value + hargaFinishing.value,
        harga_pengerjaan_snapshot: hargaPengerjaan.value,
        catatan: catatan.value,
        estimasi_pengerjaan: pengerjaanSnapshot,
        finishings: selectedFinishingData,
        file_desain: fileDesain.value,
        nama_file_preview: fileDesain.value.map(f => f.name),
        harga_dasar_awal_snapshot: hargaDasarAwal.value,
        total_diskon_snapshot: totalDiskonNominal.value,
        rincian_diskon_snapshot: rincianDiskonArray.value
    };

    if (fileDesain.value.length > 0) {
        await saveFilesToDB(newItem.cart_item_id, [...fileDesain.value]);
    }

    if (editCartItemId.value) {
        const indexLama = cart.findIndex(item => item.cart_item_id === editCartItemId.value);
        if (indexLama !== -1) {
            cart[indexLama] = newItem;
            alertStore.show('Detail item keranjang berhasil diperbarui!', 'success');
        }
    } else {
        cart.push(newItem);
        alertStore.show('Produk berhasil masuk keranjang!', 'success');
    }

    localStorage.setItem('pos_cart', JSON.stringify(cart));

    router.visit(route('pos.katalog'));
};
</script>

<template>
    <div class="space-y-8">
        <div class="grid grid-cols-1 gap-8 p-6 border shadow-sm lg:grid-cols-12 bg-base-100 border-base-300 rounded-2xl">
            <div class="flex justify-center lg:col-span-4 lg:justify-start">
                <div class="relative w-full max-w-md overflow-hidden border shadow-sm aspect-square bg-base-100 rounded-2xl border-base-200">
                    <img :src="produk.gambar || '/favicon.ico'" :alt="produk.nama_produk" class="object-cover w-full h-full" />
                </div>
            </div>

            <div class="flex flex-col gap-4 lg:col-span-8">
                <h1 class="text-2xl font-black lg:text-4xl text-base-content">{{ produk.nama_produk }}</h1>
                <p class="max-w-3xl text-sm leading-relaxed text-justify opacity-70">
                    Sticker berukuran custom Print Digital Press dengan spesifikasi terbaik. Pastikan resolusi desain Anda cukup agar hasil cetak maksimal dan tidak pecah.
                </p>

                <div class="max-w-xl mt-4">
                    <h4 class="mb-3 text-xs font-black tracking-widest uppercase opacity-50">Harga Per Pcs ({{ selectedSku?.nama_sku || 'Pilih Varian' }})</h4>
                    <div v-if="sortedHargaBertingkat.length > 0" class="overflow-hidden border shadow-sm rounded-xl border-base-300 bg-base-100">
                        <table class="table w-full text-xs table-sm table-zebra">
                            <tbody>
                                <tr v-for="tier in sortedHargaBertingkat" :key="tier.id">
                                    <td class="py-3 font-bold opacity-70">
                                        <span v-if="tier.max === 0 || tier.max === null">≥ {{ tier.min }} pcs</span>
                                        <span v-else>{{ tier.min }} - {{ tier.max }} pcs</span>
                                    </td>
                                    <td class="py-3 font-black text-right text-primary">{{ formatRupiah(tier.harga) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="p-4 text-xs font-bold text-center uppercase border opacity-50 bg-base-100 rounded-xl border-base-300">
                        Tidak ada harga grosir untuk varian ini.
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 border shadow-sm bg-base-100 border-base-300 rounded-2xl">
            <h3 class="pb-2 mb-6 text-xs font-black tracking-widest uppercase border-b opacity-50 border-base-300">Kustomisasi & Spesifikasi</h3>
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                <div class="space-y-6">
                    <div>
                        <CustomSelect
                            v-model="selectedSkuId"
                            :options="produk.produk_sku"
                            labelKey="nama_sku"
                            valueKey="id_sku"
                            label="Bahan / Varian Produk"
                        />
                    </div>

                    <div>
                        <CustomSelect
                            v-model="selectedPengerjaanId"
                            :options="pengerjaanOptions"
                            labelKey="label"
                            valueKey="id"
                            label="Estimasi Pengerjaan (SLA)"
                        />
                    </div>

                    <div>
                        <CustomInputNumber
                            v-model="qty"
                            :min="currentMinimumOrder"
                            label="Jumlah / Qty (Pcs)"
                        />
                        <p class="text-[9px] font-bold text-error mt-1 ml-1 uppercase tracking-wider">
                            🔒 Batas Minimum Order Saat Ini: {{ currentMinimumOrder }} pcs
                        </p>
                    </div>

                    <CustomTextarea
                        v-model="catatan"
                        label="Catatan Tambahan"
                        placeholder="Contoh: Potong pola, lebihin margin 2cm..."
                        :rows="3"
                    />
                </div>

                <div class="space-y-4">
                    <div v-if="Object.keys(finishingGroups).length > 0" class="h-full p-5 border shadow-sm bg-base-100 border-base-300 rounded-2xl">
                        <p class="text-[10px] font-black uppercase tracking-wider opacity-40 mb-4 border-b border-base-200 pb-2">Tambahkan Finishing</p>
                        <div class="space-y-4">
                            <div v-for="(group, groupId) in finishingGroups" :key="groupId">
                                <CustomSelect
                                    v-model="selectedFinishings[groupId]"
                                    :options="group.options"
                                    labelKey="nama_pilihan"
                                    valueKey="id"
                                    :label="`Pilihan Finishing ${group.nama}`"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <div class="flex flex-col justify-center h-full p-6 text-center border shadow-sm bg-base-100 border-base-300 rounded-2xl">
                    <h3 class="text-[10px] font-black uppercase tracking-widest opacity-50 mb-3 border-b border-base-200 pb-2">Upload File Desain</h3>
                    <div class="relative flex flex-col items-center justify-center flex-1 p-6 transition-colors border-2 border-dashed cursor-pointer border-base-300 rounded-2xl hover:border-primary hover:bg-primary/5 min-h-40">
                        <svg class="w-10 h-10 mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <span class="text-xs font-bold opacity-70">KLIK / SERET FILE KE SINI</span>
                        <input type="file" multiple @change="handleFileUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                    </div>

                    <div v-if="fileDesain.length > 0" class="mt-4 space-y-2">
                        <div v-for="(file, idx) in fileDesain" :key="idx" class="flex items-center justify-between p-2 text-xs border rounded-lg bg-base-200">
                            {{ file.name }}
                            <button @click="removeFile(idx)" class="text-error">X</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="flex flex-col h-full p-6 border shadow-sm bg-base-100 border-base-300 rounded-2xl">
                    <h3 class="text-[10px] font-black uppercase tracking-widest opacity-50 mb-5 border-b border-base-200 pb-2">Ringkasan Pembayaran</h3>
                    <div class="flex-1 px-2 mb-6 space-y-4 text-sm font-bold">

                        <!-- UI HARGA DASAR DENGAN CORETAN DISKON -->
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="opacity-70">Harga Dasar</span>
                                <div class="text-right">
                                    <span v-if="totalDiskonNominal > 0" class="mr-2 text-xs line-through text-error opacity-70">
                                        {{ formatRupiah(hargaDasarAwal) }}
                                    </span>
                                    <span>{{ formatRupiah(hargaDasar) }}</span>
                                </div>
                            </div>

                            <!-- ALERT NOTIFIKASI DISKON AKTIF (LOOPING ARRAY JSON) -->
                            <div v-if="rincianDiskonArray.length > 0" class="flex flex-col items-end gap-1 mt-1.5">
                                <span v-for="(diskon, idx) in rincianDiskonArray" :key="idx" class="text-[10px] font-black uppercase tracking-widest text-success bg-success/10 px-2.5 py-1 rounded-lg">
                                    ✨ {{ diskon.nama }}: -{{ formatRupiah(diskon.nominal) }}
                                </span>
                            </div>
                        </div>

                        <div v-if="hargaFinishing > 0" class="flex items-start justify-between text-primary">
                            <span class="opacity-70">Biaya Finishing</span>
                            <span>+ {{ formatRupiah(hargaFinishing) }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-base-200/70">
                            <span class="opacity-70">Jumlah Pesanan</span>
                            <span>x {{ qty }}</span>
                        </div>
                        <div v-if="hargaPengerjaan > 0" class="flex items-start justify-between pt-3 border-t text-primary border-base-200/70">
                            <span class="opacity-70 text-warning">Biaya Pengerjaan (SLA)</span>
                            <span class="text-warning">+ {{ formatRupiah(hargaPengerjaan) }} (Flat Fee)</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 mt-auto sm:grid-cols-2">
                        <div class="flex flex-col justify-center p-4 text-center border bg-primary/10 rounded-2xl border-primary/20">
                            <span class="text-[10px] font-black uppercase tracking-widest text-primary/70 block mb-1">Subtotal Akhir</span>
                            <span class="block text-2xl font-black lg:text-3xl text-primary">{{ formatRupiah(grandTotal) }}</span>
                        </div>
                        <button @click="tambahKeKeranjang" :disabled="!selectedSku" class="h-full text-sm font-black shadow-lg btn btn-primary rounded-2xl lg:text-base min-h-15">
                            TAMBAH KERANJANG
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
