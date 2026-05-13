<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    type: { type: String, default: 'button' },
    href: { type: String, default: null },
    variant: { type: String, default: 'primary' },
    size: { type: String, default: 'md' },
    disabled: { type: Boolean, default: false },
});

// FIX: Menggunakan warna adaptif DaisyUI
const variants = {
    primary: 'bg-primary text-primary-content hover:bg-primary/90 shadow-lg shadow-primary/30',
    success: 'bg-success text-success-content hover:bg-success/90 shadow-lg shadow-success/30',
    error: 'bg-error text-error-content hover:bg-error/90 shadow-lg shadow-error/30',
    warning: 'bg-warning text-warning-content hover:bg-warning/90 shadow-lg shadow-warning/30',
    info: 'bg-info text-info-content hover:bg-info/90 shadow-lg shadow-info/30',
    neutral: 'bg-neutral text-neutral-content hover:bg-neutral/90 shadow-lg shadow-neutral/30',
    secondary: 'bg-base-200 text-base-content hover:bg-base-300 border border-base-300 shadow-sm',
};

const sizes = {
    sm: 'px-3 py-1.5 text-xs',
    md: 'px-5 py-2.5 text-sm',
    lg: 'px-8 py-4 text-base',
};

const classes = computed(() => {
    return [
        'inline-flex items-center justify-center font-bold rounded-xl transition-all transform active:scale-95 disabled:opacity-50 disabled:scale-100 disabled:cursor-not-allowed',
        variants[props.variant] || variants.primary,
        sizes[props.size] || sizes.md
    ].join(' ');
});
</script>

<template>
    <Link v-if="type === 'link'" :href="href" :class="classes">
        <slot name="icon" />
        <span :class="{ 'ml-2': $slots.icon && $slots.default }"><slot /></span>
    </Link>

    <button v-else :type="type" :disabled="disabled" :class="classes">
        <slot name="icon" />
        <span :class="{ 'ml-2': $slots.icon && $slots.default }"><slot /></span>
    </button>
</template>
