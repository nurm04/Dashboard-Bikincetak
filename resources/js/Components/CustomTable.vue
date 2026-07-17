<script setup>
defineProps({
    headers: Array,
});
</script>

<template>
    <div class="border rounded-lg shadow-xl bg-base-100 border-base-300">
        <div class="pb-4 overflow-x-visible">
            <table class="w-full text-sm text-left border-collapse">
                <thead class="bg-base-200/50 text-base-content/70 uppercase font-black text-[10px] tracking-[0.15em] border-b-2 border-base-300">
                    <tr>
                        <th v-for="header in headers" :key="header" class="px-3 py-2">
                            {{ header }}
                        </th>
                    </tr>
                </thead>

                <tbody class="
                    divide-y border-t border-base-200 divide-base-200/50 text-base-content
                    [&_td]:border-r [&_td]:border-base-200/50 [&_td]:px-3 [&_td]:py-2
                    [&_td:last-child]:border-r-0
                    [&_tr:hover_td]:bg-base-200/30 [&_tr:hover_td]:transition-colors
                ">
                    <slot />
                </tbody>
            </table>
        </div>
    </div>
</template>
