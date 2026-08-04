<script setup>
import { ref, computed, watch } from 'vue';
import CustomInputNumber from '@/Components/Form/CustomInputNumber.vue';

const props = defineProps({
    finishings: { type: Object, default: () => ({}) },
    selectedSku: { type: Object, default: null },
    initialAttributes: { type: Object, default: () => ({}) }
});

const emit = defineEmits(['updateAttributes', 'updateBiayaTambahan']);

const jumlahHalaman = ref(1);

// Set data awal kalau dari edit mode
watch(() => props.initialAttributes, (newAttrs) => {
    if (newAttrs && newAttrs['Jumlah Halaman']) {
        let h = parseInt(newAttrs['Jumlah Halaman'], 10);
        jumlahHalaman.value = isNaN(h) || h < 1 ? 1 : h;
    }
}, { immediate: true, deep: true });

// RUMUS ASLI LU: Cek Sisi Cetak
const sisiCetakMultiplier = computed(() => {
    let sisi = 1; // Default 1 Sisi
    if (!props.selectedSku) return sisi;

    Object.values(props.finishings).forEach(idSkuFin => {
        if (!idSkuFin) return;
        const fin = props.selectedSku.opsi_finishing?.find(f => String(f.id_sku_finishing) === String(idSkuFin));
        if (fin) {
            const label = fin.nama_pilihan.toLowerCase();
            if (label.includes('2 sisi') || label.includes('dua sisi') || label.includes('bolak')) {
                sisi = 2; // Ganti jadi pengali 2
            }
        }
    });
    return sisi;
});

// RUMUS ASLI LU: Biaya Halaman
const biayaHalamanPerBuku = computed(() => {
    let inputHal = parseInt(jumlahHalaman.value, 10);
    if (isNaN(inputHal) || inputHal < 1) inputHal = 1;

    // Aturan: Halaman 1 = Rp 0, sisanya +1500 (dikali Sisi)
    const tambahanHalaman = Math.max(0, inputHal - 1);
    return tambahanHalaman * sisiCetakMultiplier.value * 1500;
});

// Tiap ada perubahan, lempar nilainya ke OrderFormCard.vue
watch([jumlahHalaman, () => biayaHalamanPerBuku.value], ([newJml, newBiaya]) => {
    emit('updateAttributes', { 'Jumlah Halaman': newJml });
    emit('updateBiayaTambahan', newBiaya);
}, { immediate: true, deep: true });
</script>

<template>
    <div class="p-4 border bg-base-200/50 rounded-xl border-base-300">
        <h4 class="mb-3 text-[10px] font-bold tracking-widest uppercase text-base-content/50">Detail Isi Buku</h4>
        <CustomInputNumber
            label="Jumlah Halaman (Termasuk Cover)"
            v-model="jumlahHalaman"
            placeholder="Contoh: 100"
        />
    </div>
</template>
