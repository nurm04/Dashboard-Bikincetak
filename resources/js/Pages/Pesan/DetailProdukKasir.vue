<script setup>
import { Head, Link } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import ProdukForm from '@/Components/ProdukForm.vue';

defineProps({
    produk: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <Head :title="`Detail - ${produk.nama_produk}`" />

    <StafLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('pos.katalog')" class="btn btn-sm btn-circle btn-ghost">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </Link>
                <div>
                    <h2 class="text-xl font-bold leading-tight text-base-content">
                        Kustomisasi Pesanan
                    </h2>
                    <p class="text-xs font-bold opacity-50 uppercase tracking-widest mt-1">
                        Kategori: {{ produk.kategori?.nama_kategori || 'Umum' }}
                    </p>
                </div>
            </div>
        </template>

        <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <ProdukForm :produk="produk" />
        </div>
    </StafLayout>
</template>
