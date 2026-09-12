<script setup>
import { Head, Link } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import { LayoutGrid, Image as ImageIcon, Settings, Type } from 'lucide-vue-next';

const menus = [
    {
        title: 'Tampilan Kategori',
        description: 'Atur urutan, icon, dan sembunyikan kategori di halaman utama.',
        route: 'tampilan-web.kategori',
        icon: LayoutGrid,
        color: 'text-primary',
        bg: 'bg-primary/10'
    },
    {
        title: 'Banner Slider',
        description: 'Ganti gambar carousel/promo yang berjalan di beranda.',
        route: 'tampilan-web.banner',
        icon: ImageIcon,
        color: 'text-info',
        bg: 'bg-info/10'
    },
    {
        title: 'Pengaturan Umum',
        description: 'Ubah teks footer, nomor WhatsApp, email, dan link sosmed.',
        route: null,
        icon: Settings,
        color: 'text-warning',
        bg: 'bg-warning/10'
    },
    {
        title: 'Halaman Statis',
        description: 'Kelola isi halaman Tentang Kami, FAQ, Syarat & Ketentuan.',
        route: null,
        icon: Type,
        color: 'text-success',
        bg: 'bg-success/10'
    }
];
</script>

<template>
    <Head title="Pengaturan Tampilan Web" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Pengaturan Tampilan Web (CMS)
                    </h2>
                    <p class="mt-1 text-sm text-base-content/60">Kelola konten dan struktur halaman depan e-commerce.</p>
                </div>
            </div>
        </template>

        <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <template v-for="(menu, index) in menus" :key="index">
                    <!-- Jika route tersedia -->
                    <Link
                        v-if="menu.route"
                        :href="route(menu.route)"
                        class="p-6 transition-all border shadow-sm rounded-2xl bg-base-100 border-base-200 hover:border-primary hover:shadow-md group"
                    >
                        <div class="flex items-start gap-4">
                            <div class="p-3 rounded-xl shrink-0" :class="menu.bg">
                                <component :is="menu.icon" class="w-6 h-6" :class="menu.color" />
                            </div>
                            <div>
                                <h3 class="font-bold transition-colors text-base-content group-hover:text-primary">{{ menu.title }}</h3>
                                <p class="mt-1 text-xs leading-relaxed text-base-content/60">{{ menu.description }}</p>
                            </div>
                        </div>
                    </Link>

                    <!-- Jika route belum dibuat (Coming Soon) -->
                    <div v-else class="p-6 border border-dashed shadow-sm opacity-60 rounded-2xl bg-base-50 border-base-300">
                        <div class="flex items-start gap-4">
                            <div class="p-3 rounded-xl shrink-0 bg-base-200">
                                <component :is="menu.icon" class="w-6 h-6 text-base-content/40" />
                            </div>
                            <div>
                                <h3 class="font-bold text-base-content/70">{{ menu.title }}</h3>
                                <p class="mt-1 text-xs leading-relaxed text-base-content/50">{{ menu.description }}</p>
                                <span class="inline-block px-2 py-0.5 mt-3 text-[9px] font-black tracking-widest uppercase rounded bg-base-300 text-base-content/50">Segera Hadir</span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </StafLayout>
</template>
