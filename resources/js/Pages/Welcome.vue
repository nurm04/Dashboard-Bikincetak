<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Welcome" />
    <GuestLayout>
        <div class="w-full max-w-lg p-10 text-center border shadow-2xl bg-base-100 rounded-3xl border-base-content/5">
            <div class="flex justify-center mb-6">
                <ApplicationLogo class="w-24 h-24 drop-shadow-md text-primary" />
            </div>

            <h1 class="mb-2 text-3xl font-black tracking-widest uppercase text-primary">
                Bikin Cetak
            </h1>
            <p class="mb-10 text-[10px] font-bold tracking-widest uppercase text-base-content/60">
                Sistem Manajemen ERP & Kasir Percetakan
            </p>

            <nav v-if="canLogin" class="flex flex-col gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="w-full h-14 shadow-lg btn btn-primary rounded-xl shadow-primary/30 hover:shadow-primary/50"
                >
                    <span class="text-xs font-black tracking-widest uppercase">Masuk ke Dashboard</span>
                </Link>

                <template v-else>
                    <Link
                        :href="route('login')"
                        class="w-full h-14 shadow-lg btn btn-primary rounded-xl shadow-primary/30 hover:shadow-primary/50"
                    >
                        <span class="text-xs font-black tracking-widest uppercase">Log in Sistem</span>
                    </Link>
                </template>
            </nav>

            <div class="pt-6 mt-10 border-t border-base-content/10">
                <p class="text-[10px] font-bold tracking-widest uppercase text-base-content/30">
                    Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                </p>
            </div>

        </div>
    </GuestLayout>

</template>
