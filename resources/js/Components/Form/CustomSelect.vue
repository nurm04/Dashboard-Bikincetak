<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    error: String,
    options: { type: Array, default: () => [] },
    labelKey: { type: String, default: 'label' },
    valueKey: { type: String, default: 'value' },
    placeholder: { type: String, default: 'Pilih data...' }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const actionRef = ref(null); // Samain kayak CustomTableAction
const dropdownRef = ref(null); // Ref untuk menu dropdown teleport
const dropdownStyle = ref({});

const calculatePosition = () => {
    if (!actionRef.value) return;

    // Ambil posisi elemen input select saat ini
    const rect = actionRef.value.getBoundingClientRect();

    // Set koordinat, lebar disamakan dengan input aslinya
    dropdownStyle.value = {
        top: `${rect.bottom + 8}px`, // Jarak 8px dari bawah tombol
        left: `${rect.left}px`,
        width: `${rect.width}px`     // Penting: Biar lebarnya nggak acak-acakan
    };
};

const toggle = async () => {
    if (!isOpen.value) {
        window.dispatchEvent(new CustomEvent('close-all-dropdowns'));
        // Tunggu DOM update, lalu hitung posisi kordinatnya
        await nextTick();
        calculatePosition();
    }
    isOpen.value = !isOpen.value;
};

const close = () => (isOpen.value = false);

const selectedLabel = computed(() => {
    const selected = props.options.find(opt => String(opt[props.valueKey]) === String(props.modelValue));
    return selected ? selected[props.labelKey] : '';
});

const selectOption = (opt) => {
    emit('update:modelValue', opt[props.valueKey]);
    close();
};

const handleClickOutside = (event) => {
    // Cek apakah klik terjadi di dalam tombol toggle
    const isClickInsideButton = actionRef.value && actionRef.value.contains(event.target);
    // Cek apakah klik terjadi di dalam menu dropdown yang di-teleport
    const isClickInsideDropdown = dropdownRef.value && dropdownRef.value.contains(event.target);

    if (!isClickInsideButton && !isClickInsideDropdown) {
        close();
    }
};

const handleCloseAll = () => {
    close();
};

const handleScroll = (event) => {
    if (!isOpen.value) return;

    const isScrollInsideDropdown = dropdownRef.value && dropdownRef.value.contains(event.target);

    if (!isScrollInsideDropdown) {
        close();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    window.addEventListener('close-all-dropdowns', handleCloseAll);
    // Angka "true" wajib ada untuk menangkap scroll dari dalam tabel
    window.addEventListener('scroll', handleScroll, true);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('close-all-dropdowns', handleCloseAll);
    window.removeEventListener('scroll', handleScroll, true);
});
</script>

<template>
    <!-- ref actionRef dipindah ke div pembungkus utama -->
    <div class="relative inline-block w-full" ref="actionRef">
        <label v-if="label" class="block mb-1 ml-1 text-xs font-bold text-base-content/70">
            {{ label }}
        </label>

        <!-- Tombol Pemicu / Input Palsu -->
        <div
            @click.stop="toggle"
            class="flex items-center justify-between w-full px-3 py-2 transition-all duration-300 border rounded-xl cursor-pointer bg-base-100"
            :class="isOpen
                ? 'border-primary ring-4 ring-primary/10'
                : 'border-base-300 hover:border-primary/50'"
        >
            <span :class="modelValue ? 'text-base-content' : 'text-base-content/30'" class="text-sm font-bold truncate">
                {{ selectedLabel || placeholder }}
            </span>
            <svg class="w-4 h-4 transition-transform duration-300 opacity-50" :class="{'rotate-180': isOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>

        <!-- AJAIBNYA VUE 3: Teleport akan memindahkan elemen ini keluar dari tabel langsung ke <body> -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="scale-95 translate-y-2 opacity-0"
                enter-to-class="scale-100 translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="scale-100 translate-y-0 opacity-100"
                leave-to-class="scale-95 translate-y-2 opacity-0"
            >
                <!-- CLASS fixed DAN z-9999 SEKARANG HARDFIX DI SINI -->
                <div
                    v-if="isOpen"
                    ref="dropdownRef"
                    class="fixed py-2 overflow-hidden border shadow-2xl z-9999 bg-base-100 border-base-300 rounded-2xl"
                    :style="dropdownStyle"
                >
                    <ul class="py-1 overflow-y-auto max-h-60 scrollbar-hide">
                        <li v-for="opt in options" :key="opt[valueKey]"
                            @click="selectOption(opt)"
                            class="px-4 py-3 text-[11px] font-black uppercase tracking-widest transition-all cursor-pointer border-b border-base-200/50 last:border-0 hover:bg-primary/10 hover:text-primary"
                            :class="String(opt[props.valueKey]) === String(modelValue) ? 'bg-primary/10 text-primary' : 'text-base-content/70'"
                        >
                            {{ opt[labelKey] }}
                        </li>
                    </ul>
                </div>
            </Transition>
        </Teleport>

        <p v-if="error" class="text-error text-[10px] mt-1 ml-1 font-bold">{{ error }}</p>
    </div>
</template>
