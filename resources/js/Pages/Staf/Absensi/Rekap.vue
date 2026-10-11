<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInputSearch from '@/Components/Form/CustomInputSearch.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import CustomTable from '@/Components/CustomTable.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import { CalendarClock, MapPin, ArrowLeft, Plus, X } from 'lucide-vue-next';

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

const headers = ['Tanggal', 'Staf', 'Status', 'Jam Kerja', 'Jam Lembur', 'Lokasi GPS', 'Aksi'];

// State untuk Filters
const search = ref(props.filters?.search || '');
const filterBulan = ref(props.filters?.bulan || new Date().getMonth() + 1 + '');
const filterTahun = ref(props.filters?.tahun || new Date().getFullYear() + '');

const bulanOptions = [
    { value: '1', label: 'Januari' }, { value: '2', label: 'Februari' },
    { value: '3', label: 'Maret' }, { value: '4', label: 'April' },
    { value: '5', label: 'Mei' }, { value: '6', label: 'Juni' },
    { value: '7', label: 'Juli' }, { value: '8', label: 'Agustus' },
    { value: '9', label: 'September' }, { value: '10', label: 'Oktober' },
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

// Fungsi buka modal peta
const openMap = (lat, long) => {
    if (!lat || !long) return;
    window.open(`https://www.google.com/maps/search/?api=1&query=${lat},${long}`, '_blank');
};

// State dan Fungsi Buka Modal Foto
const isModalOpen = ref(false);
const modalPhotoUrl = ref('');
const modalTitle = ref('');

const openPhotoModal = (photoPath, title) => {
    if (!photoPath) return;
    // Sesuaikan prefix path ini jika Anda menggunakan driver selain public ('/storage/')
    modalPhotoUrl.value = photoPath.startsWith('http') ? photoPath : `/storage/${photoPath}`;
    modalTitle.value = title;
    isModalOpen.value = true;
};

const closePhotoModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        modalPhotoUrl.value = '';
        modalTitle.value = '';
    }, 300);
};
</script>

