<script setup>
defineProps({
    modelValue: [String, Number],
    label: String,
    name: String, // Penting supaya radio button grupnya sinkron
    options: Array, // Format: [{ label: 'Debit', value: 'debit' }, ...]
    error: String,
});
defineEmits(['update:modelValue']);
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block mb-1 ml-1 text-xs font-bold text-base-content/70">{{ label }}</label>

        <div class="flex flex-wrap gap-3">
            <label v-for="option in options" :key="option.value"
                class="relative flex items-center cursor-pointer group">

                <input type="radio"
                    :name="name"
                    :value="option.value"
                    :checked="modelValue === option.value"
                    @change="$emit('update:modelValue', option.value)"
                    class="hidden peer"
                />

                <div class="px-4 py-2 text-sm font-bold transition-all duration-200 border-2 rounded-lg shadow-sm border-base-300 bg-base-100 text-base-content/70 peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary group-hover:border-primary/40">
                    {{ option.label }}
                </div>

                <div class="absolute w-3 h-3 transition-transform duration-200 scale-0 border-2 rounded-full -top-1 -right-1 bg-primary peer-checked:scale-100 border-base-100"></div>
            </label>
        </div>

        <p v-if="error" class="text-error text-[10px] mt-1 ml-1 font-bold">{{ error }}</p>
    </div>
</template>
