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
            <div class="flex items-center justify-between w-full">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Produksi & Alokasi
                    </h2>
                    <p class="mt-1 text-sm text-base-content/60">Pantau antrean, pecah tugas ke vendor, dan perbarui progres.</p>
                </div>

                <CustomButton type="link" :href="route('produksi.histori')">
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

            <!-- TABS NAVIGATION -->
            <div class="tabs tabs-boxed bg-base-200/50 p-1.5 font-semibold border border-base-300 w-full md:w-max">
                <a
                    class="tab tab-lg transition-all"
                    :class="activeTab === 'alokasi' ? 'tab-active bg-primary text-white shadow-sm' : 'text-base-content/60 hover:text-base-content'"
                    @click="activeTab = 'alokasi'"
                >
                    Menunggu Alokasi
                    <div class="badge badge-sm ml-2 border-none" :class="activeTab === 'alokasi' ? 'bg-white/20 text-white' : 'bg-base-300 text-base-content/60'">{{ listAlokasi.length }}</div>
                </a>
                <a
                    class="tab tab-lg transition-all"
                    :class="activeTab === 'pengerjaan' ? 'tab-active bg-primary text-white shadow-sm' : 'text-base-content/60 hover:text-base-content'"
                    @click="activeTab = 'pengerjaan'"
                >
                    Pengerjaan
                    <div class="badge badge-sm ml-2 border-none" :class="activeTab === 'pengerjaan' ? 'bg-white/20 text-white' : 'bg-base-300 text-base-content/60'">{{ listPengerjaan.length }}</div>
                </a>
                <a
                    class="tab tab-lg transition-all"
                    :class="activeTab === 'pengantaran' ? 'tab-active bg-primary text-white shadow-sm' : 'text-base-content/60 hover:text-base-content'"
                    @click="activeTab = 'pengantaran'"
                >
                    Pengantaran
                    <div class="badge badge-sm ml-2 border-none" :class="activeTab === 'pengantaran' ? 'bg-white/20 text-white' : 'bg-base-300 text-base-content/60'">{{ listPengantaran.length }}</div>
                </a>
            </div>

            <!-- TAB CONTENT -->
            <div class="mt-6">
                <TabMenungguAlokasi
                    v-if="activeTab === 'alokasi'"
                    :pesananList="listAlokasi"
                    :vendors="vendors"
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
