<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    headers: Array,
    // 👇 Props baru untuk menerima object pagination dari Laravel
    pagination: {
        type: [Object, Boolean],
        default: false,
    },
});
</script>

<template>
    <div class="border rounded-lg shadow-xl bg-base-100 border-base-300 flex flex-col">
        <!-- Pakai overflow-x-auto saja, sudah aman dari masalah terpotong -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead class="bg-base-200/50 text-base-content/70 uppercase font-black text-[10px] tracking-[0.15em] border-b-2 border-base-300">
                    <tr>
                        <th v-for="header in headers" :key="header" class="px-3 py-2 whitespace-nowrap">
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

        <!-- 👇 BLOK PAGINATION OTOMATIS 👇 -->
        <div v-if="pagination && pagination.total > 0" class="flex flex-col items-center justify-between gap-4 px-4 py-4 border-t sm:flex-row border-base-300 bg-base-200/30 rounded-b-lg">

            <!-- Info Data (Contoh: Menampilkan 1 - 10 dari 50 data) -->
            <div class="text-[10px] font-bold tracking-widest uppercase opacity-60">
                Menampilkan <span class="text-primary font-black">{{ pagination.from || 0 }}</span> - <span class="text-primary font-black">{{ pagination.to || 0 }}</span> dari <span class="text-primary font-black">{{ pagination.total }}</span> data
            </div>

            <!-- Tombol Navigasi Angka -->
            <div class="join shadow-sm">
                <template v-for="(link, index) in pagination.links" :key="index">
                    <!-- Tombol Aktif & Bisa Diklik -->
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        preserve-state
                        class="join-item btn btn-sm border-base-300"
                        :class="link.active ? 'btn-primary font-black pointer-events-none' : 'bg-base-100 hover:bg-base-200'"
                        v-html="link.label"
                    />
                    <!-- Tombol Disabled (Kalo udah di page paling awal/akhir) -->
                    <div
                        v-else
                        class="join-item btn btn-sm btn-disabled border-base-300 bg-base-200/50"
                        v-html="link.label"
                    />
                </template>
            </div>

        </div>
    </div>
</template>
