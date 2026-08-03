<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomTableForm from '@/Components/CustomTableForm.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomInputNumber from '@/Components/Form/CustomInputNumber.vue';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({ produk: Object });

const form = useForm({
    skus: props.produk.produk_sku?.map(s => ({
        id_sku: s.id_sku,
        nama_sku: s.nama_sku,
        minimum_pesan: s.minimum_pesan,
        harga: s.harga,
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
    const keys = Object.keys(selectedPilihans.value).filter(k => selectedPilihans.value[k].length > 0);

    if (keys.length === 0) return alert('Pilih minimal satu pilihan varian!');

    const combinations = [];

    const combine = (index, currentIds, currentNames) => {
        if (index === keys.length) {
            combinations.push({
                id_sku: null,
                nama_sku: `${props.produk.id_produk}-${props.produk.nama_produk}-${currentNames.join('-')}`,
                minimum_pesan: 1,
                harga: 0,
                pilihan_ids: [...currentIds]
            });
            return;
        }

        const varianId = keys[index];
        selectedPilihans.value[varianId].forEach(pId => {
            const pObj = props.produk.varians
                .find(v => v.id_varian === varianId)
                .pilihan_varian.find(p => p.id_pilihan === pId);

            if (pObj) {
                combine(index + 1, [...currentIds, pId], [...currentNames, pObj.nama_pilihan]);
            }
        });
    };

    combine(0, [], []);

    let addedCount = 0;
    combinations.forEach(newSku => {
        if (!form.skus.find(s => s.nama_sku === newSku.nama_sku)) {
            form.skus.push(newSku);
            addedCount++;
        }
    });

    if (addedCount === 0) {
        alert('Kombinasi pilihan ini sudah ada di tabel!');
    }
};

const generateAllCombinations = () => {
    const keys = Object.keys(selectedPilihans.value).filter(k => selectedPilihans.value[k].length > 0);
    if (keys.length === 0) return alert('Centang pilihan varian dulu!');

    const combinations = [];
    const combine = (index, currentIds, currentNames) => {
        if (index === keys.length) {
            combinations.push({
                id_sku: null,
                nama_sku: `${props.produk.id_produk}-${props.produk.nama_produk}-${currentNames.join('-')}`,
                minimum_pesan: 1,
                harga: 0,
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
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('produk.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Generate SKU {{ produk.nama_produk }}
                    </h2>
                </div>
            </div>
        </template>

        <div class="max-w-6xl px-4 py-8 mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-4">

                <!-- Sidebar Kiri: Opsi Varian -->
                <div class="lg:col-span-1">
                    <!-- REVISI UTAMA: Kotak UI (Border, Shadow, BG) dipindah ke wrapper sticky ini -->
                    <div class="sticky top-24 flex flex-col max-h-[calc(100vh-8rem)] border rounded-2xl shadow-xl bg-base-100 border-base-300 overflow-hidden">

                        <!-- AREA SCROLLABLE: Hanya list varian yang bisa di-scroll -->
                        <div class="flex-1 p-5 md:p-6 overflow-y-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:bg-base-300 [&::-webkit-scrollbar-thumb]:rounded-full">
                            <div class="flex items-center justify-between mb-4 shrink-0">
                                <h3 class="text-xs font-black tracking-widest uppercase opacity-50 text-primary">Opsi Varian</h3>
                            </div>

                            <div v-for="v in produk.varians" :key="v.id_varian" class="p-4 mb-4 border rounded-2xl bg-base-200/50 border-base-300">
                                <div class="flex items-center justify-between pb-2 mb-3 border-b border-base-300 shrink-0">
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
                        </div>

                        <!-- AREA FIXED BAWAH: Tombol menempel terus di dasar sidebar -->
                        <div class="p-5 pt-4 border-t md:p-6 bg-base-100 border-base-300 shrink-0">
                            <div class="space-y-2">
                                <CustomButton @click="addManualSku" class="w-full py-3 rounded-xl text-[10px]" variant="secondary">+ Tambah Manual</CustomButton>
                                <CustomButton @click="generateAllCombinations" class="w-full py-3 rounded-xl text-[10px]" variant="primary">Generate Auto</CustomButton>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Konten Kanan: Tabel Manajemen SKU -->
                <div class="lg:col-span-3">
                    <div class="p-4 border shadow-xl md:p-10 rounded-2xl bg-base-100 border-base-300">
                        <div class="flex flex-col justify-between gap-4 mb-8 sm:flex-row sm:items-center">
                            <div>
                                <h2 class="text-xl font-bold tracking-tighter uppercase">Setting SKU</h2>
                                <p class="text-[10px] font-medium opacity-50 tracking-widest uppercase">{{ produk.nama_produk }}</p>
                            </div>
                            <CustomButton @click="clearTable" variant="error" size="xs" outline class="w-full sm:w-auto">Kosongkan Tabel</CustomButton>
                        </div>

                        <CustomTableForm v-model="form.skus" :headers="['Kombinasi Produk', 'Minimum Pesan', 'Harga']" :can-add="false">
                            <template #row="{ row, index }">
                                <td class="w-2/3 px-4 py-4 min-w-75">
                                    <div class="text-[10px] font-black text-primary wrap-break-word uppercase">{{ row.nama_sku }}</div>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        <span v-for="id in row.pilihan_ids" :key="id" class="px-1.5 py-0.5 bg-base-200 text-[8px] rounded font-bold opacity-50">{{ id }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 min-w-37.5">
                                    <CustomInputNumber v-model="form.skus[index].minimum_pesan" :min="1" />
                                </td>
                                <td class="px-4 py-4 min-w-45">
                                    <CustomInputNumber v-model="form.skus[index].harga" :min="0" prefix="Rp" />
                                </td>
                            </template>
                        </CustomTableForm>

                        <div class="flex flex-col items-center gap-4 pt-8 mt-10 border-t border-base-300 sm:flex-row">
                            <CustomButton
                                @click="submit"
                                variant="primary"
                                class="flex-1 w-full py-4 sm:w-auto rounded-2xl"
                                :disabled="form.processing"
                            >
                                Simpan Perubahan
                            </CustomButton>
                            <CustomButton
                                type="link"
                                :href="route('produk.index')"
                                variant="secondary"
                                class="w-full py-4 sm:w-auto rounded-2xl"
                            >
                                Batal
                            </CustomButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
