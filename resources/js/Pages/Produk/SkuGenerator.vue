<script setup>
import { ref, onMounted } from 'vue';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomTableForm from '@/Components/CustomTableForm.vue';
import { Head, useForm } from '@inertiajs/vue3';
import CustomInputNumber from '@/Components/CustomInputNumber.vue';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';

const props = defineProps({ produk: Object });

const form = useForm({
    skus: props.produk.produk_sku?.map(s => ({
        nama_sku: s.nama_sku,
        harga_jasa: s.harga_jasa,
        pilihan_ids: s.sku_detail_pilihan?.map(d => d.id_pilihan) || []
    })) || []
});

const selectedPilihans = ref({});
const isClearConfirmOpen = ref(false);

onMounted(() => {
    props.produk.varians?.forEach(v => {
        selectedPilihans.value[v.id_varian] = [];
    });
});

const toggleSelectAll = (varian) => {
    const allIds = varian.pilihan_varian.map(p => p.id_pilihan);
    if (selectedPilihans.value[varian.id_varian].length === allIds.length) {
        selectedPilihans.value[varian.id_varian] = [];
    } else {
        selectedPilihans.value[varian.id_varian] = allIds;
    }
};

const addManualSku = () => {
    const ids = [];
    const names = [];
    props.produk.varians.forEach(v => {
        const selectedIds = selectedPilihans.value[v.id_varian] || [];
        selectedIds.forEach(pId => {
            const pObj = v.pilihan_varian.find(p => p.id_pilihan === pId);
            if (pObj) {
                ids.push(pId);
                names.push(pObj.nama_pilihan);
            }
        });
    });

    if (ids.length === 0) return alert('Pilih minimal satu pilihan varian!');
    const namaSku = `${props.produk.id_produk}-${props.produk.nama_produk}-${names.join('-')}`;
    if (form.skus.find(s => s.nama_sku === namaSku)) return alert('Kombinasi ini sudah ada!');

    form.skus.push({ nama_sku: namaSku, harga_jasa: 0, pilihan_ids: ids });
};

const generateAllCombinations = () => {
    const keys = Object.keys(selectedPilihans.value).filter(k => selectedPilihans.value[k].length > 0);
    if (keys.length === 0) return alert('Centang pilihan varian dulu!');

    const combinations = [];
    const combine = (index, currentIds, currentNames) => {
        if (index === keys.length) {
            combinations.push({
                nama_sku: `${props.produk.id_produk}-${props.produk.nama_produk}-${currentNames.join('-')}`,
                harga_jasa: 0,
                pilihan_ids: [...currentIds]
            });
            return;
        }
        const varianId = keys[index];
        selectedPilihans.value[varianId].forEach(pId => {
            const pObj = props.produk.varians.find(v => v.id_varian === varianId).pilihan_varian.find(p => p.id_pilihan === pId);
            combine(index + 1, [...currentIds, pId], [...currentNames, pObj.nama_pilihan]);
        });
    };
    combine(0, [], []);
    combinations.forEach(newSku => {
        if (!form.skus.find(s => s.nama_sku === newSku.nama_sku)) {
            form.skus.push(newSku);
        }
    });
};

const clearTable = () => {
    isClearConfirmOpen.value = true;
};

const handleConfirmClear = () => {
    form.skus = [];
    isClearConfirmOpen.value = false;
};

const submit = () => {
    form.post(route('produk.syncSku', props.produk.id_produk));
};
</script>

<template>
    <CustomAlertConfirm
        :show="isClearConfirmOpen"
        type="error"
        title="Kosongkan Tabel?"
        message="Semua baris SKU yang sudah Lu buat akan dihapus permanen. Lu harus generate ulang kalau mau ngisi lagi."
        confirmText="Ya, Hapus Semua"
        @close="isClearConfirmOpen = false"
        @confirm="handleConfirmClear"
    />
    <Head title="Manajemen SKU" />
    <StafLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-base-content">
                Generate SKU {{ produk.nama_produk }}
            </h2>
        </template>
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-4">

                <div class="lg:col-span-1">
                    <div class="p-6 border rounded-lg shadow-xl bg-base-100 border-base-300">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xs font-black tracking-widest uppercase opacity-50 text-primary">Opsi Varian</h3>
                        </div>

                        <div v-for="v in produk.varians" :key="v.id_varian" class="p-4 mb-6 border rounded-2xl bg-base-200/50 border-base-300">
                            <div class="flex items-center justify-between pb-2 mb-3 border-b border-base-300">
                                <label class="text-[10px] font-black uppercase tracking-tighter opacity-70">{{ v.nama_varian }}</label>
                                <button @click="toggleSelectAll(v)" type="button" class="text-[9px] font-bold text-primary hover:underline">
                                    {{ selectedPilihans[v.id_varian]?.length === v.pilihan_varian.length ? 'Unselect All' : 'Select All' }}
                                </button>
                            </div>
                            <div class="space-y-2">
                                <label v-for="p in v.pilihan_varian" :key="p.id_pilihan" class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" :value="p.id_pilihan" v-model="selectedPilihans[v.id_varian]" class="checkbox checkbox-primary checkbox-xs">
                                    <span class="text-xs font-bold transition-colors group-hover:text-primary">{{ p.nama_pilihan }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <CustomButton @click="addManualSku" class="w-full py-3 text-[10px]" variant="secondary">+ Tambah Manual</CustomButton>
                            <CustomButton @click="generateAllCombinations" class="w-full py-3 text-[10px]" variant="primary">Generate Auto</CustomButton>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="p-8 border rounded-lg shadow-xl bg-base-100 border-base-300">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h2 class="text-xl font-bold tracking-tighter uppercase">Setting Harga SKU</h2>
                                <p class="text-[10px] font-medium opacity-50 tracking-widest uppercase">{{ produk.nama_produk }}</p>
                            </div>
                            <CustomButton @click="clearTable" variant="error" size="xs" outline>Kosongkan Tabel</CustomButton>
                        </div>

                        <CustomTableForm v-model="form.skus" :headers="['Kombinasi Produk', 'Harga Jasa']" :can-add="false">
                            <template #row="{ row, index }">
                                <td class="w-2/3 px-4 py-4">
                                    <div class="text-[10px] font-black text-primary break-words uppercase">{{ row.nama_sku }}</div>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        <span v-for="id in row.pilihan_ids" :key="id" class="px-1.5 py-0.5 bg-base-200 text-[8px] rounded font-bold opacity-50">{{ id }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 min-w-[180px]">
                                    <CustomInputNumber v-model="form.skus[index].harga_jasa" :min="0" prefix="IDR" />
                                </td>
                            </template>
                        </CustomTableForm>

                        <div class="flex gap-4 pt-8 mt-10 border-t border-base-300">
                            <CustomButton @click="submit" class="flex-1 py-4" :disabled="form.processing">Simpan Perubahan</CustomButton>
                            <CustomButton type="link" :href="route('produk.index')" variant="secondary" class="py-4">Batal</CustomButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
