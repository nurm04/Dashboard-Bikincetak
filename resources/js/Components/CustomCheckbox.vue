<script setup>
    defineProps(['modelValue', 'label']);
    defineEmits(['update:modelValue']);
</script>

<template>
    <label class="inline-flex items-center cursor-pointer group w-fit">
        <input
            type="checkbox"
            :checked="modelValue"
            @change="$emit('update:modelValue', $event.target.checked)"
            class="transition-all border-2 rounded shadow-sm cursor-pointer checkbox checkbox-primary checkbox-sm border-base-300 group-hover:border-primary"
        >
        <span class="ml-2 text-sm font-bold transition-colors text-base-content/70 group-hover:text-primary">
            {{ label }}
        </span>
    </label>
</template>
