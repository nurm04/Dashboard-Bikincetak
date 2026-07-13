<script setup>
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-base-200 pt-6 sm:justify-center sm:pt-0">

        <slot />

        <div class="fixed bottom-10 right-0 p-1 pr-10 rounded-l-3xl z-9999 w-auto h-auto bg-primary">
            <ThemeSwitcher />
        </div>

    </div>
</template>
