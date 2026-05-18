<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: String,
    data: Array,
});

const handleImageError = (e) => {
    e.target.src = '/favicon.ico';
};
</script>

<template>
    <div class="mb-10">
        <p class="pt-4 pb-2 mb-4 font-bold border-b-2 text-primary lg:text-xl border-primary w-fit">
            {{ title }}
        </p>

        <div class="flex gap-4 pb-4 overflow-x-auto md:gap-5 scrollbar-hide snap-x snap-mandatory">
            <div
                v-for="(item, i) in data"
                :key="i"
                class="card min-w-40 md:min-w-60 bg-base-100 shadow-sm border border-primary/20 group overflow-hidden snap-start transition-all hover:shadow-md"
            >
                <figure class="relative w-full overflow-hidden h-28 md:h-44 bg-base-300">
                    <img
                        :src="item.gambar || '/favicon.ico'"
                        :alt="item.nama_produk"
                        @error="handleImageError"
                        class="object-cover w-full h-full transition-transform duration-500 group-hover:scale-110"
                    />
                    <div class="absolute inset-0 bg-primary/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]">
                        <Link
                            :href="route('pos.detail', item.id_produk)"
                            class="text-white shadow-lg transition-transform scale-90 btn btn-primary btn-sm group-hover:scale-100"
                        >
                            Detail Produk
                        </Link>
                    </div>
                </figure>

                <div class="p-3 md:p-4 card-body">
                    <h2 class="text-sm leading-tight md:text-base card-title h-10 line-clamp-2">
                        {{ item.nama_produk }}
                    </h2>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
