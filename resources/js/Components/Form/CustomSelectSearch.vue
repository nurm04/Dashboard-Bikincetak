<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    error: String,
    options: { type: Array, default: () => [] },
    labelKey: { type: String, default: 'label' },
    valueKey: { type: String, default: 'value' },
    placeholder: { type: String, default: 'Pilih data...' },
    addOption: { type: Boolean, default: true },
    disabled: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue', 'onCreate']);

const isOpen = ref(false);
const search = ref('');
const container = ref(null);
const dropdownRef = ref(null);
const dropdownStyle = ref({});
const teleportTarget = ref('body');

const filteredOptions = computed(() => {
    return props.options.filter(opt =>
        String(opt[props.labelKey] || '').toLowerCase().includes(search.value.toLowerCase())
    );
});

const selectedLabel = computed(() => {
    const selected = props.options.find(opt => opt[props.valueKey] === props.modelValue);
    return selected ? selected[props.labelKey] : '';
});

const selectOption = (opt) => {
    emit('update:modelValue', opt[props.valueKey]);
    isOpen.value = false;
    search.value = '';
};

const handleCreate = () => {
    emit('onCreate', search.value);
    isOpen.value = false;
};

// ==========================================
// LOGIC POSISI & TINGGI DINAMIS
// ==========================================
const calculatePosition = () => {
    if (!container.value || !isOpen.value) return;

    const rect = container.value.getBoundingClientRect();
    const spaceBelow = window.innerHeight - rect.bottom;
    const spaceAbove = rect.top;
    const dropdownHeight = 320; // Estimasi tinggi max

    let isUpwards = false;
    // Buka ke atas jika di bawah sempit DAN di atas lebih lega
    if (spaceBelow < dropdownHeight && spaceAbove > spaceBelow) {
        isUpwards = true;
    }

    if (isUpwards) {
        dropdownStyle.value = {
            position: 'fixed',
            bottom: `${window.innerHeight - rect.top + 8}px`,
            left: `${rect.left}px`,
            width: `${rect.width}px`,
            // Batasi tinggi maksimal agar tidak tembus layar atas
            maxHeight: `${Math.max(spaceAbove - 20, 150)}px`,
            zIndex: 999999
        };
    } else {
        dropdownStyle.value = {
            position: 'fixed',
            top: `${rect.bottom + 8}px`,
            left: `${rect.left}px`,
            width: `${rect.width}px`,
            // Batasi tinggi maksimal agar tidak tembus layar bawah
            maxHeight: `${Math.max(spaceBelow - 20, 150)}px`,
            zIndex: 999999
        };
    }
};

watch(isOpen, (val) => {
    if (val) {
        nextTick(() => {
            calculatePosition();
            window.addEventListener('scroll', calculatePosition, true);
            window.addEventListener('resize', calculatePosition);
        });
    } else {
        window.removeEventListener('scroll', calculatePosition, true);
        window.removeEventListener('resize', calculatePosition);
    }
});

const handleClickOutside = (event) => {
    const clickedInContainer = container.value && container.value.contains(event.target);
    const clickedInDropdown = dropdownRef.value && dropdownRef.value.contains(event.target);

    if (!clickedInContainer && !clickedInDropdown) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    const modalParent = container.value?.closest('dialog, .modal');
    if (modalParent) {
        if (!modalParent.id) {
            modalParent.id = 'modal-target-' + Math.random().toString(36).substr(2, 9);
        }
        teleportTarget.value = `#${modalParent.id}`;
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('scroll', calculatePosition, true);
    window.removeEventListener('resize', calculatePosition);
});
</script>

<template>
    <div class="relative w-full" ref="container">
        <label v-if="label" class="block mb-1 ml-1 text-xs font-bold text-base-content/70">
            {{ label }}
        </label>

        <div
            @click.stop="!disabled && (isOpen = !isOpen)"
            :class="disabled ? 'opacity-50 cursor-not-allowed bg-base-200' : 'cursor-pointer bg-base-100 focus-within:ring-4 focus-within:ring-primary/10 focus-within:border-primary'"
            class="flex items-center justify-between w-full px-3 py-2 transition border rounded-lg shadow-sm border-base-300"
        >
            <span :class="modelValue ? 'text-base-content' : 'text-base-content/30'" class="text-sm font-bold truncate">
                {{ selectedLabel || placeholder }}
            </span>
            <svg class="w-4 h-4 transition-transform opacity-50" :class="{'rotate-180': isOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>

        <Teleport :to="teleportTarget">
            <!-- 👇 Ditambahkan: flex & flex-col agar layout bisa menyesuaikan maxHeight dinamis -->
            <div
                v-if="isOpen"
                ref="dropdownRef"
                :style="dropdownStyle"
                class="flex flex-col overflow-hidden duration-200 border rounded-lg shadow-2xl bg-base-100 border-base-300 animate-in fade-in zoom-in"
            >
                <!-- KEPALA (Search) - Ditambah shrink-0 agar tidak menyusut -->
                <div class="p-2 border-b border-base-200 shrink-0">
                    <input
                        v-model="search"
                        type="text"
                        class="w-full px-3 py-2 text-xs border-none rounded-lg outline-none bg-base-200 focus:ring-2 focus:ring-primary/30 text-base-content"
                        :placeholder="'Cari ' + (label || '') + '...'"
                        autofocus
                    />
                </div>

                <!-- BADAN (List Option) - Ditambah flex-1 dan hapus max-h-60 agar fleksibel -->
                <ul class="flex-1 py-1 overflow-y-auto scrollbar-hide">
                    <li v-for="opt in filteredOptions" :key="opt[valueKey]"
                        @click="selectOption(opt)"
                        class="px-4 py-2 text-sm font-bold transition-colors cursor-pointer text-base-content/70 hover:bg-primary hover:text-white"
                    >
                        {{ opt[labelKey] }}
                    </li>

                    <li v-if="filteredOptions.length === 0" class="px-4 py-3 text-xs italic text-center text-base-content/40">
                        Data tidak ditemukan
                    </li>
                </ul>

                <!-- KAKI (Tombol Tambah) - Ditambah shrink-0 agar tidak menyusut -->
                <div v-if="addOption" @click="handleCreate" class="p-2 border-t cursor-pointer bg-base-200 border-base-300 shrink-0">
                    <button type="button" class="flex items-center justify-center w-full gap-2 py-2 text-xs font-black transition-all rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        TAMBAH {{ label?.toUpperCase() || 'DATA' }} BARU
                    </button>
                </div>
            </div>
        </Teleport>

        <p v-if="error" class="text-error text-[10px] mt-1 font-bold">{{ error }}</p>
    </div>
</template>
