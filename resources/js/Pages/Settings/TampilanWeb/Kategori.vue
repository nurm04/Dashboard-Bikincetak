<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import { ArrowLeft, GripVertical, Check } from 'lucide-vue-next';
import { alertStore } from '@/Utils/alertStore';
import { 
    Printer, Book, BookOpen, FileText, Image as ImageIcon, Monitor, 
    Shirt, ShoppingBag, Package, Box, PenTool, Scissors, Camera, 
    Layers, Grid, Tag, Gift, Briefcase, Calendar, Megaphone, Sticker, Palette, Folder 
} from 'lucide-vue-next';
const availableIcons = {
    'Printer': Printer, 'Book': Book, 'BookOpen': BookOpen, 'FileText': FileText, 'Image': ImageIcon, 'Monitor': Monitor,
    'Shirt': Shirt, 'ShoppingBag': ShoppingBag, 'Package': Package, 'Box': Box, 'PenTool': PenTool, 'Scissors': Scissors, 'Camera': Camera,
    'Layers': Layers, 'Grid': Grid, 'Tag': Tag, 'Gift': Gift, 'Briefcase': Briefcase, 'Calendar': Calendar, 'Megaphone': Megaphone, 'Sticker': Sticker, 'Palette': Palette, 'Folder': Folder
};

const props = defineProps({
    kategoris: Array
});

const form = useForm({
    kategoris: props.kategoris.map((k, index) => ({
        id_kategori: k.id_kategori,
        nama_kategori: k.nama_kategori,
        urutan: k.urutan === 0 ? index + 1 : k.urutan,
        is_active: k.is_active == 1 || k.is_active === true,
        icon: k.icon || ''
    }))
});

// ==========================================
// LOGIC DRAG & DROP NATIVE HTML5
// ==========================================
const draggingIndex = ref(null);

const onDragStart = (index) => {
    draggingIndex.value = index;
};

const onDragEnter = (index) => {
    if (draggingIndex.value === index || draggingIndex.value === null) return;
    
    const draggedItem = form.kategoris[draggingIndex.value];
    form.kategoris.splice(draggingIndex.value, 1);
    form.kategoris.splice(index, 0, draggedItem);
    
    draggingIndex.value = index;

    form.kategoris.forEach((cat, idx) => {
        cat.urutan = idx + 1;
    });
};

const onDragEnd = () => {
    draggingIndex.value = null;
};

// ==========================================
// LOGIC ICON PICKER MODAL
// ==========================================
const isIconModalOpen = ref(false);
const activeIndexForIcon = ref(null);

const openIconPicker = (index) => {
    activeIndexForIcon.value = index;
    isIconModalOpen.value = true;
};

const selectIcon = (iconName) => {
    if (activeIndexForIcon.value !== null) {
        form.kategoris[activeIndexForIcon.value].icon = iconName;
    }
    isIconModalOpen.value = false;
    activeIndexForIcon.value = null;
};

const closeIconModal = () => {
    isIconModalOpen.value = false;
    activeIndexForIcon.value = null;
};

// ==========================================
// SUBMIT BULK UPDATE
// ==========================================
const submit = () => {
    form.post(route('tampilan-web.kategori.sync'), {
        onSuccess: () => alertStore.show('Urutan & Tampilan Kategori berhasil disimpan!', 'success'),
    });
};
</script>

