<script setup>
const props = defineProps({
    modelValue: [Number, String],
    min: { type: Number, default: 0 },
    label: String,
    prefix: String,
    placeholder: String,
    error: String,
    required: Boolean
});

const emit = defineEmits(['update:modelValue']);

const handleInput = (e) => {
    let val = e.target.value;
    if (val === '') { emit('update:modelValue', ''); return; }
    let numericVal = parseFloat(val);
    if (numericVal < props.min) numericVal = props.min;
    emit('update:modelValue', numericVal);
};

const handleBlur = (e) => {
    if (e.target.value === '' || parseFloat(e.target.value) < props.min) {
        emit('update:modelValue', props.min);
    }
};
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block mb-1 ml-1 text-xs font-bold text-base-content/70">
            {{ label }}
        </label>

        <div
            class="flex items-center w-full transition border rounded-lg shadow-sm bg-base-100 border-base-300 focus-within:ring-2 focus-within:ring-primary/20 focus-within:border-primary overflow-hidden"
            style="border-style: solid !important; border-width: 1px !important;"
        >
            <span v-if="prefix" class="pl-3 pr-1 text-sm font-bold opacity-40 select-none shrink-0">
                {{ prefix }}
            </span>

            <input
                type="number"
                :value="modelValue"
                :min="min"
                :placeholder="placeholder"
                :required="required"
                @input="handleInput"
                @blur="handleBlur"
                class="w-full py-2 text-sm transition bg-transparent border-none outline-none focus:ring-0 text-base-content placeholder:text-base-content/30"
                :class="prefix ? 'pl-1 pr-3' : 'px-4'"
                style="border: none !important; box-shadow: none !important; outline: none !important; padding-left: 12px !important;"
            />
        </div>

        <p v-if="error" class="text-error text-[10px] mt-1 ml-1 font-bold">{{ error }}</p>
    </div>
</template>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type=number] {
    -moz-appearance: textfield;
}
</style>
