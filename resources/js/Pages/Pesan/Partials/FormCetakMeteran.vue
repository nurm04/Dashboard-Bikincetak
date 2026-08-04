<script setup>
import { ref, watch, onMounted } from 'vue';
import CustomInput from '@/Components/Form/CustomInput.vue';

const props = defineProps({
    finishings: { type: Object, default: () => ({}) },
    selectedSku: { type: Object, default: null },
    initialAttributes: { type: Object, default: () => ({}) },
    hargaSatuanSnapshot: { type: Number, default: 0 },
    qty: { type: Number, default: 1 }
});

const emit = defineEmits(['updateAttributes', 'updateBiayaTambahan']);

const panjang = ref('1');
const lebar = ref('1');

// Standar ukuran roll bahan di percetakan (dalam meter)
const rollSizes = [0.9, 1.2, 1.6, 1.8, 2];

// State untuk nampilin data di template UI
const detailKalkulasi = ref({
    sisiTerbesar: 0,
    lebarEfektif: 0,
    luasHitung: 0,
    luasDihargai: 0
});

onMounted(() => {
    if (props.initialAttributes && Object.keys(props.initialAttributes).length > 0) {
        if (props.initialAttributes['Panjang']) panjang.value = props.initialAttributes['Panjang'];
        if (props.initialAttributes['Lebar']) lebar.value = props.initialAttributes['Lebar'];
    }
    kalkulasi();
});

const kalkulasi = () => {
    // 1. Ambil angka desimal (Ubah koma jadi titik biar aman)
    const p = parseFloat(String(panjang.value).replace(',', '.')) || 0;
    const l = parseFloat(String(lebar.value).replace(',', '.')) || 0;
    const luasMurni = p * l;

    // 2. Logic "Waste" Bahan berdasarkan ukuran Roll
    const sisiTerkecil = Math.min(p, l);
    const sisiTerbesar = Math.max(p, l);

    // Cari roll terkecil yang bisa muat sisi terkecil pesanan
    let lebarBahan = sisiTerkecil;
    const fittingRoll = rollSizes.find(size => size >= sisiTerkecil);

    if (fittingRoll) {
        lebarBahan = fittingRoll;
    } else {
        // Jika ukurannya lebih besar dari roll 2 meter, pakai ukuran asli (asumsi di-sambung)
        lebarBahan = sisiTerkecil;
    }

    // Luas murni berdasarkan lebar bahan (contoh: 1 x 0.9 = 0.9 m2)
    const luasHitung = sisiTerbesar * lebarBahan;

    // ATURAN PERCETAKAN: Minimal order luas yang dihargai adalah 1 m2
    const luasDihargai = Math.max(luasHitung, 1);

    // Update state untuk nampilin UI
    detailKalkulasi.value = {
        sisiTerbesar,
        lebarEfektif: lebarBahan,
        luasHitung,
        luasDihargai
    };

    emit('updateAttributes', {
        'Panjang': String(panjang.value),
        'Lebar': String(lebar.value),
        'Luas Murni (m2)': String(luasMurni),
        'Lebar Bahan Dihitung': String(lebarBahan),
        'Luas Dihargai (m2)': String(luasDihargai) // Setor luas yang udah kena minimal 1m2
    });

    if (luasHitung === 0) {
        emit('updateBiayaTambahan', 0);
        return;
    }

    // ==============================================================
    // 3. LOGIC GROSIR BY AREA (Bukan by Qty Pcs)
    // ==============================================================
    const hargaDasar = Number(props.selectedSku?.harga_dasar) || 0;
    let diskonGrosir = 0;

    // Pakai luasDihargai (minimal 1) buat ngitung tier grosir
    const totalLuasSemuaOrder = luasDihargai * props.qty;

    if (props.selectedSku?.harga_bertingkat && props.selectedSku.harga_bertingkat.length > 0) {
        const validTiers = props.selectedSku.harga_bertingkat.filter(t => totalLuasSemuaOrder >= Number(t.min));

        if (validTiers.length > 0) {
            const activeTier = validTiers.sort((a, b) => b.min - a.min)[0];
            diskonGrosir = activeTier.tipe === 'persen'
                ? hargaDasar * (Number(activeTier.nilai) / 100)
                : Number(activeTier.nilai);
        }
    }

    const hargaPerM2Final = hargaDasar - diskonGrosir;

    // Total harga dikalikan luasDihargai (minimal 1)
    const hargaSatuPcsFull = luasDihargai * hargaPerM2Final;
    const selisihUntukParent = hargaSatuPcsFull - props.hargaSatuanSnapshot;

    emit('updateBiayaTambahan', selisihUntukParent);
};

watch(
    [panjang, lebar, () => props.selectedSku, () => props.hargaSatuanSnapshot, () => props.qty],
    () => { kalkulasi(); },
    { deep: true }
);
</script>

<template>
    <div class="p-5 mt-4 border border-dashed rounded-xl bg-primary/5 border-primary/30">
        <div class="flex items-center gap-2 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-primary">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
            </svg>
            <h4 class="text-[10px] font-black tracking-widest uppercase text-primary">Kalkulator Cetak Meteran</h4>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <CustomInput label="Panjang (Meter)" type="text" v-model="panjang" placeholder="Cth: 2,5" />
            <CustomInput label="Lebar (Meter)" type="text" v-model="lebar" placeholder="Cth: 1" />
        </div>

        <!-- UI Bantuan Kalkulasi Minimalis -->
        <div class="pt-4 mt-5 border-t border-dashed border-primary/20">
            <div class="flex flex-col gap-3">
                <!-- Pilihan Roll Bahan -->
                <div>
                    <span class="text-[10px] font-bold text-base-content/50 uppercase tracking-wider mb-2 block">
                        Lebar Bahan Terpilih
                    </span>
                    <div class="flex flex-wrap gap-1.5">
                        <span
                            v-for="roll in rollSizes"
                            :key="roll"
                            :class="[
                                'px-2.5 py-1 text-[11px] font-bold rounded-md border transition-colors',
                                detailKalkulasi.lebarEfektif === roll
                                    ? 'bg-primary text-primary-content border-primary shadow-sm'
                                    : 'bg-base-100 text-base-content/50 border-base-200'
                            ]"
                        >
                            {{ roll }}
                        </span>

                        <!-- Kalau ukurannya di atas 2 meter, tampilkan badge khusus -->
                        <span
                            v-if="!rollSizes.includes(detailKalkulasi.lebarEfektif) && detailKalkulasi.lebarEfektif > 0"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-md border bg-primary text-primary-content border-primary shadow-sm"
                        >
                            {{ detailKalkulasi.lebarEfektif }}
                        </span>
                    </div>
                </div>

                <!-- Rumus Perhitungan -->
                <div class="px-3 py-2 text-[11px] font-medium border rounded-lg bg-base-100/50 border-base-200 text-base-content/70">
                    <div class="flex items-center gap-1">
                        Perhitungan:
                        {{ props.qty }} x {{ detailKalkulasi.lebarEfektif }} x {{ detailKalkulasi.sisiTerbesar }} =
                        <span class="font-bold text-primary">{{ detailKalkulasi.luasHitung.toLocaleString('id-ID', { maximumFractionDigits: 2 }) }} m&sup2;</span>
                    </div>

                    <!-- Peringatan kalau di bawah 1 m2 -->
                    <div v-if="detailKalkulasi.luasHitung > 0 && detailKalkulasi.luasHitung < 1" class="mt-1 text-[10px] italic font-bold text-error">
                        *Dihitung minimal 1 m&sup2;
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
