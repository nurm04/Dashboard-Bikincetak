<script setup>
import { computed, useSlots } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    type: { type: String, default: 'button' },
    href: { type: String, default: null },
    variant: { type: String, default: 'primary' },
    size: { type: String, default: 'md' },
    disabled: { type: Boolean, default: false },
    block: { type: Boolean, default: false }, // BARU: Tambahin true biar tombol full-width (w-full)
});

// Import slots buat ngecek isi konten secara akurat di Vue 3
const slots = useSlots();

// BASE VARIANTS (Tanpa efek hover)
const variants = {
    primary: 'bg-primary text-primary-content shadow-primary/30',
    success: 'bg-success text-success-content shadow-success/30',
    error: 'bg-error text-error-content shadow-error/30',
    warning: 'bg-warning text-warning-content shadow-warning/30',
    info: 'bg-info text-info-content shadow-info/30',
    neutral: 'bg-neutral text-neutral-content shadow-neutral/30',
    secondary: 'bg-base-200 text-base-content border border-base-300 shadow-sm',
};

// HOVER VARIANTS (Dipisah biar gak nyala pas tombol lagi disabled)
const hoverVariants = {
    primary: 'hover:bg-primary/90 hover:shadow-lg',
    success: 'hover:bg-success/90 hover:shadow-lg',
    error: 'hover:bg-error/90 hover:shadow-lg',
    warning: 'hover:bg-warning/90 hover:shadow-lg',
    info: 'hover:bg-info/90 hover:shadow-lg',
    neutral: 'hover:bg-neutral/90 hover:shadow-lg',
    secondary: 'hover:bg-base-300',
};

const sizes = {
    sm: 'px-3 py-1.5 text-xs',
    md: 'px-5 py-2.5 text-sm',
    lg: 'px-8 py-4 text-base',
};

const classes = computed(() => {
    return [
        'inline-flex items-center justify-center font-bold rounded-xl transition-all shadow-sm',
        variants[props.variant] || variants.primary,
        sizes[props.size] || sizes.md,
        props.block ? 'w-full' : '', // Kalau prop block=true, otomatis penuhi layar

        // Handling disabled manual yang ngefek ke <button> maupun <Link>
        props.disabled
            ? 'opacity-50 cursor-not-allowed pointer-events-none' // pointer-events-none mengunci link agar tidak bisa diklik
            : `${hoverVariants[props.variant] || hoverVariants.primary} transform active:scale-95`
    ].join(' ');
});
</script>

<template>
    <!-- Render sebagai Inertia Link -->
    <Link v-if="type === 'link'" :href="disabled ? '#' : href" :class="classes">
        <slot name="icon" />
        <span v-if="slots.default" :class="{ 'ml-2': slots.icon }">
            <slot />
        </span>
    </Link>

    <!-- Render sebagai Button Biasa -->
    <button v-else :type="type" :disabled="disabled" :class="classes">
        <slot name="icon" />
        <span v-if="slots.default" :class="{ 'ml-2': slots.icon }">
            <slot />
        </span>
    </button>
</template>
