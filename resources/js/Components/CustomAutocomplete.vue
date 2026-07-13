<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    error: String,
    placeholder: { type: String, default: 'Ketik nama kota/kecamatan...' },
    labelKey: { type: String, default: 'name' },
    valueKey: { type: String, default: 'id' },
    initialLabel: { type: String, default: '' }
});

const emit = defineEmits(['update:modelValue', 'select']);

const searchQuery = ref(props.initialLabel);
const results = ref([]);
const isOpen = ref(false);
const isLoading = ref(false);
const container = ref(null);
let timeout = null;

const handleInput = (e) => {
    searchQuery.value = e.target.value;


    if (searchQuery.value === '') {
        emit('update:modelValue', '');
    }


    if (searchQuery.value.length < 3) {
        results.value = [];
        isOpen.value = false;
        return;
    }

    isLoading.value = true;
    isOpen.value = true;


    clearTimeout(timeout);
    timeout = setTimeout(async () => {
        try {

            const response = await fetch(`/api/shipping/destination?search=${searchQuery.value}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            console.log("CEK BALASAN DESTINASI:", data);
            results.value = data.data || data;
        } catch (error) {
            console.error('Error fetching destination', error);
        } finally {
            isLoading.value = false;
        }
    }, 500);
};

const selectOption = (opt) => {
    searchQuery.value = opt[props.labelKey];
    emit('update:modelValue', opt[props.valueKey]);
    emit('select', opt);
    isOpen.value = false;
};

const handleClickOutside = (event) => {
    if (container.value && !container.value.contains(event.target)) {
        isOpen.value = false;
    }
};

watch(() => props.initialLabel, (newVal) => {
    if (newVal) searchQuery.value = newVal;
});

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div class="relative inline-block w-full" ref="container">
        <label v-if="label" class="block mb-1 ml-1 text-xs font-bold text-base-content/70 uppercase">
            {{ label }} <span class="text-error">*</span>
        </label>

        <div class="relative">
            <input
                type="text"
                :value="searchQuery"
                @input="handleInput"
                :placeholder="placeholder"
                class="w-full px-4 py-2 text-sm transition-all duration-300 border rounded-xl bg-base-100 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary border-base-300"
            />

            <div v-if="isLoading" class="absolute right-3 top-2.5">
                <span class="loading loading-spinner loading-xs text-primary"></span>
            </div>
        </div>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="scale-95 translate-y-2 opacity-0"
            enter-to-class="scale-100 translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="scale-100 translate-y-0 opacity-100"
            leave-to-class="scale-95 translate-y-2 opacity-0"
        >
            <div v-if="isOpen && (results.length > 0 || !isLoading)"
                 class="absolute right-0 top-full mt-2 w-full min-w-50 z-999 bg-base-100 border border-base-300 shadow-2xl rounded-2xl overflow-hidden py-2">

                <ul v-if="results.length > 0" class="py-1 overflow-y-auto max-h-60 scrollbar-hide">
                    <li v-for="opt in results" :key="opt[valueKey]"
                        @click="selectOption(opt)"
                        class="px-4 py-3 text-xs font-bold transition-all cursor-pointer border-b border-base-200/50 last:border-0 hover:bg-primary/10 hover:text-primary text-base-content/70"
                    >
                        {{ opt[labelKey] }}
                    </li>
                </ul>

                <div v-else class="px-4 py-3 text-xs text-center text-base-content/50">
                    Daerah tidak ditemukan.
                </div>
            </div>
        </Transition>

        <p v-if="error" class="text-error text-[10px] mt-1 ml-1 font-bold">{{ error }}</p>
    </div>
</template>
