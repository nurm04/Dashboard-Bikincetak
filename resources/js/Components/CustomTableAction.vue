<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const isOpen = ref(false);
const actionRef = ref(null);

const toggle = () => {
    if (!isOpen.value) {
        window.dispatchEvent(new CustomEvent('close-all-dropdowns'));
    }
    isOpen.value = !isOpen.value;
};

const close = () => (isOpen.value = false);

const handleClickOutside = (event) => {
    if (actionRef.value && !actionRef.value.contains(event.target)) {
        close();
    }
};

const handleCloseAll = () => {
    close();
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    window.addEventListener('close-all-dropdowns', handleCloseAll);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('close-all-dropdowns', handleCloseAll);
});
</script>

<template>
    <div class="relative inline-block" ref="actionRef">
        <button
            @click.stop="toggle"
            type="button"
            class="p-2 transition-all duration-300 border border-transparent rounded-xl hover:bg-primary/10 group"
            :class="isOpen ? 'bg-primary/10 text-primary border-primary/20' : 'text-base-content/30'"
        >
            <svg class="w-5 h-5 transition-transform duration-300" :class="{ 'rotate-90': isOpen }" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
            </svg>
        </button>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="scale-95 translate-y-2 opacity-0"
            enter-to-class="scale-100 translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="scale-100 translate-y-0 opacity-100"
            leave-to-class="scale-95 translate-y-2 opacity-0"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 top-full mt-2 w-52 z-999 bg-base-100 border border-base-300 shadow-2xl rounded-2xl overflow-hidden py-2"
            >
                <slot :close="close" />
            </div>
        </Transition>
    </div>
</template>
