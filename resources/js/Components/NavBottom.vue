<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const userRole = computed(() => {
    const roleStaf = page.props.auth?.user?.staf?.id_role_staf || '';
    const roleUser = page.props.auth?.user?.role || '';

    const combinedRole = `${roleStaf} ${roleUser}`.toLowerCase();

    if (combinedRole.includes('admin')) return 'admin';
    if (combinedRole.includes('produksi')) return 'produksi';
    if (combinedRole.includes('kasir')) return 'kasir';

    return 'admin';
});

const isActive = (routeName) => {
    if (routeName === 'dashboard') {
        return route().current('dashboard');
    }
    return route().current(routeName + '.*') || route().current(routeName);
};
</script>

<template>
    <nav class="fixed bottom-0 left-0 right-0 z-50 flex items-center justify-between w-full h-16 px-2 border-t md:hidden bg-base-100 border-base-300"
         style="padding-bottom: env(safe-area-inset-bottom);">

        <!-- ========================================== -->
        <!-- MENU ADMIN -->
        <!-- [home, pesanan, PRODUK, produksi, customer] -->
        <!-- ========================================== -->
        <template v-if="userRole === 'admin'">
            <Link :href="route('dashboard')" class="flex flex-col items-center justify-center w-1/5 gap-1 transition-all" :class="isActive('dashboard') ? 'text-primary' : 'text-base-content/40 hover:text-base-content/70'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="text-[9px] font-black tracking-wide">Home</span>
            </Link>

            <Link :href="route('pesan.index')" class="flex flex-col items-center justify-center w-1/5 gap-1 transition-all" :class="isActive('pesan') ? 'text-primary' : 'text-base-content/40 hover:text-base-content/70'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path></svg>
                <span class="text-[9px] font-black tracking-wide">Pesanan</span>
            </Link>

            <!-- MENU TENGAH (UTAMA): PRODUK -->
            <div class="relative flex flex-col items-center justify-center w-1/5 h-full">
                <Link :href="route('produk.index')"
                      class="absolute flex items-center justify-center w-14 h-14 text-white transition-all duration-300 rounded-full shadow-lg -top-5 bg-primary shadow-primary/40 border-4 border-base-100 active:scale-95"
                      :class="isActive('produk') || isActive('sku') ? '-translate-y-0.5 shadow-primary/60 scale-105' : 'hover:scale-105'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </Link>
                <span class="text-[9px] font-black mt-8 tracking-wide" :class="isActive('produk') || isActive('sku') ? 'text-primary' : 'text-base-content/40'">Produk</span>
            </div>

            <Link :href="route('produksi.index')" class="flex flex-col items-center justify-center w-1/5 gap-1 transition-all" :class="isActive('produksi') ? 'text-primary' : 'text-base-content/40 hover:text-base-content/70'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span class="text-[9px] font-black tracking-wide">Produksi</span>
            </Link>

            <Link :href="route('customer.index')" class="flex flex-col items-center justify-center w-1/5 gap-1 transition-all" :class="isActive('customer') ? 'text-primary' : 'text-base-content/40 hover:text-base-content/70'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"></path></svg>
                <span class="text-[9px] font-black tracking-wide">Customer</span>
            </Link>
        </template>


        <!-- ========================================== -->
        <!-- MENU PRODUKSI -->
        <!-- [home, bahan baku, PRODUKSI, pesanan, pembelian] -->
        <!-- ========================================== -->
        <template v-else-if="userRole === 'produksi'">
            <Link :href="route('dashboard')" class="flex flex-col items-center justify-center w-1/5 gap-1 transition-all" :class="isActive('dashboard') ? 'text-primary' : 'text-base-content/40 hover:text-base-content/70'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="text-[9px] font-black tracking-wide">Home</span>
            </Link>

            <Link :href="route('bahan-baku.index')" class="flex flex-col items-center justify-center w-1/5 gap-1 transition-all" :class="isActive('bahan-baku') ? 'text-primary' : 'text-base-content/40 hover:text-base-content/70'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v.878m13.5-3A2.25 2.25 0 0119.5 9v.878m0 0a2.246 2.246 0 00-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0121 12v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6c0-.98.626-1.813 1.5-2.122"></path></svg>
                <span class="text-[9px] font-black tracking-wide">Bahan</span>
            </Link>

            <!-- MENU TENGAH (UTAMA): PRODUKSI -->
            <div class="relative flex flex-col items-center justify-center w-1/5 h-full">
                <Link :href="route('produksi.index')"
                      class="absolute flex items-center justify-center w-14 h-14 text-white transition-all duration-300 rounded-full shadow-lg -top-5 bg-primary shadow-primary/40 border-4 border-base-100 active:scale-95"
                      :class="isActive('produksi') ? '-translate-y-0.5 shadow-primary/60 scale-105' : 'hover:scale-105'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </Link>
                <span class="text-[9px] font-black mt-8 tracking-wide" :class="isActive('produksi') ? 'text-primary' : 'text-base-content/40'">Produksi</span>
            </div>

            <Link :href="route('pesan.index')" class="flex flex-col items-center justify-center w-1/5 gap-1 transition-all" :class="isActive('pesan') ? 'text-primary' : 'text-base-content/40 hover:text-base-content/70'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path></svg>
                <span class="text-[9px] font-black tracking-wide">Pesanan</span>
            </Link>

            <Link :href="route('pembelian-bahan.index')" class="flex flex-col items-center justify-center w-1/5 gap-1 transition-all" :class="isActive('pembelian-bahan') ? 'text-primary' : 'text-base-content/40 hover:text-base-content/70'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"></path></svg>
                <span class="text-[9px] font-black tracking-wide">Pembelian</span>
            </Link>
        </template>


        <!-- ========================================== -->
        <!-- MENU KASIR -->
        <!-- [home, customer, PESANAN, pembayaran, voucher] -->
        <!-- ========================================== -->
        <template v-else-if="userRole === 'kasir'">
            <Link :href="route('dashboard')" class="flex flex-col items-center justify-center w-1/5 gap-1 transition-all" :class="isActive('dashboard') ? 'text-primary' : 'text-base-content/40 hover:text-base-content/70'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="text-[9px] font-black tracking-wide">Home</span>
            </Link>

            <Link :href="route('customer.index')" class="flex flex-col items-center justify-center w-1/5 gap-1 transition-all" :class="isActive('customer') ? 'text-primary' : 'text-base-content/40 hover:text-base-content/70'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"></path></svg>
                <span class="text-[9px] font-black tracking-wide">Customer</span>
            </Link>

            <!-- MENU TENGAH (UTAMA): PESANAN -->
            <div class="relative flex flex-col items-center justify-center w-1/5 h-full">
                <Link :href="route('pesan.index')"
                      class="absolute flex items-center justify-center w-14 h-14 text-white transition-all duration-300 rounded-full shadow-lg -top-5 bg-primary shadow-primary/40 border-4 border-base-100 active:scale-95"
                      :class="isActive('pesan') ? '-translate-y-0.5 shadow-primary/60 scale-105' : 'hover:scale-105'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path></svg>
                </Link>
                <span class="text-[9px] font-black mt-8 tracking-wide" :class="isActive('pesan') ? 'text-primary' : 'text-base-content/40'">Pesanan</span>
            </div>

            <Link :href="route('pembayaran.index')" class="flex flex-col items-center justify-center w-1/5 gap-1 transition-all" :class="isActive('pembayaran') ? 'text-primary' : 'text-base-content/40 hover:text-base-content/70'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"></path></svg>
                <span class="text-[9px] font-black tracking-wide">Kasir</span>
            </Link>

            <Link :href="route('voucher.index')" class="flex flex-col items-center justify-center w-1/5 gap-1 transition-all" :class="isActive('voucher') ? 'text-primary' : 'text-base-content/40 hover:text-base-content/70'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"></path></svg>
                <span class="text-[9px] font-black tracking-wide">Voucher</span>
            </Link>
        </template>

    </nav>
</template>
