<script setup>
const props = defineProps({
    modelValue: [Number, String],
    min: {
        type: Number,
        default: 0
    },
    label: String,
    prefix: String,
    placeholder: String
});

const emit = defineEmits(['update:modelValue']);

const handleInput = (e) => {
    let val = e.target.value;

    // Jika kosong, biarkan dulu agar user bisa hapus angka
    if (val === '') {
        emit('update:modelValue', '');
        return;
    }

    // Ubah ke numeric
    let numericVal = parseFloat(val);

    // Proteksi: Jika di bawah minimum, paksa ke minimum
    if (numericVal < props.min) {
        numericVal = props.min;
    }

    emit('update:modelValue', numericVal);
};

// Proteksi tambahan saat user selesai input (fokus pindah)
const handleBlur = (e) => {
    if (e.target.value === '' || parseFloat(e.target.value) < props.min) {
        emit('update:modelValue', props.min);
    }
};
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block mb-1 text-[10px] font-black uppercase tracking-widest opacity-50 ml-1">
            {{ label }}
        </label>
        <div class="flex items-center gap-2 px-3 transition-all border rounded-xl bg-base-200 border-base-300 focus-within:border-primary focus-within:bg-base-100">
            <span v-if="prefix" class="text-[10px] font-black opacity-30 select-none">
                {{ prefix }}
            </span>
            <input
                type="number"
                :value="modelValue"
                :min="min"
                :placeholder="placeholder"
                @input="handleInput"
                @blur="handleBlur"
                class="w-full py-3 text-sm font-bold bg-transparent border-none focus:ring-0"
            />
        </div>
    </div>
</template>
