<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import { ArrowLeft, GripVertical } from 'lucide-vue-next';
import { alertStore } from '@/Utils/alertStore';

const props = defineProps({
    produks: Array
});

// 1. Kelompokkan produk berdasarkan kategori
const groupedData = [];
props.produks.forEach(p => {
    const catName = p.kategori?.nama_kategori || 'Tanpa Kategori';
    let group = groupedData.find(g => g.categoryName === catName);

    if (!group) {
        group = { categoryName: catName, items: [] };
        groupedData.push(group);
    }

    group.items.push({
        id_produk: p.id_produk,
        nama_produk: p.nama_produk,
        kategori: catName,
        urutan: p.urutan,
        is_active: p.is_active == 1 || p.is_active === true,
    });
});

// 2. Kalkulasi ulang urutan global awal untuk merapikan data yang acak
let initialUrutan = 1;
groupedData.forEach(group => {
    group.items.forEach(item => {
        item.urutan = initialUrutan++;
    });
});

// Map ke dalam form state
const form = useForm({
    groups: groupedData
});

// ==========================================
// LOGIC DRAG & DROP TERBATAS DALAM KATEGORI
// ==========================================
const draggingInfo = ref({ groupIndex: null, itemIndex: null });

const onDragStart = (groupIndex, itemIndex) => {
    draggingInfo.value = { groupIndex, itemIndex };
};

const onDragEnter = (groupIndex, itemIndex) => {
    const dGroup = draggingInfo.value.groupIndex;
    const dItem = draggingInfo.value.itemIndex;

    // CEGAH DRAG LINTAS KATEGORI: Tolak jika index grup berbeda
    if (dGroup !== groupIndex) return;
    if (dItem === itemIndex || dItem === null) return;

    // Tukar posisi elemen di dalam array kategori yang sama
    const draggedItem = form.groups[dGroup].items[dItem];
    form.groups[dGroup].items.splice(dItem, 1);
    form.groups[dGroup].items.splice(itemIndex, 0, draggedItem);

    draggingInfo.value.itemIndex = itemIndex;

    // Hitung ulang nomor urutan secara global
    recalculateUrutan();
};

const onDragEnd = () => {
    draggingInfo.value = { groupIndex: null, itemIndex: null };
};

const recalculateUrutan = () => {
    let counter = 1;
    form.groups.forEach(group => {
        group.items.forEach(item => {
            item.urutan = counter++;
        });
    });
};

// ==========================================
// SUBMIT BULK UPDATE
// ==========================================
const submit = () => {
    form.transform((data) => {
        // Flatten (lebur) kembali data group menjadi array flat untuk backend Laravel
        const flatProduks = [];
        data.groups.forEach(group => {
            group.items.forEach(item => {
                flatProduks.push(item);
            });
        });
        return {
            produks: flatProduks
        };
    }).post(route('tampilan-web.produk.sync'), {
        onSuccess: () => alertStore.show('Urutan & Tampilan Produk berhasil disimpan!', 'success'),
    });
};
</script>

