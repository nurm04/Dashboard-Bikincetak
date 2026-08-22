<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import { alertStore } from '@/Utils/alertStore';
import { Clock, MapPin, Camera, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    absensi: Object,
    office_lat: Number,
    office_long: Number,
    max_radius: Number,
});

const videoRef = ref(null);
const canvasRef = ref(null);
const isCameraReady = ref(false);
const streamTrack = ref(null);

const location = ref({ lat: null, long: null, accuracy: null, error: null });
const distance = ref(null);
const currentTime = ref('');

const form = useForm({
    tipe: '',
    foto: '',
    lat: '',
    long: '',
});

// Hitung jarak (Haversine di JS untuk indikator UI)
const calculateDistance = (lat1, lon1, lat2, lon2) => {
    const R = 6371e3; // Radius bumi dalam meter
    const p1 = lat1 * Math.PI / 180;
    const p2 = lat2 * Math.PI / 180;
    const dp = (lat2 - lat1) * Math.PI / 180;
    const dl = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(dp / 2) * Math.sin(dp / 2) + Math.cos(p1) * Math.cos(p2) * Math.sin(dl / 2) * Math.sin(dl / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
};

// Start Kamera
const startCamera = async () => {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } });
        videoRef.value.srcObject = stream;
        streamTrack.value = stream;
        isCameraReady.value = true;
    } catch (err) {
        alertStore.show("Gagal mengakses kamera. Pastikan izin kamera diberikan.", "error");
    }
};

// Start GPS
const getLocation = () => {
    if (!navigator.geolocation) {
        location.value.error = "Browser tidak support GPS.";
        return;
    }
    navigator.geolocation.watchPosition(
        (pos) => {
            location.value = { lat: pos.coords.latitude, long: pos.coords.longitude, accuracy: pos.coords.accuracy, error: null };
            distance.value = calculateDistance(props.office_lat, props.office_long, pos.coords.latitude, pos.coords.longitude);
        },
        (err) => {
            location.value.error = "Gagal melacak lokasi. Nyalakan GPS Anda.";
        },
        { enableHighAccuracy: true, maximumAge: 0 }
    );
};

// Jepret Foto & Submit
const takePhotoAndSubmit = (tipeAbsen) => {
    if (distance.value > props.max_radius) {
        alertStore.show(`Anda di luar jangkauan kantor! Jarak: ${Math.round(distance.value)}m`, "error");
        return;
    }

    const canvas = canvasRef.value;
    const video = videoRef.value;
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

    const base64Image = canvas.toDataURL('image/jpeg', 0.8);

    form.tipe = tipeAbsen;
    form.foto = base64Image;
    form.lat = location.value.lat;
    form.long = location.value.long;

    form.post(route('absensi.store'), {
        onSuccess: () => alertStore.show('Absen berhasil dikirim!', 'success'),
        onError: () => alertStore.show('Gagal mengirim absensi.', 'error')
    });
};

onMounted(() => {
    startCamera();
    getLocation();
    setInterval(() => {
        currentTime.value = new Date().toLocaleTimeString('id-ID');
    }, 1000);
});

onUnmounted(() => {
    if (streamTrack.value) {
        streamTrack.value.getTracks().forEach(track => track.stop());
    }
});

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.get(route('dashboard'));
    }
};
</script>

