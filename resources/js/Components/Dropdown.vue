<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    align: { type: String, default: 'right' },
    width: { type: String, default: '48' },
    contentClasses: { type: String, default: 'py-2 bg-base-100' },
});

const open = ref(false);
const dropdownElement = ref(null); // Reference ke elemen utama

// Fungsi untuk menutup jika klik di luar elemen dropdown
const closeOnClickOutside = (event) => {
    if (open.value && dropdownElement.value && !dropdownElement.value.contains(event.target)) {
        open.value = false;
    }
};

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') open.value = false;
};

onMounted(() => {
    document.addEventListener('click', closeOnClickOutside);
    document.addEventListener('keydown', closeOnEscape);
});

onUnmounted(() => {
    document.removeEventListener('click', closeOnClickOutside);
    document.removeEventListener('keydown', closeOnEscape);
});

const widthClass = computed(() => {
    return {
        '48': 'w-48',
        '56': 'w-56',
    }[props.width.toString()] || 'w-48';
});

const alignmentClasses = computed(() => {
    if (props.align === 'left') return 'origin-top-left left-0';
    if (props.align === 'right') return 'origin-top-right right-0';
    return 'origin-top';
});
</script>

<template>
    <div class="relative inline-block" ref="dropdownElement">

        <div @click="open = !open" class="cursor-pointer">
            <slot name="trigger" />
        </div>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95 translate-y-[-10px]"
            enter-to-class="scale-100 translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="scale-100 translate-y-0 opacity-100"
            leave-to-class="opacity-0 scale-95 translate-y-[-10px]"
        >
            <div
                v-show="open"
                class="absolute mt-2 overflow-hidden border shadow-2xl z-100 rounded-2xl border-base-300"
                :class="[widthClass, alignmentClasses, contentClasses]"
                @click="open = false"
            >
                <slot name="content" />
            </div>
        </Transition>
    </div>
</template>
