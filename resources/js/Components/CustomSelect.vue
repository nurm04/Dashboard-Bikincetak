<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

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
const container = ref(null);

const toggle = () => {
    if (!isOpen.value) {
        window.dispatchEvent(new CustomEvent('close-all-dropdowns'));
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
    if (container.value && !container.value.contains(event.target)) {
        close();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    window.addEventListener('close-all-dropdowns', close);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('close-all-dropdowns', close);
});
</script>

<template>
    <div class="relative inline-block w-full" ref="container">
        <label v-if="label" class="block mb-1 ml-1 text-xs font-bold text-base-content/70 uppercase">
            {{ label }}
        </label>

        <div
            @click.stop="toggle"
            class="flex items-center justify-between w-full px-3 py-2 transition-all duration-300 border rounded-xl cursor-pointer bg-base-100"
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

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="scale-95 translate-y-2 opacity-0"
            enter-to-class="scale-100 translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="scale-100 translate-y-0 opacity-100"
            leave-to-class="scale-95 translate-y-2 opacity-0"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 top-full mt-2 w-full min-w-[200px] z-[999] bg-base-100 border border-base-300 shadow-2xl rounded-2xl overflow-hidden py-2"
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

        <p v-if="error" class="text-error text-[10px] mt-1 ml-1 font-bold">{{ error }}</p>
    </div>
</template>
