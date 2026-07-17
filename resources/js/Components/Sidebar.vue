<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    isCollapsed: {
        type: Boolean,
        default: false
    }
});

const isProductDropdownOpen = ref(
    route().current('produk.*') ||
    route().current('kategori.*') ||
    route().current('varian.*') ||
    route().current('finishing.*') ||
    route().current('voucher.*') ||
    route().current('sku.*')
);

const toggleProductDropdown = () => {
    isProductDropdownOpen.value = !isProductDropdownOpen.value;
};
</script>

<template>
    <aside
        class="fixed top-0 left-0 z-50 flex flex-col h-screen transition-all duration-300 border-r bg-base-100 border-base-300"
        :class="isCollapsed ? 'w-20' : 'w-64'"
    >
        <div class="flex items-center justify-center h-16 border-b shrink-0 border-base-300" :class="isCollapsed ? 'px-2' : 'px-6'">
            <span v-if="!isCollapsed" class="text-xl italic font-black tracking-tighter text-primary drop-shadow-sm">
                BIKIN<span class="text-base-content">CETAK</span>
            </span>
            <span v-else class="text-2xl italic font-black tracking-tighter text-primary drop-shadow-sm">
                B<span class="text-base-content">C</span>
            </span>
        </div>

        <nav class="flex-1 py-6 space-y-2 overflow-y-auto" :class="isCollapsed ? 'px-2' : 'px-4'">
            <p v-if="!isCollapsed" class="text-[10px] font-black text-base-content/30 uppercase tracking-widest ml-3 mb-4">Navigasi Utama</p>

            <Link :href="route('dashboard')"
                class="flex items-center py-3 text-sm font-bold transition-all duration-300 rounded-xl group"
                :class="{
                    'bg-primary text-white shadow-lg shadow-primary/30': route().current('dashboard'),
                    'text-base-content/70 hover:bg-base-200 hover:text-base-content': !route().current('dashboard'),
                    'translate-x-1': !isCollapsed && route().current('dashboard'),
                    'hover:translate-x-1': !isCollapsed && !route().current('dashboard'),
                    'justify-center px-0': isCollapsed,
                    'px-4': !isCollapsed
                }"
                :title="isCollapsed ? 'Dashboard' : ''"
            >
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span v-if="!isCollapsed">Dashboard</span>
            </Link>

            <Link v-if="$can('akun')" :href="route('akun.index')"
                class="flex items-center py-3 text-sm font-bold transition-all duration-300 rounded-xl"
                :class="{
                    'bg-primary text-white shadow-lg shadow-primary/30': route().current('akun.*'),
                    'text-base-content/70 hover:bg-base-200 hover:text-base-content': !route().current('akun.*'),
                    'translate-x-1': !isCollapsed && route().current('akun.*'),
                    'hover:translate-x-1': !isCollapsed && !route().current('akun.*'),
                    'justify-center px-0': isCollapsed,
                    'px-4': !isCollapsed
                }"
                :title="isCollapsed ? 'Data Akun COA' : ''"
            >
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span v-if="!isCollapsed">Data Akun COA</span>
            </Link>

            <Link v-if="$can('customer')" :href="route('customer.index')"
                class="flex items-center py-3 text-sm font-bold transition-all duration-300 rounded-xl"
                :class="{
                    'bg-primary text-white shadow-lg shadow-primary/30': route().current('customer.*'),
                    'text-base-content/70 hover:bg-base-200 hover:text-base-content': !route().current('customer.*'),
                    'translate-x-1': !isCollapsed && route().current('customer.*'),
                    'hover:translate-x-1': !isCollapsed && !route().current('customer.*'),
                    'justify-center px-0': isCollapsed,
                    'px-4': !isCollapsed
                }"
                :title="isCollapsed ? 'Data Customer' : ''"
            >
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span v-if="!isCollapsed">Data Customer</span>
            </Link>

            <Link v-if="$can('staf')" :href="route('staf.index')"
                class="flex items-center py-3 text-sm font-bold transition-all duration-300 rounded-xl"
                :class="{
                    'bg-primary text-white shadow-lg shadow-primary/30': route().current('staf.*'),
                    'text-base-content/70 hover:bg-base-200 hover:text-base-content': !route().current('staf.*'),
                    'translate-x-1': !isCollapsed && route().current('staf.*'),
                    'hover:translate-x-1': !isCollapsed && !route().current('staf.*'),
                    'justify-center px-0': isCollapsed,
                    'px-4': !isCollapsed
                }"
                :title="isCollapsed ? 'Data Staf' : ''"
            >
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span v-if="!isCollapsed">Data Staf</span>
            </Link>

            <div v-if="$can('kategori') || $can('produk') || $can('varian') || $can('produk-sku') || $can('finishing') || $can('voucher')" class="space-y-1">
                <button @click="toggleProductDropdown"
                    class="flex items-center w-full py-3 text-sm font-bold transition-all duration-300 rounded-xl group"
                    :class="{
                        'bg-primary text-white shadow-lg shadow-primary/30': route().current('produk.*') || route().current('finishing.*') || route().current('kategori.*') || route().current('varian.*') || route().current('voucher.*') || route().current('sku.*'),
                        'text-base-content/70 hover:bg-base-200 hover:text-base-content': !(route().current('produk.*') || route().current('finishing.*') || route().current('kategori.*') || route().current('varian.*') || route().current('voucher.*') || route().current('sku.*')),
                        'translate-x-1': !isCollapsed && (route().current('produk.*') || route().current('finishing.*') || route().current('kategori.*') || route().current('varian.*') || route().current('voucher.*') || route().current('sku.*')),
                        'hover:translate-x-1': !isCollapsed && !(route().current('produk.*') || route().current('finishing.*') || route().current('kategori.*') || route().current('varian.*') || route().current('voucher.*') || route().current('sku.*')),
                        'justify-center px-0': isCollapsed,
                        'justify-between px-4': !isCollapsed
                    }"
                    :title="isCollapsed ? 'Data Produk' : ''"
                >
                    <div class="flex items-center" :class="isCollapsed && 'justify-center w-full'">
                        <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <span v-if="!isCollapsed">Data Produk</span>
                    </div>
                    <svg v-if="!isCollapsed" class="w-4 h-4 transition-transform duration-300 shrink-0"
                        :class="{ 'rotate-180': isProductDropdownOpen }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div v-show="isProductDropdownOpen && !isCollapsed" class="pl-2 mt-1 ml-4 space-y-2 border-l-2 border-base-300/50">
                    <Link v-if="$can('kategori')" :href="route('kategori.index')"
                        class="flex items-center px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all duration-300 rounded-lg"
                        :class="{
                            'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1': route().current('kategori.*'),
                            'text-base-content/60 hover:bg-base-200 hover:text-base-content hover:translate-x-1': !route().current('kategori.*')
                        }"
                    >
                        Kategori
                    </Link>

                    <Link v-if="$can('produk')" :href="route('produk.index')"
                        class="flex items-center px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all duration-300 rounded-lg"
                        :class="{
                            'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1': route().current('produk.*') || route().current('sku.*'),
                            'text-base-content/60 hover:bg-base-200 hover:text-base-content hover:translate-x-1': !(route().current('produk.*') || route().current('sku.*'))
                        }"
                    >
                        Produk
                    </Link>

                    <Link v-if="$can('varian')" :href="route('varian.index')"
                        class="flex items-center px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all duration-300 rounded-lg"
                        :class="{
                            'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1': route().current('varian.*'),
                            'text-base-content/60 hover:bg-base-200 hover:text-base-content hover:translate-x-1': !route().current('varian.*')
                        }"
                    >
                        Varian
                    </Link>

                    <Link v-if="$can('finishing')" :href="route('finishing.index')"
                        class="flex items-center px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all duration-300 rounded-lg"
                        :class="{
                            'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1': route().current('finishing.*'),
                            'text-base-content/60 hover:bg-base-200 hover:text-base-content hover:translate-x-1': !route().current('finishing.*')
                        }"
                    >
                        Finishing
                    </Link>

                    <Link v-if="$can('voucher')" :href="route('voucher.index')"
                        class="flex items-center px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all duration-300 rounded-lg"
                        :class="{
                            'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1': route().current('voucher.*'),
                            'text-base-content/60 hover:bg-base-200 hover:text-base-content hover:translate-x-1': !route().current('voucher.*')
                        }"
                    >
                        Voucher
                    </Link>
                </div>
            </div>

            <Link v-if="$can('bahan-baku')" :href="route('bahan-baku.index')"
                class="flex items-center py-3 text-sm font-bold transition-all duration-300 rounded-xl"
                :class="{
                    'bg-primary text-white shadow-lg shadow-primary/30': route().current('bahan-baku.*'),
                    'text-base-content/70 hover:bg-base-200 hover:text-base-content': !route().current('bahan-baku.*'),
                    'translate-x-1': !isCollapsed && route().current('bahan-baku.*'),
                    'hover:translate-x-1': !isCollapsed && !route().current('bahan-baku.*'),
                    'justify-center px-0': isCollapsed,
                    'px-4': !isCollapsed
                }"
                :title="isCollapsed ? 'Bahan Baku' : ''"
            >
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span v-if="!isCollapsed">Bahan Baku</span>
            </Link>

            <Link v-if="$can('pembelian-bahan')" :href="route('pembelian-bahan.index')"
                class="flex items-center py-3 text-sm font-bold transition-all duration-300 rounded-xl"
                :class="{
                    'bg-primary text-white shadow-lg shadow-primary/30': route().current('pembelian-bahan.*'),
                    'text-base-content/70 hover:bg-base-200 hover:text-base-content': !route().current('pembelian-bahan.*'),
                    'translate-x-1': !isCollapsed && route().current('pembelian-bahan.*'),
                    'hover:translate-x-1': !isCollapsed && !route().current('pembelian-bahan.*'),
                    'justify-center px-0': isCollapsed,
                    'px-4': !isCollapsed
                }"
                :title="isCollapsed ? 'Pembelian Bahan' : ''"
            >
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span v-if="!isCollapsed">Pembelian Bahan</span>
            </Link>

            <Link v-if="$can('pesan')" :href="route('pesan.index')"
                class="flex items-center py-3 text-sm font-bold transition-all duration-300 rounded-xl"
                :class="{
                    'bg-primary text-white shadow-lg shadow-primary/30': route().current('pesan.*'),
                    'text-base-content/70 hover:bg-base-200 hover:text-base-content': !route().current('pesan.*'),
                    'translate-x-1': !isCollapsed && route().current('pesan.*'),
                    'hover:translate-x-1': !isCollapsed && !route().current('pesan.*'),
                    'justify-center px-0': isCollapsed,
                    'px-4': !isCollapsed
                }"
                :title="isCollapsed ? 'Pesanan' : ''"
            >
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span v-if="!isCollapsed">Pesanan</span>
            </Link>

            <Link v-if="$can('pembayaran')" :href="route('pembayaran.index')"
                class="flex items-center py-3 text-sm font-bold transition-all duration-300 rounded-xl"
                :class="{
                    'bg-primary text-white shadow-lg shadow-primary/30': route().current('pembayaran.*'),
                    'text-base-content/70 hover:bg-base-200 hover:text-base-content': !route().current('pembayaran.*'),
                    'translate-x-1': !isCollapsed && route().current('pembayaran.*'),
                    'hover:translate-x-1': !isCollapsed && !route().current('pembayaran.*'),
                    'justify-center px-0': isCollapsed,
                    'px-4': !isCollapsed
                }"
                :title="isCollapsed ? 'Pembayaran' : ''"
            >
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span v-if="!isCollapsed">Pembayaran</span>
            </Link>

            <Link v-if="$can('hak-akses')" :href="route('hak-akses.index')"
                class="flex items-center py-3 text-sm font-bold transition-all duration-300 rounded-xl"
                :class="{
                    'bg-primary text-white shadow-lg shadow-primary/30': route().current('hak-akses.*'),
                    'text-base-content/70 hover:bg-base-200 hover:text-base-content': !route().current('hak-akses.*'),
                    'translate-x-1': !isCollapsed && route().current('hak-akses.*'),
                    'hover:translate-x-1': !isCollapsed && !route().current('hak-akses.*'),
                    'justify-center px-0': isCollapsed,
                    'px-4': !isCollapsed
                }"
                :title="isCollapsed ? 'Hak Akses' : ''"
            >
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span v-if="!isCollapsed">Hak Akses</span>
            </Link>
        </nav>

        <div class="p-4 border-t border-base-300 bg-base-100 shrink-0" :class="isCollapsed ? 'flex justify-center' : ''">
            <div class="flex items-center px-3 py-3 border bg-base-200 rounded-2xl border-base-300/50" :class="isCollapsed ? 'justify-center p-2' : ''">
                <div class="avatar placeholder" :class="!isCollapsed && 'mr-3'">
                    <div class="flex items-center justify-center font-black text-white rounded-lg shadow-sm bg-primary w-9 h-9">
                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                    </div>
                </div>
                <div v-if="!isCollapsed" class="flex-1 min-w-0">
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
