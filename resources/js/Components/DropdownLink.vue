<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
    as: {
        type: String,
        default: 'a',
    },
});
</script>

<template>
    <div>
        <button v-if="as == 'button'" type="submit" class="block w-full px-4 py-2 text-sm font-bold leading-5 transition duration-150 ease-in-out text-start text-base-content/70 hover:bg-base-200 hover:text-base-content focus:outline-none focus:bg-base-200">
            <slot />
        </button>

        <a v-else-if="as =='a'" :href="href" class="block w-full px-4 py-2 text-sm font-bold leading-5 transition duration-150 ease-in-out text-start text-base-content/70 hover:bg-base-200 hover:text-base-content focus:outline-none focus:bg-base-200">
            <slot />
        </a>

        <Link v-else :href="href" class="block w-full px-4 py-2 text-sm font-bold leading-5 transition duration-150 ease-in-out text-start text-base-content/70 hover:bg-base-200 hover:text-base-content focus:outline-none focus:bg-base-200">
            <slot />
        </Link>
    </div>
</template>
