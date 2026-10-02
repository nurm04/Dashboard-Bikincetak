<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    error: String,
    options: { type: Array, default: () => [] },
    labelKey: { type: String, default: 'label' },
    valueKey: { type: String, default: 'value' },
    placeholder: { type: String, default: 'Pilih data...' }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const actionRef = ref(null);
const dropdownRef = ref(null);
const dropdownStyle = ref({});

// TAMBAHAN: Target teleport dinamis (default body)
const teleportTarget = ref('body');

const calculatePosition = () => {
    if (!actionRef.value) return;

    const rect = actionRef.value.getBoundingClientRect();

    dropdownStyle.value = {
        top: `${rect.bottom + 8}px`,
        left: `${rect.left}px`,
        width: `${rect.width}px`
    };
};

const toggle = async () => {
    if (!isOpen.value) {
        window.dispatchEvent(new CustomEvent('close-all-dropdowns'));
        await nextTick();
        calculatePosition();
    }
    isOpen.value = !isOpen.value;
};

const close = () => (isOpen.value = false);

const selectedLabel = computed(() => {
    const selected = props.options.find(opt => String(opt[props.valueKey]) === String(props.modelValue));
    return selected ? selected[props.labelKey] : '';
});

const selectOption = (opt) => {
    emit('update:modelValue', opt[props.valueKey]);
    close();
};

const handleClickOutside = (event) => {
    const isClickInsideButton = actionRef.value && actionRef.value.contains(event.target);
    const isClickInsideDropdown = dropdownRef.value && dropdownRef.value.contains(event.target);

    if (!isClickInsideButton && !isClickInsideDropdown) {
        close();
    }
};

const handleCloseAll = () => {
    close();
};

const handleScroll = (event) => {
    if (!isOpen.value) return;

    const isScrollInsideDropdown = dropdownRef.value && dropdownRef.value.contains(event.target);

    if (!isScrollInsideDropdown) {
        close();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    window.addEventListener('close-all-dropdowns', handleCloseAll);
    window.addEventListener('scroll', handleScroll, true);

    // FIX AJAIB: Deteksi otomatis apakah dipanggil di dalam Modal
    const modalParent = actionRef.value?.closest('dialog, .modal');
    if (modalParent) {
        // Buatkan ID sementara jika modalnya belum punya ID agar Teleport akurat
        if (!modalParent.id) {
            modalParent.id = 'modal-target-' + Math.random().toString(36).substr(2, 9);
        }
        // Ubah target teleport ke dalam modal tersebut
        teleportTarget.value = `#${modalParent.id}`;
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('close-all-dropdowns', handleCloseAll);
    window.removeEventListener('scroll', handleScroll, true);
});
</script>

<template>
    <div class="relative inline-block w-full" ref="actionRef">
        <label v-if="label" class="block mb-1 ml-1 text-xs font-bold text-base-content/70">
            {{ label }}
        </label>

        <div
            @click.stop="toggle"
            class="flex items-center justify-between w-full px-3 py-2 transition-all duration-300 border cursor-pointer rounded-xl bg-base-100"
            :class="isOpen
                ? 'border-primary ring-4 ring-primary/10'
                : 'border-base-300 hover:border-primary/50'"
        >
            <span :class="modelValue ? 'text-base-content' : 'text-base-content/30'" class="text-sm font-bold truncate">
                {{ selectedLabel || placeholder }}
            </span>
            <svg class="w-4 h-4 transition-transform duration-300 opacity-50" :class="{'rotate-180': isOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>

        <!-- UBAH: Teleport sekarang mengarah ke variabel dinamis -->
        <Teleport :to="teleportTarget">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="scale-95 translate-y-2 opacity-0"
                enter-to-class="scale-100 translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="scale-100 translate-y-0 opacity-100"
                leave-to-class="scale-95 translate-y-2 opacity-0"
            >
                <!-- FIX KRUSIAL: z-9999 diubah jadi z-[9999] -->
                <div
                    v-if="isOpen"
                    ref="dropdownRef"
                    class="fixed py-2 overflow-hidden border shadow-2xl z-9999 bg-base-100 border-base-300 rounded-2xl"
                    :style="dropdownStyle"
                >
                    <ul class="py-1 overflow-y-auto max-h-60 scrollbar-hide">
                        <li v-for="opt in options" :key="opt[valueKey]"
                            @click="selectOption(opt)"
                            class="px-4 py-3 text-[11px] font-black uppercase tracking-widest transition-all cursor-pointer border-b border-base-200/50 last:border-0 hover:bg-primary/10 hover:text-primary"
                            :class="String(opt[props.valueKey]) === String(modelValue) ? 'bg-primary/10 text-primary' : 'text-base-content/70'"
                        >
                            {{ opt[labelKey] }}
                        </li>
                    </ul>
                </div>
            </Transition>
        </Teleport>

        <p v-if="error" class="text-error text-[10px] mt-1 ml-1 font-bold">{{ error }}</p>
    </div>
</template>