<template>
    <Head title="Rekap Absensi Staf" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('staf.index')" class="transition-colors btn btn-sm btn-circle btn-ghost ring-1 ring-base-300 hover:bg-base-200">
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

                    <!-- Tombol Input Manual -->
                    <CustomButton type="link" :href="route('absensi.create')" variant="primary" size="md" class="shadow-md shrink-0 rounded-xl">
                        <template #icon>
                            <Plus class="w-4 h-4 mr-1" />
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

                            <!-- Kolom Jam Kerja -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col text-sm min-w-25">
                                    <div class="flex items-center gap-2 mb-1">
                                        <button
                                            type="button"
                                            @click="absen.tipe_absen === 'Reguler' && absen.foto_masuk ? openPhotoModal(absen.foto_masuk, 'Selfie Masuk (Reguler)') : null"
                                            :disabled="absen.tipe_absen !== 'Reguler' || !absen.foto_masuk"
                                            class="text-[10px] font-bold px-1.5 py-0.5 rounded transition-all"
                                            :class="(absen.tipe_absen === 'Reguler' && absen.foto_masuk) ? 'bg-success/20 text-success hover:bg-success hover:text-white cursor-pointer shadow-sm' : 'bg-success/10 text-success/50 cursor-not-allowed'"
                                            title="Lihat Foto"
                                        >IN</button>
                                        <span class="font-bold text-base-content/80">
                                            {{ absen.tipe_absen === 'Reguler' ? (absen.jam_masuk || '-') : '-' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="absen.tipe_absen === 'Reguler' && absen.foto_keluar ? openPhotoModal(absen.foto_keluar, 'Selfie Keluar (Reguler)') : null"
                                            :disabled="absen.tipe_absen !== 'Reguler' || !absen.foto_keluar"
                                            class="text-[10px] font-bold px-1.5 py-0.5 rounded transition-all"
                                            :class="(absen.tipe_absen === 'Reguler' && absen.foto_keluar) ? 'bg-error/20 text-error hover:bg-error hover:text-white cursor-pointer shadow-sm' : 'bg-error/10 text-error/50 cursor-not-allowed'"
                                            title="Lihat Foto"
                                        >OUT</button>
                                        <span class="font-bold text-base-content/80">
                                            {{ absen.tipe_absen === 'Reguler' ? (absen.jam_keluar || '-') : '-' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom Jam Lembur -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col text-sm min-w-25">
                                    <div class="flex items-center gap-2 mb-1">
                                        <button
                                            type="button"
                                            @click="absen.tipe_absen === 'Lembur' && absen.foto_masuk ? openPhotoModal(absen.foto_masuk, 'Selfie Masuk (Lembur)') : null"
                                            :disabled="absen.tipe_absen !== 'Lembur' || !absen.foto_masuk"
                                            class="text-[10px] font-bold px-1.5 py-0.5 rounded transition-all"
                                            :class="(absen.tipe_absen === 'Lembur' && absen.foto_masuk) ? 'bg-info/20 text-info hover:bg-info hover:text-white cursor-pointer shadow-sm' : 'bg-info/10 text-info/50 cursor-not-allowed'"
                                            title="Lihat Foto"
                                        >IN</button>
                                        <span class="font-bold text-base-content/80">
                                            {{ absen.tipe_absen === 'Lembur' ? (absen.jam_masuk || '-') : '-' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="absen.tipe_absen === 'Lembur' && absen.foto_keluar ? openPhotoModal(absen.foto_keluar, 'Selfie Keluar (Lembur)') : null"
                                            :disabled="absen.tipe_absen !== 'Lembur' || !absen.foto_keluar"
                                            class="text-[10px] font-bold px-1.5 py-0.5 rounded transition-all"
                                            :class="(absen.tipe_absen === 'Lembur' && absen.foto_keluar) ? 'bg-warning/20 text-warning hover:bg-warning hover:text-white cursor-pointer shadow-sm' : 'bg-warning/10 text-warning/50 cursor-not-allowed'"
                                            title="Lihat Foto"
                                        >OUT</button>
                                        <span class="font-bold text-base-content/80">
                                            {{ absen.tipe_absen === 'Lembur' ? (absen.jam_keluar || '-') : '-' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Lokasi GPS -->
                            <td class="px-6 py-4">
                                <button v-if="absen.lat_masuk && absen.long_masuk" @click="openMap(absen.lat_masuk, absen.long_masuk)" class="btn btn-xs btn-ghost text-info gap-1 text-[10px] uppercase font-bold tracking-widest px-2">
                                    <MapPin size="12" /> Buka Peta
                                </button>
                                <span v-else class="text-xs italic opacity-40">-</span>
                            </td>

                            <!-- Aksi -->
                            <td class="px-6 py-4">
                                <CustomButton type="link" :href="route('absensi.edit', absen.id)" size="sm">
                                    Edit
                                </CustomButton>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="absensis.data.length === 0">
                            <td colspan="7" class="px-6 py-20 text-center">
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
                    <div v-if="absensis.links && absensis.links.length > 3" class="flex justify-center p-4 border-t border-base-200 bg-base-50">
                        <div class="join">
                            <component
                                :is="link.url ? Link : 'span'"
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

        <!-- Modal Lihat Foto -->
        <dialog class="modal modal-bottom sm:modal-middle backdrop-blur-sm" :class="{ 'modal-open': isModalOpen }">
            <div class="modal-box relative">
                <!-- Tombol Close Modal -->
                <button @click="closePhotoModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                    <X class="w-5 h-5"/>
                </button>
                <h3 class="font-bold text-lg mb-4">{{ modalTitle }}</h3>
                <div class="w-full flex items-center justify-center p-2 bg-base-200 rounded-xl min-h-75">
                    <img v-if="modalPhotoUrl" :src="modalPhotoUrl" class="w-full max-h-[60vh] object-contain rounded-lg shadow-sm" alt="Foto Absen" />
                    <span v-else class="text-sm font-semibold opacity-50 italic">Gambar tidak ditemukan</span>
                </div>
                <div class="modal-action">
                    <button class="btn btn-primary" @click="closePhotoModal">Tutup</button>
                </div>
            </div>
            <!-- Klik backdrop untuk close -->
            <form method="dialog" class="modal-backdrop" @click="closePhotoModal">
                <button>close</button>
            </form>
        </dialog>

    </StafLayout>
</template>
