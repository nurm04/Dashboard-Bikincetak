<script setup>
import { alertStore } from '@/Utils/alertStore';
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out transform"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-x-0 opacity-100"
        leave-to-class="translate-x-4 opacity-0"
    >
        <div v-if="alertStore.visible" class="toast toast-top toast-end z-[9999] mt-20">
            <div :class="[
                'alert shadow-2xl font-bold border-none flex items-center gap-3',
                { 'alert-success text-success-content': alertStore.type === 'success' },
                { 'alert-error text-error-content': alertStore.type === 'error' },
                { 'alert-info text-info-content': alertStore.type === 'info' },
                { 'alert-warning text-warning-content': alertStore.type === 'warning' },
            ]">
                <svg v-if="alertStore.type === 'success'" class="w-6 h-6 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <svg v-if="alertStore.type === 'error'" class="w-6 h-6 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>

                <span>{{ alertStore.message }}</span>

                <button @click="alertStore.visible = false" class="ml-2 btn btn-ghost btn-xs btn-circle opacity-60 hover:opacity-100">✕</button>
            </div>
        </div>
    </Transition>
</template>
