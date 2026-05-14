<script setup>
// Props tetap sama supaya gak ngerusak logic v-model di Form
defineProps({
    modelValue: [Boolean, Number],
    label: String,
    color: { type: String, default: 'primary' }
});

const emit = defineEmits(['update:modelValue']);

const toggle = () => {
    emit('update:modelValue', !modelValue);
};
</script>

<template>
    <div class="flex items-center justify-center">
        <button
            type="button"
            @click.stop="$emit('update:modelValue', !modelValue)"
            class="relative flex items-center justify-center w-6 h-6 transition-all duration-300 border-2 rounded-lg group overflow-hidden"
            :class="[
                modelValue
                    ? (color === 'primary' ? 'bg-primary border-primary shadow-lg shadow-primary/20' :
                       color === 'success' ? 'bg-success border-success shadow-lg shadow-success/20' :
                       color === 'warning' ? 'bg-warning border-warning shadow-lg shadow-warning/20' :
                       'bg-error border-error shadow-lg shadow-error/20')
                    : 'bg-base-300/30 border-base-300 hover:border-primary/50'
            ]"
        >
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="scale-0 opacity-0"
                enter-to-class="scale-100 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="scale-100 opacity-100"
                leave-to-class="scale-0 opacity-0"
            >
                <svg
                    v-if="modelValue"
                    class="w-4 h-4 text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="4"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </Transition>

            <div class="absolute inset-0 transition-opacity opacity-0 bg-white/10 group-hover:opacity-100"></div>
        </button>

        <span v-if="label"
              @click="$emit('update:modelValue', !modelValue)"
              class="ml-3 text-[11px] font-black uppercase tracking-widest cursor-pointer select-none transition-colors"
              :class="modelValue ? 'text-base-content' : 'text-base-content/40 hover:text-base-content'"
        >
            {{ label }}
        </span>
    </div>
</template>
