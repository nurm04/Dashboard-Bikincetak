<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import Dropdown from '@/Components/Dropdown.vue';
import CustomAlert from '@/Components/CustomAlert.vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { watch, onMounted, ref } from 'vue';
import { alertStore } from '@/Utils/alertStore';
import CustomInputSearch from '@/Components/CustomInputSearch.vue';

const page = usePage();

watch(() => page.props.flash, (flash) => {
    if (flash?.success) alertStore.show(flash.success, 'success');
    if (flash?.error) alertStore.show(flash.error, 'error');
}, { deep: true });

const notifikasiBanyak = ref([]);

const notifSound = new Audio('/sounds/notif.mp3');

const playNotificationSound = () => {
    notifSound.currentTime = 0;

    notifSound.play().catch(err => {
        console.warn('Browser nge-blokir suara nih! Lu harus interaksi (klik apapun) di halaman ini dulu.', err);
    });
};

onMounted(() => {
    if (page.props.flash?.success) alertStore.show(page.props.flash.success, 'success');

    console.log("Status Echo:", window.Echo ? "Nyala" : "Mati");

    if (window.Echo) {
        window.Echo.channel('pesanan-channel')
            .listen('.pesanan.baru', (e) => {
                console.log("Sinyal masuk bro!", e);

                playNotificationSound();

                const idNotif = Date.now();
                notifikasiBanyak.value.push({
                    id: idNotif,
                    pesan: `Ada order masuk baru: ${e.pesan.id_pesan}`,
                });

                setTimeout(() => {
                    notifikasiBanyak.value = notifikasiBanyak.value.filter(n => n.id !== idNotif);
                }, 5000);

                router.reload();
            });
    } else {
        console.error("Waduh, window.Echo belum aktif! Cek file bootstrap.js lu.");
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

        <Sidebar />

        <div class="flex flex-col flex-1 min-h-screen lg:ml-64">

            <nav class="sticky top-0 flex items-center justify-between h-16 px-4 transition-colors border-b shadow-sm z-60 lg:px-8 bg-base-100/90 backdrop-blur-md border-base-300">

                <div class="flex-1 hidden max-w-md md:block">
                    <form @submit.prevent="doGlobalSearch">
                        <CustomInputSearch
                            v-model="globalSearchKey"
                            placeholder="Cari data global..."
                        />
                    </form>
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
                                    <span class="text-[9px] leading-none text-primary mt-1 uppercase tracking-wider">Administrator</span>
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
        <div class="toast toast-end toast-bottom z-9999">
            <div v-for="notif in notifikasiBanyak" :key="notif.id" class="border-l-4 shadow-lg alert bg-base-100 border-success animate-bounce">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-6 h-6 stroke-success shrink-0"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <h3 class="text-sm font-bold">Pesanan Baru!</h3>
                    <div class="text-xs opacity-80">{{ notif.pesan }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
