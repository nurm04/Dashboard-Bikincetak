<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInputSearch from '@/Components/Form/CustomInputSearch.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import { CalendarClock, MapPin, Edit, ArrowLeft } from 'lucide-vue-next';

const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const props = defineProps({
    absensis: Object,
    filters: Object
});

const headers = ['Tanggal', 'Staf', 'Status', 'Jam Masuk', 'Jam Keluar', 'Lokasi GPS', 'Aksi'];

// State untuk Filters
const search = ref(props.filters?.search || '');
const filterBulan = ref(props.filters?.bulan || new Date().getMonth() + 1 + '');
const filterTahun = ref(props.filters?.tahun || new Date().getFullYear() + '');

const bulanOptions = [
    { value: '01', label: 'Januari' }, { value: '02', label: 'Februari' },
    { value: '03', label: 'Maret' }, { value: '04', label: 'April' },
    { value: '05', label: 'Mei' }, { value: '06', label: 'Juni' },
    { value: '07', label: 'Juli' }, { value: '08', label: 'Agustus' },
    { value: '09', label: 'September' }, { value: '10', label: 'Oktober' },
    { value: '11', label: 'November' }, { value: '12', label: 'Desember' }
];

const tahunOptions = computed(() => {
    const currentYear = new Date().getFullYear();
    let years = [];
    for (let i = currentYear; i >= currentYear - 3; i--) {
        years.push({ value: i.toString(), label: i.toString() });
    }
    return years;
});

// Trigger pencarian saat ada perubahan filter
watch(
    [search, filterBulan, filterTahun],
    debounce(([newSearch, newBulan, newTahun]) => {
        router.get(route('absensi.rekap'), {
            search: newSearch,
            bulan: newBulan,
            tahun: newTahun
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300)
);

// Format Helper
const formatTanggal = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
};

// Fungsi buka modal peta (Opsional, buat liat GPS)
const openMap = (lat, long) => {
    if (!lat || !long) return;
    window.open(`https://www.google.com/maps/search/?api=1&query=${lat},${long}`, '_blank');
};
</script>

<template>
    <Head title="Rekap Absensi Staf" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('staf.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Rekap Absensi Staf
                    </h2>
                </div>
            </div>
        </template>

        <div class="min-h-screen px-4 py-3 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">

                <!-- Filter Section -->
                <div class="flex flex-col w-full gap-3 mb-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-col w-full gap-3 sm:flex-row md:w-auto">
                        <CustomInputSearch
                            v-model="search"
                            class="w-full sm:w-64"
                            placeholder="Cari Nama Staf..."
                        />
                        <div class="flex w-full gap-2 sm:w-auto">
                            <div class="w-1/2 sm:w-40">
                                <CustomSelect v-model="filterBulan" :options="bulanOptions" valueKey="value" labelKey="label" />
                            </div>
                            <div class="w-1/2 sm:w-32">
                                <CustomSelect v-model="filterTahun" :options="tahunOptions" valueKey="value" labelKey="label" />
                            </div>
                        </div>
                    </div>

                    <!-- 👇 TAMBAHAN TOMBOL INPUT MANUAL 👇 -->
                    <CustomButton type="link" :href="route('absensi.create')" variant="primary" size="md" class="shadow-md shrink-0 rounded-xl">
                        <template #icon>
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        </template>
                        Input Manual / Izin
                    </CustomButton>
                </div>

                <!-- Table Section -->
                <div class="overflow-hidden border shadow-sm bg-base-100 rounded-2xl border-base-300">
                    <CustomTable :headers="headers">
                        <tr v-for="absen in absensis.data" :key="absen.id" class="transition-colors hover:bg-base-200/50">

                            <!-- Tanggal -->
                            <td class="px-6 py-4">
                                <div class="text-xs font-bold tracking-widest uppercase text-primary">{{ formatTanggal(absen.tanggal) }}</div>
                            </td>

                            <!-- Info Staf -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-base-content">{{ absen.staf?.user?.name || 'Unknown' }}</div>
                                <div class="text-[10px] text-base-content/50 font-medium uppercase tracking-widest">{{ absen.staf?.role_staf?.role || '-' }}</div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest border shadow-sm"
                                    :class="
                                        absen.status === 'Tepat Waktu' ? 'bg-success/20 text-success border-success/30' :
                                        absen.status === 'Terlambat' ? 'bg-warning/20 text-warning border-warning/30' :
                                        absen.status === 'Alpha' ? 'bg-error/20 text-error border-error/30' :
                                        'bg-info/20 text-info border-info/30' // Untuk Sakit / Izin
                                    "
                                >
                                    {{ absen.status }}
                                </span>
                            </td>

                            <!-- Jam & Foto Masuk -->
                            <td class="px-6 py-4">
                                <div v-if="absen.jam_masuk" class="flex flex-col">
                                    <span class="font-bold text-base-content/80">{{ absen.jam_masuk }}</span>
                                    <a v-if="absen.foto_masuk" :href="`/storage/${absen.foto_masuk}`" target="_blank" class="text-[10px] text-primary hover:underline font-bold mt-1">Lihat Foto</a>
                                </div>
                                <span v-else class="text-xs italic opacity-40">-</span>
                            </td>

                            <!-- Jam & Foto Keluar -->
                            <td class="px-6 py-4">
                                <div v-if="absen.jam_keluar" class="flex flex-col">
                                    <span class="font-bold text-base-content/80">{{ absen.jam_keluar }}</span>
                                    <a v-if="absen.foto_keluar" :href="`/storage/${absen.foto_keluar}`" target="_blank" class="text-[10px] text-primary hover:underline font-bold mt-1">Lihat Foto</a>
                                </div>
                                <span v-else class="text-xs italic opacity-40">-</span>
                            </td>

                            <!-- Lokasi GPS -->
                            <td class="px-6 py-4">
                                <button v-if="absen.lat_masuk && absen.long_masuk" @click="openMap(absen.lat_masuk, absen.long_masuk)" class="btn btn-xs btn-ghost text-info gap-1 text-[10px] uppercase font-bold tracking-widest px-2">
                                    <MapPin size="12" /> Buka Peta
                                </button>
                                <span v-else class="text-xs italic opacity-40">-</span>
                            </td>

                            <td class="px-6 py-4">
                                <CustomButton type="link" :href="route('absensi.edit', absen.id)" size="sm">
                                    Edit
                                </CustomButton>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="absensis.data.length === 0">
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center opacity-30">
                                    <CalendarClock class="w-12 h-12 mb-2" />
                                    <p class="text-sm font-bold tracking-widest uppercase">
                                        {{ search ? 'Data absensi tidak ditemukan' : 'Belum ada data absensi bulan ini' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </CustomTable>

                    <!-- Pagination Component -->
                    <!-- Kalau lu punya komponen pagination standar, taruh di sini -->
                    <div v-if="absensis.links && absensis.links.length > 3" class="flex justify-center p-4 border-t border-base-200 bg-base-50">
                        <div class="join">
                            <component
                                :is="link.url ? 'a' : 'span'"
                                v-for="(link, idx) in absensis.links"
                                :key="idx"
                                :href="link.url"
                                v-html="link.label"
                                class="join-item btn btn-sm"
                                :class="{ 'btn-active btn-primary pointer-events-none': link.active, 'btn-disabled': !link.url }"
                            />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </StafLayout>
</template>
