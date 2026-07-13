<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';

const props = defineProps({
    results: Object,
    keyword: String,
    sort: String
});

const currentSort = ref(props.sort);

const updateSort = () => {
    router.get(route('global.search'), { 
        key: props.keyword, 
        sort: currentSort.value 
    }, {
        preserveState: true,
        preserveScroll: true
    });
};
</script>

<template>
    <Head :title="`Pencarian: ${keyword}`" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Hasil Pencarian Global
            </h2>
        </template>

        <div class="min-h-screen px-4 py-6 mx-auto sm:px-6 lg:px-8 max-w-7xl">

            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-sm font-medium text-base-content/60">
                        Menampilkan <span class="font-black text-primary">{{ results.total }} data</span> untuk pencarian: <span class="font-black text-base-content">"{{ keyword }}"</span>
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold uppercase text-base-content/50">Urutkan:</span>
                    <select 
                        v-model="currentSort" 
                        @change="updateSort" 
                        class="text-xs font-bold uppercase border-none shadow-sm select select-bordered select-sm bg-base-100 focus:ring-2 focus:ring-primary/20"
                    >
                        <option value="desc">Paling Baru</option>
                        <option value="asc">Paling Lama</option>
                    </select>
                </div>
            </div>
            
            <div class="w-full h-px mb-6 bg-base-300"></div>

            <div v-if="results.data.length > 0">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    <div
                        v-for="(item, index) in results.data"
                        :key="index"
                        class="flex flex-col justify-between p-5 transition-all border shadow-sm bg-base-100 rounded-xl border-base-300 hover:border-primary/50 hover:shadow-md group"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2 py-1 text-[9px] font-black tracking-widest uppercase border border-base-300 bg-base-200 rounded-md text-base-content/70">
                                    Modul {{ item.nama_modul }}
                                </span>
                                <span class="text-[10px] font-bold text-base-content/40">
                                    {{ item.updated_at }}
                                </span>
                            </div>

                            <h3 class="font-mono text-sm font-black text-primary group-hover:text-primary-focus">
                                {{ item.id_data }}
                            </h3>
                            <p class="mt-1 text-xs font-medium truncate text-base-content/70">
                                {{ item.keterangan }}
                            </p>
                        </div>

                        <div class="mt-5">
                            <Link
                                :href="`/${item.slug}?search=${item.id_data}`"
                                class="w-full text-xs font-bold btn btn-sm btn-outline hover:btn-primary"
                            >
                                Lihat Detail
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="results.links && results.links.length > 3" class="flex justify-center mt-10 space-x-1">
                    <template v-for="(link, k) in results.links" :key="k">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="px-4 py-2 text-xs font-black transition-colors border shadow-sm rounded-xl"
                            :class="link.active 
                                ? 'bg-primary text-primary-content border-primary' 
                                : 'bg-base-100 text-base-content hover:bg-base-200 border-base-300'"
                            preserve-scroll
                        />
                        <span 
                            v-else 
                            v-html="link.label" 
                            class="px-4 py-2 text-xs font-black border shadow-sm opacity-50 cursor-not-allowed rounded-xl bg-base-200 text-base-content/50 border-base-300"
                        ></span>
                    </template>
                </div>
            </div>

            <div v-else class="py-20 text-center">
                <div class="flex flex-col items-center justify-center opacity-40">
                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <h3 class="text-sm font-black tracking-widest uppercase">Data Tidak Ditemukan</h3>
                </div>
            </div>

        </div>
    </StafLayout>
</template>