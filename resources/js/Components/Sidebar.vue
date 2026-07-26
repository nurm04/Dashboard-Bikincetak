<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    isCollapsed: {
        type: Boolean,
        default: false
    }
});

// Dropdown Produk
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

// Dropdown Pengaturan (BARU)
const isSettingsDropdownOpen = ref(
    route().current('hak-akses.*') ||
    route().current('file-manage.*') // Pastikan route name lu 'file-manage.index'
);

const toggleSettingsDropdown = () => {
    isSettingsDropdownOpen.value = !isSettingsDropdownOpen.value;
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
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path></svg>
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
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"></path></svg>
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
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"></path></svg>
                <span v-if="!isCollapsed">Data Staf</span>
            </Link>

            <Link v-if="$can('vendor')" :href="route('vendor.index')"
                class="flex items-center py-3 text-sm font-bold transition-all duration-300 rounded-xl"
                :class="{
                    'bg-primary text-white shadow-lg shadow-primary/30': route().current('vendor.*'),
                    'text-base-content/70 hover:bg-base-200 hover:text-base-content': !route().current('vendor.*'),
                    'translate-x-1': !isCollapsed && route().current('vendor.*'),
                    'hover:translate-x-1': !isCollapsed && !route().current('vendor.*'),
                    'justify-center px-0': isCollapsed,
                    'px-4': !isCollapsed
                }"
                :title="isCollapsed ? 'Data Vendor' : ''"
            >
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"></path></svg>
                <span v-if="!isCollapsed">Data Vendor</span>
            </Link>

            <!-- DROPDOWN PRODUK -->
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
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v.878m13.5-3A2.25 2.25 0 0119.5 9v.878m0 0a2.246 2.246 0 00-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0121 12v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6c0-.98.626-1.813 1.5-2.122"></path></svg>
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
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"></path></svg>
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
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path></svg>
                <span v-if="!isCollapsed">Pesanan</span>
            </Link>

            <Link v-if="$can('produksi')" :href="route('produksi.index')"
                class="flex items-center py-3 text-sm font-bold transition-all duration-300 rounded-xl"
                :class="{
                    'bg-primary text-white shadow-lg shadow-primary/30': route().current('produksi.*'),
                    'text-base-content/70 hover:bg-base-200 hover:text-base-content': !route().current('produksi.*'),
                    'translate-x-1': !isCollapsed && route().current('produksi.*'),
                    'hover:translate-x-1': !isCollapsed && !route().current('produksi.*'),
                    'justify-center px-0': isCollapsed,
                    'px-4': !isCollapsed
                }"
                :title="isCollapsed ? 'Produksi' : ''"
            >
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span v-if="!isCollapsed">Produksi</span>
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
                <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"></path></svg>
                <span v-if="!isCollapsed">Pembayaran</span>
            </Link>

            <!-- DROPDOWN PENGATURAN -->
            <div v-if="$can('hak-akses') || $can('file-manage')" class="space-y-1">
                <button @click="toggleSettingsDropdown"
                    class="flex items-center w-full py-3 text-sm font-bold transition-all duration-300 rounded-xl group"
                    :class="{
                        'bg-primary text-white shadow-lg shadow-primary/30': route().current('hak-akses.*') || route().current('file-manage.*'),
                        'text-base-content/70 hover:bg-base-200 hover:text-base-content': !(route().current('hak-akses.*') || route().current('file-manage.*')),
                        'translate-x-1': !isCollapsed && (route().current('hak-akses.*') || route().current('file-manage.*')),
                        'hover:translate-x-1': !isCollapsed && !(route().current('hak-akses.*') || route().current('file-manage.*')),
                        'justify-center px-0': isCollapsed,
                        'justify-between px-4': !isCollapsed
                    }"
                    :title="isCollapsed ? 'Pengaturan' : ''"
                >
                    <div class="flex items-center" :class="isCollapsed && 'justify-center w-full'">
                        <!-- Icon Adjustments untuk Settings -->
                        <svg class="w-5 h-5 shrink-0" :class="!isCollapsed && 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        <span v-if="!isCollapsed">Pengaturan</span>
                    </div>
                    <svg v-if="!isCollapsed" class="w-4 h-4 transition-transform duration-300 shrink-0"
                        :class="{ 'rotate-180': isSettingsDropdownOpen }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div v-show="isSettingsDropdownOpen && !isCollapsed" class="pl-2 mt-1 ml-4 space-y-2 border-l-2 border-base-300/50">
                    <Link v-if="$can('hak-akses')" :href="route('hak-akses.index')"
                        class="flex items-center px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all duration-300 rounded-lg"
                        :class="{
                            'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1': route().current('hak-akses.*'),
                            'text-base-content/60 hover:bg-base-200 hover:text-base-content hover:translate-x-1': !route().current('hak-akses.*')
                        }"
                    >
                        Hak Akses
                    </Link>

                    <Link v-if="$can('file-manage')" :href="route('file-manage.index')"
                        class="flex items-center px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all duration-300 rounded-lg"
                        :class="{
                            'bg-primary text-white shadow-lg shadow-primary/30 translate-x-1': route().current('file-manage.*'),
                            'text-base-content/60 hover:bg-base-200 hover:text-base-content hover:translate-x-1': !route().current('file-manage.*')
                        }"
                    >
                        Storage File
                    </Link>
                </div>
            </div>

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
