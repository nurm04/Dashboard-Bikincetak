<script setup>
const props = defineProps({
    modelValue: [String, Number],
    label: String,
    placeholder: String,
    error: String,
    required: Boolean,
    rows: { type: Number, default: 3 }
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block mb-1 ml-1 text-xs font-bold text-base-content/70">
            {{ label }}
        </label>

        <div
            class="flex w-full transition border rounded-lg shadow-sm bg-base-100 border-base-300 focus-within:ring-2 focus-within:ring-primary/20 focus-within:border-primary overflow-hidden"
            style="border-style: solid !important; border-width: 1px !important;"
        >
            <textarea
                :value="modelValue"
                :placeholder="placeholder"
                :required="required"
                :rows="rows"
                @input="$emit('update:modelValue', $event.target.value)"
                class="w-full px-4 py-3 text-sm transition bg-transparent border-none outline-none focus:ring-0 text-base-content placeholder:text-base-content/30 resize-none"
                style="border: none !important; box-shadow: none !important; outline: none !important;"
            ></textarea>
        </div>

        <p v-if="error" class="text-error text-[10px] mt-1 ml-1 font-bold">{{ error }}</p>
    </div>
</template>
