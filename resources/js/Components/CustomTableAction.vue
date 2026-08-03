<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';

const isOpen = ref(false);
const actionRef = ref(null);
const dropdownRef = ref(null); // Tambahan ref untuk menu dropdown
const dropdownStyle = ref({});

const calculatePosition = () => {
    if (!actionRef.value) return;

    // Ambil posisi tombol Aksi di layar saat ini
    const rect = actionRef.value.getBoundingClientRect();

    // Hitung posisi dropdown (w-52 = 13rem = 208px)
    // Kita kurangi lebar dropdown agar posisinya rata kanan dengan tombol
    let leftPos = rect.right - 208;

    // Set posisi menggunakan 'fixed' berdasarkan layar, bukan relative ke tabel
    dropdownStyle.value = {
        top: `${rect.bottom + 8}px`, // Jarak 8px dari bawah tombol
        left: `${leftPos}px`,
    };
};

const toggle = async () => {
    if (!isOpen.value) {
        window.dispatchEvent(new CustomEvent('close-all-dropdowns'));
        // Tunggu DOM update, lalu hitung posisi kordinatnya
        await nextTick();
        calculatePosition();
    }
    isOpen.value = !isOpen.value;
};

const close = () => (isOpen.value = false);

const handleClickOutside = (event) => {
    // Cek apakah klik terjadi di dalam tombol toggle
    const isClickInsideButton = actionRef.value && actionRef.value.contains(event.target);
    // Cek apakah klik terjadi di dalam menu dropdown yang di-teleport
    const isClickInsideDropdown = dropdownRef.value && dropdownRef.value.contains(event.target);

    if (!isClickInsideButton && !isClickInsideDropdown) {
        close();
    }
};

const handleCloseAll = () => {
    close();
};

const handleScroll = () => {
    // WAJIB: Tutup dropdown otomatis jika tabel atau halaman di-scroll.
    // Ini mencegah dropdown melayang lepas dari tombolnya.
    if (isOpen.value) close();
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    window.addEventListener('close-all-dropdowns', handleCloseAll);
    // Angka "true" di bawah ini wajib ada untuk menangkap scroll dari dalam tabel
    window.addEventListener('scroll', handleScroll, true);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('close-all-dropdowns', handleCloseAll);
    window.removeEventListener('scroll', handleScroll, true);
});
</script>

<template>
    <!-- ref actionRef dipindah ke div pembungkus utama -->
    <div class="relative inline-block" ref="actionRef">
        <button
            @click.stop="toggle"
            type="button"
            class="p-2 transition-all duration-300 border border-transparent rounded-xl hover:bg-primary/10 group"
            :class="isOpen ? 'bg-primary/10 text-primary border-primary/20' : 'text-base-content/30'"
        >
            <svg class="w-5 h-5 transition-transform duration-300" :class="{ 'rotate-90': isOpen }" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
            </svg>
        </button>

        <!-- AJAIBNYA VUE 3: Teleport akan memindahkan elemen ini keluar dari tabel langsung ke <body> -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="scale-95 translate-y-2 opacity-0"
                enter-to-class="scale-100 translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="scale-100 translate-y-0 opacity-100"
                leave-to-class="scale-95 translate-y-2 opacity-0"
            >
                <div
                    v-if="isOpen"
                    ref="dropdownRef"
                    class="fixed py-2 overflow-hidden border shadow-2xl w-52 z-9999 bg-base-100 border-base-300 rounded-2xl"
                    :style="dropdownStyle"
                >
                    <slot :close="close" />
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