<template>
    <Head title="Atur Tampilan Kategori" />
    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('tampilan-web.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h2 class="text-xl font-semibold leading-tight text-base-content">
                            Atur Tampilan Kategori
                        </h2>
                        <p class="mt-1 text-sm text-base-content/60">Tarik baris (drag & drop) untuk mengurutkan posisi kategori di Web.</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-4xl px-4 py-8 mx-auto sm:px-6 lg:px-8">
            <div class="p-6 border shadow-xl sm:p-10 rounded-2xl bg-base-100 border-base-300">
                <form @submit.prevent="submit" class="space-y-6">

                    <div class="space-y-3">
                        <div class="flex items-center justify-between ml-1">
                            <h2 class="text-xs font-black text-base-content/50 uppercase tracking-[0.2em]">Daftar Kategori Web</h2>
                        </div>

                        <div class="overflow-x-auto border border-base-300 rounded-3xl bg-base-200/30">
                            <table class="w-full text-left border-collapse rounded-3xl">
                                <thead class="bg-base-200/50">
                                    <tr>
                                        <th class="w-10 px-3 py-2 text-[10px] font-black text-center text-base-content/40 uppercase tracking-widest whitespace-nowrap first:rounded-tl-3xl">Urutan</th>
                                        <th class="px-3 py-2 text-[10px] font-black text-base-content/40 uppercase tracking-widest whitespace-nowrap">Nama Kategori</th>
                                        <th class="w-48 px-3 py-2 text-[10px] font-black text-center text-base-content/40 uppercase tracking-widest whitespace-nowrap">Icon Kategori</th>
                                        <th class="w-24 px-3 py-2 text-[10px] font-black text-center text-base-content/40 uppercase tracking-widest whitespace-nowrap last:rounded-tr-3xl">Tampil di Web</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-base-300">
                                    
                                    <tr 
                                        v-for="(row, index) in form.kategoris" 
                                        :key="row.id_kategori" 
                                        class="transition-colors bg-base-100 group hover:bg-base-200/50 cursor-grab active:cursor-grabbing"
                                        :class="{'opacity-50 scale-[0.99] bg-base-200': draggingIndex === index}"
                                        draggable="true"
                                        @dragstart="onDragStart(index)"
                                        @dragenter.prevent="onDragEnter(index)"
                                        @dragover.prevent
                                        @dragend="onDragEnd"
                                    >
                                        <!-- Drag Handle & Nomer -->
                                        <td class="px-3 py-3 text-center border-r border-base-300/30">
                                            <div class="flex items-center justify-center gap-1 opacity-40 group-hover:opacity-100 text-base-content">
                                                <GripVertical class="w-4 h-4 shrink-0" />
                                                <span class="w-4 text-xs font-black">{{ row.urutan }}</span>
                                            </div>
                                        </td>
                                        
                                        <!-- Nama Kategori -->
                                        <td class="px-4 py-3 align-middle">
                                            <span class="text-sm font-bold text-base-content">{{ row.nama_kategori }}</span>
                                        </td>

                                        <!-- 👇 TOMBOL PILIH ICON 👇 -->
                                        <td class="px-4 py-3 align-middle border-l border-base-300/30">
                                            <button 
                                                type="button" 
                                                @click="openIconPicker(index)" 
                                                class="flex items-center justify-between w-full gap-2 px-3 py-2 transition-colors border shadow-sm rounded-xl border-base-300 bg-base-100 hover:bg-base-200 hover:border-primary/50"
                                            >
                                                <div class="flex items-center gap-2">
                                                    <!-- Render dinamis komponen icon -->
                                                    <component 
                                                        v-if="row.icon && availableIcons[row.icon]" 
                                                        :is="availableIcons[row.icon]" 
                                                        class="w-4 h-4 text-primary shrink-0" 
                                                    />
                                                    <div v-else class="flex items-center justify-center w-4 h-4 border border-dashed rounded-md bg-base-200 border-base-300 text-[9px] font-black text-base-content/40">?</div>
                                                    
                                                    <span class="text-xs font-bold truncate text-base-content/80">{{ row.icon || 'Pilih Icon' }}</span>
                                                </div>
                                            </button>
                                        </td>

                                        <!-- Toggle Status (Hide/Show) -->
                                        <td class="px-4 py-3 text-center align-middle border-l border-base-300/30">
                                            <input 
                                                type="checkbox" 
                                                v-model="form.kategoris[index].is_active" 
                                                class="toggle toggle-sm toggle-success"
                                            />
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
                            Simpan Perubahan Tampilan
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

        <!-- 👇 MODAL ICON PICKER 👇 -->
        <dialog class="modal" :class="{'modal-open': isIconModalOpen}">
            <div class="max-w-xl p-0 modal-box rounded-2xl z-100">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b sm:p-5 border-base-200 bg-base-50">
                    <div>
                        <h3 class="text-base font-bold text-base-content">Pilih Icon Kategori</h3>
                        <p class="text-[11px] sm:text-sm font-medium text-base-content/50 mt-0.5">Icon ini akan muncul di navigasi Front-end E-commerce</p>
                    </div>
                    <button type="button" @click="closeIconModal" class="btn btn-sm btn-circle btn-ghost text-base-content/40 hover:text-error">✕</button>
                </div>
                
                <!-- Body (Grid Icons) -->
                <div class="p-4 sm:p-5 max-h-[60vh] overflow-y-auto">
                    <div class="grid grid-cols-4 gap-3 sm:grid-cols-6 lg:grid-cols-8">
                        
                        <!-- Opsi Tanpa Icon -->
                        <button 
                            type="button"
                            @click="selectIcon('')"
                            class="flex flex-col items-center justify-center p-3 transition-all border border-dashed rounded-xl hover:bg-base-200 border-base-300"
                            :class="form.kategoris[activeIndexForIcon]?.icon === '' ? 'ring-2 ring-primary bg-primary/5 border-solid' : 'bg-base-100'"
                        >
                            <span class="text-[10px] font-black text-base-content/50">Kosong</span>
                        </button>

                        <!-- Looping Opsi Icon -->
                        <button 
                            v-for="(iconComponent, iconName) in availableIcons" 
                            :key="iconName"
                            type="button"
                            @click="selectIcon(iconName)"
                            class="relative flex flex-col items-center justify-center p-3 transition-all border rounded-xl hover:border-primary/50 hover:bg-primary/5 group border-base-200"
                            :class="form.kategoris[activeIndexForIcon]?.icon === iconName ? 'ring-2 ring-primary bg-primary/10 border-primary' : 'bg-base-100'"
                            :title="iconName"
                        >
                            <component :is="iconComponent" class="w-6 h-6 transition-colors text-base-content/70 group-hover:text-primary" :class="{'text-primary': form.kategoris[activeIndexForIcon]?.icon === iconName}" />
                            
                            <!-- Checkmark buat yang lagi kepilih -->
                            <div v-if="form.kategoris[activeIndexForIcon]?.icon === iconName" class="absolute flex items-center justify-center w-4 h-4 text-white rounded-full shadow-sm -top-1 -right-1 bg-primary">
                                <Check class="w-3 h-3" />
                            </div>
                        </button>

                    </div>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop bg-base-content/50 z-90"><button @click="closeIconModal">close</button></form>
        </dialog>

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