<template>
    <Head title="Live Absensi" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <button @click.prevent="goBack" class="transition-colors btn btn-sm btn-circle btn-ghost ring-1 ring-base-300 hover:bg-base-200">
                        <ArrowLeft class="w-4 h-4" />
                    </button>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Absensi Harian
                    </h2>
                </div>
            </div>
        </template>

        <div class="max-w-2xl px-4 py-8 mx-auto">
            <div class="flex flex-col items-center p-6 border shadow-xl bg-base-100 rounded-3xl border-base-300">

                <!-- JAM & TANGGAL -->
                <div class="mb-6 text-center">
                    <h1 class="font-mono text-4xl font-black tracking-widest text-primary">{{ currentTime || '00:00:00' }}</h1>
                    <p class="text-sm font-bold uppercase opacity-60">{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                </div>

                <!-- INDIKATOR LOKASI -->
                <div :class="['w-full p-4 rounded-2xl mb-6 border flex items-center justify-center gap-2 font-bold text-sm', distance <= max_radius ? 'bg-success/10 text-success border-success/20' : 'bg-error/10 text-error border-error/20']">
                    <MapPin size="18" />
                    <span v-if="location.error">{{ location.error }}</span>
                    <span v-else-if="distance !== null">
                        Jarak Anda: {{ Math.round(distance) }} meter
                        {{ distance <= max_radius ? '(Di Area Kantor)' : '(Di Luar Area)' }}
                    </span>
                    <span v-else>Mencari titik lokasi GPS...</span>
                </div>

                <!-- LIVE CAMERA FEED -->
                <div class="relative w-full max-w-sm mb-8 overflow-hidden border-4 shadow-inner aspect-3/4 bg-base-300 rounded-3xl border-base-200">
                    <video ref="videoRef" autoplay playsinline class="w-full h-full object-cover transform scale-x-[-1]"></video>
                    <canvas ref="canvasRef" class="hidden"></canvas>

                    <div v-if="!isCameraReady" class="absolute inset-0 flex flex-col items-center justify-center opacity-50">
                        <Camera size="48" class="mb-2 animate-pulse" />
                        <span class="text-xs font-bold tracking-widest uppercase">Menyiapkan Kamera...</span>
                    </div>

                    <!-- Overlay Garis Panduan Wajah -->
                    <div class="absolute inset-0 pointer-events-none border-[1.5px] border-dashed border-white/40 rounded-[100%] m-8 opacity-50"></div>
                </div>

                <!-- TOMBOL AKSI -->
                <div class="flex flex-col w-full max-w-sm gap-4 sm:flex-row">
                    <CustomButton
                        v-if="!absensi?.jam_masuk"
                        variant="primary"
                        class="w-full py-4 font-black tracking-widest uppercase rounded-2xl"
                        :disabled="!isCameraReady || distance > max_radius || form.processing"
                        @click="takePhotoAndSubmit('masuk')"
                    >
                        <span v-if="form.processing" class="loading loading-spinner"></span>
                        <span v-else>Absen Masuk</span>
                    </CustomButton>

                    <CustomButton
                        v-if="absensi?.jam_masuk && !absensi?.jam_keluar"
                        variant="warning"
                        class="w-full py-4 font-black tracking-widest text-white uppercase rounded-2xl"
                        :disabled="!isCameraReady || distance > max_radius || form.processing"
                        @click="takePhotoAndSubmit('keluar')"
                    >
                        <span v-if="form.processing" class="loading loading-spinner"></span>
                        <span v-else>Absen Pulang</span>
                    </CustomButton>

                    <div v-if="absensi?.jam_masuk && absensi?.jam_keluar" class="w-full p-4 font-black tracking-widest text-center uppercase shadow-lg bg-success text-success-content rounded-2xl">
                        ✅ Anda Sudah Selesai Absen Hari Ini
                    </div>
                </div>

                <!-- RIWAYAT HARI INI -->
                <div v-if="absensi" class="w-full max-w-sm p-4 mt-8 border bg-base-200/50 rounded-2xl border-base-300">
                    <h3 class="pb-2 mb-3 text-xs font-black tracking-widest uppercase border-b opacity-50 border-base-content/10">Catatan Hari Ini</h3>
                    <div class="flex items-center justify-between mb-2">
                        <span class="flex items-center gap-2 text-sm font-bold opacity-80"><Clock size="14"/> Jam Masuk:</span>
                        <span class="text-sm font-black text-primary">{{ absensi.jam_masuk || '--:--' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-sm font-bold opacity-80"><Clock size="14"/> Jam Keluar:</span>
                        <span class="text-sm font-black text-warning">{{ absensi.jam_keluar || '--:--' }}</span>
                    </div>
                </div>

            </div>
        </div>
    </StafLayout>
</template>
