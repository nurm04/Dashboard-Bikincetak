<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const searchQuery = ref('');

// State untuk dropdown produk
// Kita buat default 'true' kalau user lagi di halaman produk/kategori/varian
const isProductDropdownOpen = ref(
    route().current('produk.*') ||
    route().current('kategori.*') ||
    route().current('varian.*')
);

const toggleProductDropdown = () => {
    isProductDropdownOpen.value = !isProductDropdownOpen.value;
};
</script>

<template>
    <aside class="fixed top-0 left-0 z-50 flex flex-col w-64 h-screen transition-colors border-r bg-base-100 border-base-300">

        <div class="flex items-center shrink-0 h-16 px-6 border-b border-base-300">
            <span class="text-xl italic font-black tracking-tighter text-primary drop-shadow-sm">
                BIKIN<span class="text-base-content">CETAK</span>
            </span>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <p class="text-[10px] font-black text-base-content/30 uppercase tracking-widest ml-3 mb-4">Navigasi Utama</p>

            <Link :href="route('dashboard')"
                :class="[route().current('dashboard')
                    ? 'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1'
                    : 'text-base-content/70 hover:bg-base-200 hover:text-base-content hover:translate-x-1']"
                class="flex items-center px-4 py-3 text-sm font-bold transition-all duration-300 rounded-xl group">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Dashboard
            </Link>

            <Link v-if="$can('akun')" :href="route('akun.index')"
                :class="[route().current('akun.*')
                    ? 'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1'
                    : 'text-base-content/70 hover:bg-base-200 hover:text-base-content hover:translate-x-1']"
                class="flex items-center px-4 py-3 text-sm font-bold transition-all duration-300 rounded-xl">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Data Akun COA
            </Link>

            <Link v-if="$can('customer')" :href="route('customer.index')"
                :class="[route().current('customer.*')
                    ? 'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1'
                    : 'text-base-content/70 hover:bg-base-200 hover:text-base-content hover:translate-x-1']"
                class="flex items-center px-4 py-3 text-sm font-bold transition-all duration-300 rounded-xl">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Data Customer
            </Link>

            <Link v-if="$can('staf')" :href="route('staf.index')"
                :class="[route().current('staf.*')
                    ? 'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1'
                    : 'text-base-content/70 hover:bg-base-200 hover:text-base-content hover:translate-x-1']"
                class="flex items-center px-4 py-3 text-sm font-bold transition-all duration-300 rounded-xl">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Data Staf
            </Link>

            <div v-if="$can('kategori') || $can('produk') || $can('varian') || $can('produk-sku') || $can('finishing')" class="space-y-1">
                <button @click="toggleProductDropdown"
                    :class="[route().current('produk.*') || route().current('finishing.*') || route().current('kategori.*') || route().current('varian.*') || route().current('sku.*')
                        ? 'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1'
                        : 'text-base-content/70 hover:bg-base-200 hover:text-base-content hover:translate-x-1']"
                    class="flex items-center justify-between w-full px-4 py-3 text-sm font-bold transition-all duration-300 rounded-xl group">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Data Produk
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-300"
                        :class="{ 'rotate-180': isProductDropdownOpen }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div v-show="isProductDropdownOpen" class="pl-2 mt-1 ml-4 space-y-2 border-l-2 border-base-300/50">
                    <Link v-if="$can('kategori')" :href="route('kategori.index')"
                        :class="[route().current('kategori.*')
                            ? 'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1'
                            : 'text-base-content/60 hover:bg-base-200 hover:text-base-content hover:translate-x-1']"
                        class="flex items-center px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all duration-300 rounded-lg">
                        Kategori
                    </Link>

                    <Link v-if="$can('produk')" :href="route('produk.index')"
                        :class="[route().current('produk.*') || route().current('sku.*')
                            ? 'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1'
                            : 'text-base-content/60 hover:bg-base-200 hover:text-base-content hover:translate-x-1']"
                        class="flex items-center px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all duration-300 rounded-lg">
                        Produk
                    </Link>

                    <Link v-if="$can('varian')" :href="route('varian.index')"
                        :class="[route().current('varian.*')
                            ? 'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1'
                            : 'text-base-content/60 hover:bg-base-200 hover:text-base-content hover:translate-x-1']"
                        class="flex items-center px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all duration-300 rounded-lg">
                        Varian
                    </Link>

                    <Link v-if="$can('finishing')" :href="route('finishing.index')"
                        :class="[route().current('finishing.*')
                            ? 'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1'
                            : 'text-base-content/60 hover:bg-base-200 hover:text-base-content hover:translate-x-1']"
                        class="flex items-center px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all duration-300 rounded-lg">
                        Finishing
                    </Link>
                </div>
            </div>

            <Link v-if="$can('bahan-baku')" :href="route('bahan-baku.index')"
                :class="[route().current('bahan-baku.*')
                    ? 'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1'
                    : 'text-base-content/70 hover:bg-base-200 hover:text-base-content hover:translate-x-1']"
                class="flex items-center px-4 py-3 text-sm font-bold transition-all duration-300 rounded-xl">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Bahan Baku
            </Link>

            <Link v-if="$can('pembelian-bahan')" :href="route('pembelian-bahan.index')"
                :class="[route().current('pembelian-bahan.*')
                    ? 'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1'
                    : 'text-base-content/70 hover:bg-base-200 hover:text-base-content hover:translate-x-1']"
                class="flex items-center px-4 py-3 text-sm font-bold transition-all duration-300 rounded-xl">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Pembelian Bahan
            </Link>

            <Link v-if="$can('pesan')" :href="route('pesan.index')"
                :class="[route().current('pesan.*')
                    ? 'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1'
                    : 'text-base-content/70 hover:bg-base-200 hover:text-base-content hover:translate-x-1']"
                class="flex items-center px-4 py-3 text-sm font-bold transition-all duration-300 rounded-xl">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Pesanan
            </Link>

            <Link v-if="$can('hak-akses')" :href="route('hak-akses.index')"
                :class="[route().current('hak-akses.*')
                    ? 'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1'
                    : 'text-base-content/70 hover:bg-base-200 hover:text-base-content hover:translate-x-1']"
                class="flex items-center px-4 py-3 text-sm font-bold transition-all duration-300 rounded-xl">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Hak Akses
            </Link>
        </nav>

        <div class="p-4 border-t border-base-300 bg-base-100 shrink-0">
            <div class="flex items-center px-3 py-3 border bg-base-200 rounded-2xl border-base-300/50">
                <div class="mr-3 avatar placeholder">
                    <div class="flex items-center justify-center font-black text-white rounded-lg shadow-sm bg-primary w-9 h-9">
                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-black leading-tight truncate text-base-content">{{ $page.props.auth.user.name }}</p>
                    <p class="text-[9px] text-base-content/40 truncate italic font-bold">ADMINISTRATOR</p>
                </div>
            </div>
        </div>
    </aside>
</template>

<style scoped>
nav::-webkit-scrollbar {
    width: 4px;
}
nav::-webkit-scrollbar-track {
    background: transparent;
}
nav::-webkit-scrollbar-thumb {
    background-color: oklch(var(--p) / 0.2);
    border-radius: 10px;
}
nav:hover::-webkit-scrollbar-thumb {
    background-color: oklch(var(--p) / 0.5);
}
</style>
