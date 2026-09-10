<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    error: String,
    options: { type: Array, default: () => [] },
    labelKey: { type: String, default: 'label' },
    valueKey: { type: String, default: 'value' },
    placeholder: { type: String, default: 'Pilih data...' },
    addOption: { type: Boolean, default: true }
});

const emit = defineEmits(['update:modelValue', 'onCreate']);

const isOpen = ref(false);
const search = ref('');
const container = ref(null);
const dropdownRef = ref(null);
const dropdownStyle = ref({});

// Filter data berdasarkan ketikan
const filteredOptions = computed(() => {
    return props.options.filter(opt =>
        String(opt[props.labelKey] || '').toLowerCase().includes(search.value.toLowerCase())
    );
});

// Ambil label dari value yang terpilih
const selectedLabel = computed(() => {
    const selected = props.options.find(opt => opt[props.valueKey] === props.modelValue);
    return selected ? selected[props.labelKey] : '';
});

const selectOption = (opt) => {
    emit('update:modelValue', opt[props.valueKey]);
    isOpen.value = false;
    search.value = '';
};

const handleCreate = () => {
    emit('onCreate', search.value);
    isOpen.value = false;
};

// ==========================================
// LOGIC POSISI FIXED (AGAR KELUAR DARI TABEL)
// ==========================================
const calculatePosition = () => {
    if (!container.value || !isOpen.value) return;

    const rect = container.value.getBoundingClientRect();
    const spaceBelow = window.innerHeight - rect.bottom;
    const dropdownHeight = 300; // Estimasi tinggi dropdown

    // Jika di bawah gak cukup ruang tapi di atas cukup, buka ke atas
    if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
        dropdownStyle.value = {
            position: 'fixed',
            bottom: `${window.innerHeight - rect.top + 8}px`, // 8px margin
            left: `${rect.left}px`,
            width: `${rect.width}px`,
            zIndex: 999999
        };
    } else {
        // Normalnya buka ke bawah
        dropdownStyle.value = {
            position: 'fixed',
            top: `${rect.bottom + 8}px`,
            left: `${rect.left}px`,
            width: `${rect.width}px`,
            zIndex: 999999
        };
    }
};

watch(isOpen, (val) => {
    if (val) {
        nextTick(() => {
            calculatePosition();
            // Pakai true (capture) agar bisa mendeteksi scroll di dalam parent (tabel)
            window.addEventListener('scroll', calculatePosition, true);
            window.addEventListener('resize', calculatePosition);
        });
    } else {
        window.removeEventListener('scroll', calculatePosition, true);
        window.removeEventListener('resize', calculatePosition);
    }
});

// Close dropdown kalau klik di luar
const handleClickOutside = (event) => {
    // Karena dropdown kita teleport ke body, kita harus ngecek apakah kliknya di container ATAU di dalam dropdown
    const clickedInContainer = container.value && container.value.contains(event.target);
    const clickedInDropdown = dropdownRef.value && dropdownRef.value.contains(event.target);

    if (!clickedInContainer && !clickedInDropdown) {
        isOpen.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('scroll', calculatePosition, true);
    window.removeEventListener('resize', calculatePosition);
});
</script>

<template>
    <div class="relative w-full" ref="container">
        <label v-if="label" class="block mb-1 ml-1 text-xs font-bold text-base-content/70">
            {{ label }}
        </label>

        <div
            @click="isOpen = !isOpen"
            class="flex items-center justify-between w-full px-3 py-2 transition border rounded-lg shadow-sm cursor-pointer border-base-300 bg-base-100 focus-within:ring-4 focus-within:ring-primary/10 focus-within:border-primary"
        >
            <span :class="modelValue ? 'text-base-content' : 'text-base-content/30'" class="text-sm font-bold truncate">
                {{ selectedLabel || placeholder }}
            </span>
            <svg class="w-4 h-4 transition-transform opacity-50" :class="{'rotate-180': isOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>

        <!-- 👇 TELEPORT KE BODY AGAR LEPAS DARI OVERFLOW TABEL 👇 -->
        <Teleport to="body">
            <div
                v-if="isOpen"
                ref="dropdownRef"
                :style="dropdownStyle"
                class="overflow-hidden duration-200 border rounded-lg shadow-2xl bg-base-100 border-base-300 animate-in fade-in zoom-in"
            >
                <div class="p-2 border-b border-base-200">
                    <input
                        v-model="search"
                        type="text"
                        class="w-full px-3 py-2 text-xs border-none rounded-lg bg-base-200 focus:ring-2 focus:ring-primary/30 text-base-content outline-none"
                        :placeholder="'Cari ' + (label || '') + '...'"
                        autofocus
                    />
                </div>

                <ul class="py-1 overflow-y-auto max-h-60 scrollbar-hide">
                    <li v-for="opt in filteredOptions" :key="opt[valueKey]"
                        @click="selectOption(opt)"
                        class="px-4 py-2 text-sm font-bold transition-colors cursor-pointer text-base-content/70 hover:bg-primary hover:text-white"
                    >
                        {{ opt[labelKey] }}
                    </li>

                    <li v-if="filteredOptions.length === 0" class="px-4 py-3 text-xs italic text-center text-base-content/40">
                        Data tidak ditemukan
                    </li>
                </ul>

                <div v-if="addOption" @click="handleCreate" class="p-2 border-t bg-base-200 border-base-300 cursor-pointer">
                    <button type="button" class="flex items-center justify-center w-full gap-2 py-2 text-xs font-black transition-all rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        TAMBAH {{ label?.toUpperCase() || 'DATA' }} BARU
                    </button>
                </div>
            </div>
        </Teleport>

        <p v-if="error" class="text-error text-[10px] mt-1 font-bold">{{ error }}</p>
    </div>
</template>
