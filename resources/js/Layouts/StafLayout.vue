<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import Dropdown from '@/Components/Dropdown.vue';
import CustomAlert from '@/Components/CustomAlert.vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { watch, onMounted, ref, computed, onUnmounted } from 'vue';
import { alertStore } from '@/Utils/alertStore';
import CustomInputSearch from '@/Components/Form/CustomInputSearch.vue';

const page = usePage();

const isVendor = computed(() => page.props.auth?.user?.role === 'vendor');
const stafRole = computed(() => page.props.auth?.user?.staf?.id_role_staf);

watch(() => page.props.flash, (flash) => {
    if (flash?.success) alertStore.show(flash.success, 'success');
    if (flash?.error) alertStore.show(flash.error, 'error');
}, { deep: true });

const notifikasiBanyak = ref([]);

const soundKasir = new Audio('/sounds/notif kasir.mp3');
const soundProduksi = new Audio('/sounds/notif produksi.mp3');

let originalTitle = '';

const isSidebarCollapsed = ref(localStorage.getItem('sidebar_collapsed') === 'true');

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    localStorage.setItem('sidebar_collapsed', isSidebarCollapsed.value);
};

const playNotificationSound = (tipe) => {
    const soundToPlay = tipe === 'pesanan' ? soundKasir : soundProduksi;

    soundToPlay.currentTime = 0;
    soundToPlay.play().catch(err => {
        console.warn('Suara ke-blokir, user belum berinteraksi dengan halaman.', err);
    });
};

const tambahNotifKeLayar = (kodePesanan, judul, pesan, tipe) => {
    // ADMIN: Mute sound. Selain admin (Kasir/Produksi) baru di-play.
    if (stafRole.value !== 'ROLE-STAF-ADMIN') {
        playNotificationSound(tipe);
    }

    if (document.hidden) {
        document.title = `(1) 🔔 ${judul}`;
    }

    const idNotif = Date.now();
    notifikasiBanyak.value.push({
        id: idNotif,
        tipe: tipe,
        judul: judul,
        pesan: `${pesan}: ${kodePesanan}`,
    });

    setTimeout(() => {
        notifikasiBanyak.value = notifikasiBanyak.value.filter(n => n.id !== idNotif);
    }, 5000);

    router.reload({ preserveScroll: true, preserveState: true });
};

onMounted(() => {
    originalTitle = document.title;

    if (page.props.flash?.success) alertStore.show(page.props.flash.success, 'success');

    window.addEventListener('focus', () => {
        document.title = originalTitle;
    });

    if (window.Echo) {
        // Bersihin channel yang nyangkut dari sesi sebelumnya (mencegah ghost listener)
        window.Echo.leave('pesanan-channel');
        window.Echo.leave('produksi-channel');

        // PASTIKAN BUKAN VENDOR
        if (!isVendor.value) {

            // KASIR & ADMIN -> Dengerin channel pesanan
            if (['ROLE-STAF-ADMIN', 'ROLE-STAF-KASIR'].includes(stafRole.value)) {
                window.Echo.channel('pesanan-channel')
                    .listen('.pesanan.baru', (e) => {
                        const kodePesanan = e?.pesan?.id_pesan || e?.pesanan?.id_pesan || 'Cek Dashboard';
                        tambahNotifKeLayar(kodePesanan, 'Pesanan Baru!', 'Ada order masuk baru', 'pesanan');
                    });
            }

            // PRODUKSI & ADMIN -> Dengerin channel produksi
            if (['ROLE-STAF-ADMIN', 'ROLE-STAF-PRODUKSI'].includes(stafRole.value)) {
                window.Echo.channel('produksi-channel')
                    .listen('.produksi.baru', (e) => {
                        const kodePesanan = e?.pesan?.id_pesan || e?.pesanan?.id_pesan || 'Cek Dashboard';
                        tambahNotifKeLayar(kodePesanan, 'Antrean Produksi!', 'Pesanan Lunas/DP, siap dikerjakan', 'produksi');
                    });
            }

        }
    } else {
        console.error("Waduh, window.Echo belum aktif! Cek file bootstrap.js lu.");
    }
});

// Bersihkan listener saat layout ini di-destroy / unmount (contoh saat pindah ke halaman vendor)
onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave('pesanan-channel');
        window.Echo.leave('produksi-channel');
    }
});

const globalSearchKey = ref(new URLSearchParams(window.location.search).get('key') || '');

const doGlobalSearch = () => {
    if (!globalSearchKey.value.trim()) return;
    router.get('/search', { key: globalSearchKey.value });
};
</script>

