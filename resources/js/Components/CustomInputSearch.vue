<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    },
    placeholder: {
        type: String,
        default: 'Cari data...'
    }
});

const emit = defineEmits(['update:modelValue']);
const inputRef = ref(null);

const clearInput = () => {
    emit('update:modelValue', '');
    inputRef.value?.focus();
};
</script>

<template>
    <div class="relative group">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-4 h-4 transition-colors text-base-content/30 group-focus-within:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </span>

        <input
            ref="inputRef"
            type="text"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="w-full py-2 pl-10 pr-10 text-sm font-bold transition border shadow-sm outline-none rounded-xl bg-base-100 text-base-content border-base-300 focus:ring-2 focus:ring-primary/20 focus:border-primary placeholder:text-base-content/40"
            :placeholder="placeholder"
        />

        <button
            v-if="modelValue"
            @click="clearInput"
            type="button"
            class="absolute inset-y-0 right-0 flex items-center pr-3 transition-colors text-base-content/30 hover:text-error"
            title="Bersihkan pencarian"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</template>
