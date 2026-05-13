<script setup>
import Modal from '@/Components/Modal.vue';
import CustomButton from '@/Components/CustomButton.vue';

const props = defineProps({
    show: Boolean,
    type: {
        type: String,
        default: 'primary' // [error, success, info, warning, primary]
    },
    title: String,
    message: String,
    confirmText: {
        type: String,
        default: 'Ya, Lanjutkan'
    },
    cancelText: {
        type: String,
        default: 'Batal'
    },
    loading: Boolean
});

const emit = defineEmits(['close', 'confirm']);

// Mapping warna & icon berdasarkan type
const config = {
    primary: {
        bgIcon: 'bg-primary/10',
        textIcon: 'text-primary',
        btnVariant: 'primary',
        icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    },
    success: {
        bgIcon: 'bg-success/10',
        textIcon: 'text-success',
        btnVariant: 'success',
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
    },
    error: {
        bgIcon: 'bg-error/10',
        textIcon: 'text-error',
        btnVariant: 'error',
        icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
    },
    warning: {
        bgIcon: 'bg-warning/10',
        textIcon: 'text-warning',
        btnVariant: 'warning',
        icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
    },
    info: {
        bgIcon: 'bg-info/10',
        textIcon: 'text-info',
        btnVariant: 'info',
        icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    }
};

const current = config[props.type] || config.primary;
</script>

<template>
    <Modal :show="show" @close="$emit('close')" maxWidth="md">
        <div class="relative p-8 overflow-hidden bg-base-100 rounded-3xl">
            <div class="absolute top-0 right-0 w-32 h-32 -mt-16 -mr-16 rounded-full opacity-5" :class="current.bgIcon"></div>

            <div class="flex flex-col items-center space-y-4 text-center">
                <div :class="['p-4 rounded-2xl shadow-sm transition-all duration-500', current.bgIcon, current.textIcon]">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="current.icon"></path>
                    </svg>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl italic font-black tracking-tighter uppercase text-base-content">
                        {{ title }}
                    </h3>
                    <p class="px-4 text-sm font-medium leading-relaxed text-base-content/60">
                        {{ message }}
                    </p>
                </div>

                <div class="flex flex-col w-full gap-3 pt-6 sm:flex-row">
                    <CustomButton
                        @click="$emit('confirm')"
                        :variant="current.btnVariant"
                        class="flex-1 order-2 py-4 rounded-2xl sm:order-1"
                        :disabled="loading"
                    >
                        <template #icon v-if="loading">
                            <span class="mr-2 loading loading-spinner loading-xs"></span>
                        </template>
                        {{ confirmText }}
                    </CustomButton>

                    <CustomButton
                        @click="$emit('close')"
                        variant="secondary"
                        class="flex-1 order-1 py-4 rounded-2xl sm:order-2"
                        :disabled="loading"
                    >
                        {{ cancelText }}
                    </CustomButton>
                </div>
            </div>
        </div>
    </Modal>
</template>