<template>
    <div class="flex min-h-screen bg-base-200 text-base-content selection:bg-primary selection:text-white">

        <Sidebar v-if="!isVendor" :isCollapsed="isSidebarCollapsed" />

        <div class="flex flex-col flex-1 min-h-screen transition-all duration-300" :class="!isVendor ? (isSidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64') : ''">

            <nav class="sticky top-0 flex items-center h-16 px-4 transition-colors border-b shadow-sm z-60 lg:px-8 bg-base-100/90 backdrop-blur-md border-base-300">
                <div class="flex items-center gap-3">

                    <button v-if="!isVendor" @click="toggleSidebar" class="hidden lg:flex btn btn-ghost btn-sm btn-circle hover:bg-base-200 ring-1 ring-base-300/50">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path v-if="!isSidebarCollapsed" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M12 17.25h8.25" />
                        </svg>
                    </button>

                    <div v-else class="flex items-center ml-1">
                        <span class="text-xl italic font-black tracking-tighter text-primary drop-shadow-sm">
                            BIKIN<span class="text-base-content">CETAK</span>
                        </span>
                    </div>

                    <div v-if="!isVendor" class="flex-1 hidden max-w-md ml-2 md:block">
                        <form @submit.prevent="doGlobalSearch">
                            <CustomInputSearch
                                v-model="globalSearchKey"
                                placeholder="Cari data global..."
                            />
                        </form>
                    </div>
                </div>

                <div class="flex items-center ml-auto space-x-2 lg:space-x-4">
                    <ThemeSwitcher />
                    <div class="h-6 w-0.5 bg-base-300 mx-1"></div>

                    <Dropdown align="right" width="56">
                        <template #trigger>
                            <button type="button" class="flex items-center gap-3 px-2 py-1.5 font-bold normal-case transition-all btn btn-ghost btn-sm rounded-xl hover:bg-base-200 ring-1 ring-base-300/50">
                                <div class="shadow-sm avatar placeholder">
                                    <div class="flex items-center justify-center w-8 h-8 text-white rounded-lg bg-primary">
                                        <span class="text-sm font-black">{{ $page.props.auth.user.name.charAt(0).toUpperCase() }}</span>
                                    </div>
                                </div>
                                <div class="hidden sm:flex sm:flex-col sm:items-start sm:justify-center">
                                    <span class="text-xs leading-none opacity-80">{{ $page.props.auth.user.name }}</span>
                                    <span class="text-[9px] leading-none text-primary mt-1 uppercase tracking-wider">{{ $page.props.auth.user.role }}</span>
                                </div>
                                <svg class="hidden w-4 h-4 ml-1 opacity-50 sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                        </template>

                        <template #content>
                            <div class="px-3 py-2 text-[10px] font-black text-base-content/40 uppercase tracking-widest border-b border-base-200 mb-1">
                                Menu Pengguna
                            </div>

                            <Link :href="route('profil.edit')" class="flex items-center gap-3 w-full px-3 py-2.5 text-sm font-bold text-base-content/80 rounded-lg hover:bg-base-200 hover:text-primary transition-colors">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Profil Saya
                            </Link>

                            <Link :href="route('logout')" method="post" as="button" class="flex items-center gap-3 w-full px-3 py-2.5 mt-1 text-sm font-bold text-error rounded-lg hover:bg-error/10 transition-colors">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Keluar Sistem
                            </Link>
                        </template>
                    </Dropdown>
                </div>
            </nav>

            <CustomAlert />

            <header v-if="$slots.header" class="px-4 py-8 duration-500 lg:px-8 animate-in fade-in slide-in-from-top-4">
                <div class="flex items-center gap-4">
                    <div class="w-2 h-10 bg-primary rounded-full shadow-[0_0_15px_rgba(56,133,248,0.4)]"></div>
                    <slot name="header" />
                </div>
            </header>

            <main class="flex-1 px-4 pb-12 lg:px-8">
                <slot />
            </main>
        </div>

        <div class="toast toast-end toast-bottom" style="z-index: 99999;">
            <div v-for="notif in notifikasiBanyak" :key="notif.id"
                class="border-l-4 shadow-lg alert bg-base-100 animate-bounce"
                :class="notif.tipe === 'produksi' ? 'border-warning' : 'border-success'">

                <!-- Ikon Hijau untuk Pesanan Baru -->
                <svg v-if="notif.tipe === 'pesanan'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-6 h-6 stroke-success shrink-0"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>

                <!-- Ikon Kuning/Warning untuk Produksi Baru -->
                <svg v-else-if="notif.tipe === 'produksi'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-6 h-6 stroke-warning shrink-0"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.83-5.83M15.17 11.42L21 5.58A2.652 2.652 0 0017.25 1.83l-5.83 5.83m-3.84 3.84L1.83 17.25A2.652 2.652 0 005.58 21l5.83-5.83m-3.84-3.84L1.83 5.58A2.652 2.652 0 015.58 1.83l5.83 5.83"></path></svg>

                <div>
                    <h3 class="text-sm font-bold" :class="notif.tipe === 'produksi' ? 'text-warning' : 'text-success'">
                        {{ notif.judul }}
                    </h3>
                    <div class="text-xs opacity-80">{{ notif.pesan }}</div>
                </div>
            </div>
        </div>
    </div>
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
