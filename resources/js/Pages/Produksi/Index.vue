<script setup>
import { ref, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Inbox } from 'lucide-vue-next';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';

// Import Komponen Tab
import TabMenungguAlokasi from './Partials/TabMenungguAlokasi.vue';
import TabPengerjaan from './Partials/TabPengerjaan.vue';
import TabPengantaran from './Partials/TabPengantaran.vue';

const page = usePage();
const currentUser = page.props.auth?.user;

const props = defineProps({
    pesananProduksi: Array,
    vendors: Array,
    stafs: Array,
    currentVendorId: String,
});

const activeTab = ref('alokasi');

// Logic untuk mengecek apakah semua item sudah selesai
const isReadyToShip = (pesanan) => {
    if (!pesanan.pesanan_item || pesanan.pesanan_item.length === 0) return false;
    let hasSchedule = false;
    let allCompleted = true;

    pesanan.pesanan_item.forEach(item => {
        if (!item.pesanan_item_produksi || item.pesanan_item_produksi.length === 0) {
            allCompleted = false;
        } else {
            hasSchedule = true;
            item.pesanan_item_produksi.forEach(sch => {
                if (sch.status_pengerjaan !== 'selesai') allCompleted = false;
            });
        }
    });

    return hasSchedule && allCompleted;
};

// Filter Data Berdasarkan Status & Kesiapan
const listAlokasi = computed(() =>
    props.pesananProduksi.filter(p => p.status_operasional === 'menunggu_diproses')
);

// Tab Pengerjaan: Status proses_pengerjaan TAPI BELUM siap kirim
const listPengerjaan = computed(() =>
    props.pesananProduksi.filter(p => p.status_operasional === 'proses_pengerjaan' && !isReadyToShip(p))
);

// Tab Pengantaran: Status proses_pengerjaan DAN SUDAH siap kirim (Semua item selesai)
const listPengantaran = computed(() =>
    props.pesananProduksi.filter(p => p.status_operasional === 'proses_pengerjaan' && isReadyToShip(p))
);
</script>

<template>
    <Head title="Dashboard Produksi" />
    <StafLayout>
        <template #header>
            <div class="flex flex-col justify-between w-full gap-4 md:flex-row md:items-center">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Produksi & Alokasi
                    </h2>
                    <p class="mt-1 text-sm text-base-content/60">Pantau antrean, pecah tugas ke vendor, dan perbarui progres.</p>
                </div>

                <CustomButton type="link" :href="route('produksi.histori')" class="w-full md:w-auto shrink-0" block>
                    <template #icon>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </template>
                    Histori Produksi
                </CustomButton>
            </div>
        </template>

        <div class="px-4 py-8 mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">

            <!-- REVISI TAB BAR: Ditambahkan flex-nowrap dan shrink-0 pada button -->
            <div class="flex gap-4 pb-px mb-2 overflow-x-auto border-b flex-nowrap border-base-300 sm:gap-6 custom-scrollbar">
                <button
                    @click="activeTab = 'alokasi'"
                    :class="[
                        'pb-3 text-sm font-bold tracking-wide transition-colors border-b-2 whitespace-nowrap px-4 sm:px-0 shrink-0',
                        activeTab === 'alokasi'
                            ? 'text-primary border-primary'
                            : 'text-base-content/50 border-transparent hover:text-base-content/80'
                    ]"
                >
                    Menunggu Alokasi
                    <span v-if="listAlokasi.length > 0" class="px-2 py-0.5 ml-1.5 text-xs text-white bg-error rounded-full">
                        {{ listAlokasi.length }}
                    </span>
                </button>

                <button
                    @click="activeTab = 'pengerjaan'"
                    :class="[
                        'pb-3 text-sm font-bold tracking-wide transition-colors border-b-2 whitespace-nowrap px-4 sm:px-0 shrink-0',
                        activeTab === 'pengerjaan'
                            ? 'text-primary border-primary'
                            : 'text-base-content/50 border-transparent hover:text-base-content/80'
                    ]"
                >
                    Pengerjaan
                    <span v-if="listPengerjaan.length > 0" class="px-2 py-0.5 ml-1.5 text-xs text-white bg-error rounded-full">
                        {{ listPengerjaan.length }}
                    </span>
                </button>

                <button
                    @click="activeTab = 'pengantaran'"
                    :class="[
                        'pb-3 text-sm font-bold tracking-wide transition-colors border-b-2 whitespace-nowrap px-4 sm:px-0 shrink-0',
                        activeTab === 'pengantaran'
                            ? 'text-primary border-primary'
                            : 'text-base-content/50 border-transparent hover:text-base-content/80'
                    ]"
                >
                    Pengantaran
                    <span v-if="listPengantaran.length > 0" class="px-2 py-0.5 ml-1.5 text-xs text-white bg-error rounded-full">
                        {{ listPengantaran.length }}
                    </span>
                </button>
            </div>

            <!-- TAB CONTENT -->
            <div class="mt-4">
                <TabMenungguAlokasi
                    v-if="activeTab === 'alokasi'"
                    :pesananList="listAlokasi"
                    :vendors="vendors"
                    :stafs="stafs"
                    :currentUser="currentUser"
                />

                <TabPengerjaan
                    v-if="activeTab === 'pengerjaan'"
                    :pesananList="listPengerjaan"
                    :currentUser="currentUser"
                    :currentVendorId="currentVendorId"
                />

                <TabPengantaran
                    v-if="activeTab === 'pengantaran'"
                    :pesananList="listPengantaran"
                    :currentUser="currentUser"
                />
            </div>

        </div>
    </StafLayout>
</template>

<style scoped>
/* Opsional: Menyembunyikan scrollbar di tab saat mobile biar terlihat lebih rapi */
.custom-scrollbar::-webkit-scrollbar {
    display: none;
}
.custom-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>