<template>
    <Head title="Atur Tampilan Produk" />
    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('tampilan-web.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h2 class="text-xl font-semibold leading-tight text-base-content">
                            Atur Urutan Produk
                        </h2>
                        <p class="mt-1 text-sm text-base-content/60">Tarik baris (drag & drop) untuk mengurutkan posisi tampil katalog produk di Web.</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-5xl px-4 py-8 mx-auto sm:px-6 lg:px-8">
            <div class="p-6 border shadow-xl sm:p-10 rounded-2xl bg-base-100 border-base-300">
                <form @submit.prevent="submit" class="space-y-6">

                    <div class="space-y-3">
                        <div class="flex items-center justify-between ml-1">
                            <h2 class="text-xs font-black text-base-content/50 uppercase tracking-[0.2em]">Daftar Katalog Produk Berdasarkan Kategori</h2>
                        </div>

                        <div class="overflow-hidden border border-base-300 rounded-3xl bg-base-200/30">
                            <table class="w-full text-left border-collapse rounded-3xl">
                                <thead class="bg-base-200/50">
                                    <tr>
                                        <th class="w-10 px-3 py-2 text-[10px] font-black text-center text-base-content/40 uppercase tracking-widest whitespace-nowrap">Urutan</th>
                                        <th class="px-3 py-2 text-[10px] font-black text-base-content/40 uppercase tracking-widest whitespace-nowrap">Nama & ID Produk</th>
                                        <th class="w-48 px-3 py-2 text-[10px] font-black text-base-content/40 uppercase tracking-widest whitespace-nowrap">Kategori</th>
                                        <th class="w-24 px-3 py-2 text-[10px] font-black text-center text-base-content/40 uppercase tracking-widest whitespace-nowrap">Tampil di Katalog</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-base-300">
                                    <!-- LOOPING GRUP KATEGORI -->
                                    <template v-for="(group, gIndex) in form.groups" :key="group.categoryName">

                                        <!-- Header Pembatas Kategori -->
                                        <tr class="bg-base-200/80">
                                            <td colspan="4" class="px-4 py-3 text-xs font-black tracking-widest uppercase shadow-sm text-primary border-y border-base-300">
                                                Kategori: {{ group.categoryName }}
                                            </td>
                                        </tr>

                                        <!-- LOOPING ITEM PRODUK DALAM KATEGORI -->
                                        <tr
                                            v-for="(row, iIndex) in group.items"
                                            :key="row.id_produk"
                                            class="transition-colors bg-base-100 group hover:bg-base-200/50 cursor-grab active:cursor-grabbing"
                                            :class="{'opacity-50 scale-[0.99] bg-base-200': draggingInfo.groupIndex === gIndex && draggingInfo.itemIndex === iIndex}"
                                            draggable="true"
                                            @dragstart="onDragStart(gIndex, iIndex)"
                                            @dragenter.prevent="onDragEnter(gIndex, iIndex)"
                                            @dragover.prevent
                                            @dragend="onDragEnd"
                                        >
                                            <!-- Drag Handle & Nomer Urut -->
                                            <td class="px-3 py-3 text-center border-r border-base-300/30">
                                                <div class="flex items-center justify-center gap-1 opacity-40 group-hover:opacity-100 text-base-content">
                                                    <GripVertical class="w-4 h-4 shrink-0" />
                                                    <span class="w-4 text-xs font-black">{{ row.urutan }}</span>
                                                </div>
                                            </td>

                                            <!-- Nama & ID Produk -->
                                            <td class="px-4 py-3 align-middle">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-bold text-base-content">{{ row.nama_produk }}</span>
                                                    <span class="text-[10px] font-bold text-primary opacity-80 uppercase tracking-wider">{{ row.id_produk }}</span>
                                                </div>
                                            </td>

                                            <!-- Kategori Produk (Badge) -->
                                            <td class="px-4 py-3 align-middle border-l border-base-300/30">
                                                <span class="text-[11px] font-bold badge badge-ghost badge-sm text-base-content/60">
                                                    {{ row.kategori }}
                                                </span>
                                            </td>

                                            <!-- Toggle Status (Hide/Show) -->
                                            <td class="px-4 py-3 text-center align-middle border-l border-base-300/30">
                                                <input
                                                    type="checkbox"
                                                    v-model="form.groups[gIndex].items[iIndex].is_active"
                                                    class="toggle toggle-sm toggle-success"
                                                />
                                            </td>
                                        </tr>
                                    </template>

                                    <!-- State Kosong -->
                                    <tr v-if="form.groups.length === 0">
                                        <td colspan="4" class="py-10 text-sm font-bold tracking-widest text-center uppercase text-base-content/40">
                                            Belum ada produk di database
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex flex-col items-center gap-4 pt-6 mt-6 border-t border-base-300 sm:flex-row">
                        <CustomButton
                            type="submit"
                            variant="primary"
                            class="flex-1 w-full py-4 sm:w-auto rounded-2xl"
                            :disabled="form.processing"
                        >
                            Simpan Urutan Produk
                        </CustomButton>

                        <CustomButton
                            type="link"
                            :href="route('tampilan-web.index')"
                            variant="secondary"
                            class="w-full py-4 sm:w-auto rounded-2xl"
                        >
                            Batal
                        </CustomButton>
                    </div>
                </form>
            </div>
        </div>
    </StafLayout>
</template>

<style scoped>
.cursor-grab {
    cursor: -webkit-grab;
    cursor: grab;
}
.active\:cursor-grabbing:active {
    cursor: -webkit-grabbing;
    cursor: grabbing;
}
</style>
