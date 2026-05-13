<script setup>
    defineProps(['modelValue', 'label', 'type', 'error', 'placeholder', 'required']);
    defineEmits(['update:modelValue']);
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block mb-1 ml-1 text-xs font-bold text-base-content/70">{{ label }}</label>
        <input
            :type="type || 'text'"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="w-full px-3 py-2 text-sm transition border rounded-lg shadow-sm outline-none bg-base-100 text-base-content border-base-300 focus:ring-2 focus:ring-primary/20 focus:border-primary placeholder:text-base-content/30"
            :placeholder="placeholder"
            :required="required"
        />
        <p v-if="error" class="text-error text-[10px] mt-1 ml-1 font-bold">{{ error }}</p>
    </div>
</template>
