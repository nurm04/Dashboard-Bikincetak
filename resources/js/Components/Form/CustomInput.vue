<script setup>
import { ref, computed } from 'vue';

const props = defineProps(['modelValue', 'label', 'type', 'error', 'placeholder', 'required', 'disabled']);
defineEmits(['update:modelValue']);

// State untuk toggle mata
const showPassword = ref(false);

// Logic penentu tipe input
const inputType = computed(() => {
    if (props.type === 'password') {
        return showPassword.value ? 'text' : 'password';
    }
    return props.type || 'text';
});
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block mb-1 ml-1 text-xs font-bold text-base-content/70">{{ label }}</label>
        
        <div class="relative">
            <input
                :type="inputType"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
                class="w-full px-3 py-2 text-sm transition border rounded-lg shadow-sm outline-none bg-base-100 text-base-content border-base-300 focus:ring-2 focus:ring-primary/20 focus:border-primary placeholder:text-base-content/30"
                :class="{ 'pr-10': type === 'password' }"
                :placeholder="placeholder"
                :required="required"
                :disabled="disabled"
            />
            
            <!-- Tombol Mata (Muncul Kalo Props type === "password") -->
            <button 
                v-if="type === 'password'"
                type="button" 
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-3 flex items-center justify-center opacity-40 hover:opacity-100 transition-opacity"
                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
            >
                <!-- Icon Eye Off -->
                <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                    <line x1="2" y1="2" x2="22" y2="22"></line>
                </svg>
                <!-- Icon Eye -->
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </button>
        </div>

        <p v-if="error" class="text-error text-[10px] mt-1 ml-1 font-bold">{{ error }}</p>
    </div>
</template>