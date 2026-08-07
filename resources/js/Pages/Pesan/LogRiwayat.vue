<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';

const props = defineProps({
    pesanan: {
        type: Object,
        required: true
    }
});

const expandedLogs = ref(new Set());

const toggleExpand = (id) => {
    if (expandedLogs.value.has(id)) {
        expandedLogs.value.delete(id);
    } else {
        expandedLogs.value.add(id);
    }
};

const isExpanded = (id) => expandedLogs.value.has(id);

const formatWaktu = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short'
    }).format(date) + ' WIB';
};

const formatAksi = (aksi) => {
    if (!aksi) return 'Aksi Sistem';
    return aksi.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const getNamaPelaku = (log) => {
    if (!log.id_staf) return 'Pelanggan / Sistem';
    return log.staf?.user?.name || log.id_staf;
};

const getAksiColor = (aksi) => {
    const colors = {
        buat_pesanan: 'text-success bg-success/10 border-success/20',
        edit_pesanan: 'text-warning bg-warning/10 border-warning/20',
        pindah_produksi: 'text-primary bg-primary/10 border-primary/20',
        alokasi_pekerjaan: 'text-info bg-info/10 border-info/20',
        selesai_produksi: 'text-success bg-success/10 border-success/20',
        pindah_pengantaran: 'text-primary bg-primary/10 border-primary/20',
        kirim_pesanan: 'text-success bg-success/10 border-success/20',
        pembayaran: 'text-warning bg-warning/10 border-warning/20',
        tambah_item: 'text-success bg-success/10 border-success/20',
        edit_item: 'text-warning bg-warning/10 border-warning/20',
        hapus_item: 'text-error bg-error/10 border-error/20',
    };
    return colors[aksi] || 'text-base-content bg-base-200 border-base-300';
};

// ==============================================================================
// ALGORITMA PENGOLAHAN JSON KE TABEL YANG MUDAH DIBACA MANUSIA
// ==============================================================================

// Fungsi untuk nge-flatten (mendatarkan) JSON yang bersarang menjadi 1 level
const flattenObject = (obj, prefix = '') => {
    let result = {};
    if (!obj) return result;

    for (const key in obj) {
        if (!Object.prototype.hasOwnProperty.call(obj, key)) continue;

        let value = obj[key];

        // Buang kolom-kolom yang nggak penting buat dibaca user
        const ignoreKeys = ['created_at', 'updated_at', 'deleted_at', 'id'];
        if (ignoreKeys.includes(key)) continue;

        // Coba deteksi kalau ada JSON string di dalam data (misal: atribut_custom)
        if (typeof value === 'string' && (value.startsWith('{') || value.startsWith('['))) {
            try {
                const parsed = JSON.parse(value);
                if (typeof parsed === 'object' && parsed !== null) value = parsed;
            } catch(e) {}
        }

        const newKey = prefix ? `${prefix}.${key}` : key;

        if (value !== null && typeof value === 'object') {
            if (Array.isArray(value)) {
                if (value.length === 0) {
                    result[newKey] = '[] (Kosong)';
                } else {
                    value.forEach((item, index) => {
                        if (item !== null && typeof item === 'object') {
                            Object.assign(result, flattenObject(item, `${newKey}[${index + 1}]`));
                        } else {
                            result[`${newKey}[${index + 1}]`] = item;
                        }
                    });
                }
            } else {
                if (Object.keys(value).length === 0) {
                    result[newKey] = '{} (Kosong)';
                } else {
                    Object.assign(result, flattenObject(value, newKey));
                }
            }
        } else {
            result[newKey] = value;
        }
    }
    return result;
};

// Fungsi mengubah key 'pesanan_item[1].harga_ongkir' jadi 'Pesanan Item #1 ➔ Harga Ongkir'
const formatKeyLabel = (key) => {
    return key
        .replace(/\[(\d+)\]/g, ' #$1') // Ubah [1] jadi #1
        .replace(/\./g, ' ➔ ')         // Ubah titik jadi panah navigasi
        .replace(/_/g, ' ')            // Hapus underscore
        .replace(/\b\w/g, c => c.toUpperCase()); // Tiap kata huruf besar
};

// Fungsi Utama membandingkan Lama vs Baru dan ambil yang beda saja
const getDifferences = (oldObj, newObj) => {
    const flatOld = flattenObject(oldObj);
    const flatNew = flattenObject(newObj);
    const allKeys = new Set([...Object.keys(flatOld), ...Object.keys(flatNew)]);
    const changes = [];

    allKeys.forEach(key => {
        const oldVal = flatOld[key];
        const newVal = flatNew[key];

        // Skip kalau isinya sama persis
        if (oldVal === newVal) return;

        // Skip kalau dua-duanya bernilai kosong (null, undefined, '')
        if (!oldVal && !newVal) return;

        changes.push({
            key: formatKeyLabel(key),
            old: oldVal,
            new: newVal
        });
    });

    // Urutkan abjad biar gampang dibaca
    return changes.sort((a, b) => a.key.localeCompare(b.key));
};

const formatTanggal = (tgl) => {
    if (!tgl) return '-';
    const date = new Date(tgl);
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
};

const formatDisplayValue = (val) => {
    if (val === null || val === undefined || val === '') return '-';
    if (typeof val === 'boolean') return val ? 'Ya' : 'Tidak';
    if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}/.test(val)) {
        return formatTanggal(val);
    }
    return String(val);
};
</script>

<template>
    <Head :title="`Audit Trail - ${pesanan.id_pesan}`" />

    <StafLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <CustomButton type="link" :href="route('pesan.index')" variant="secondary" size="sm" class="px-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </CustomButton>
                <div>
                    <h2 class="text-xl font-bold leading-tight text-base-content">Riwayat Aktivitas Pesanan</h2>
                    <p class="mt-1 font-mono text-xs font-bold tracking-widest uppercase text-base-content/50">{{ pesanan.id_pesan }} &bull; {{ pesanan.kode_transaksi }}</p>
                </div>
            </div>
        </template>

        <div class="min-h-screen px-4 py-6 mx-auto sm:px-6 lg:px-8 max-w-7xl">

            <div class="flex flex-col justify-between gap-4 p-6 mb-8 border shadow-sm rounded-2xl bg-base-100 border-base-300 md:flex-row">
                <div>
                    <div class="mb-1 text-xs font-black tracking-widest uppercase text-base-content/40">Customer</div>
                    <div class="text-lg font-bold text-base-content">{{ pesanan.customer?.user?.name || 'Walk-in / Umum' }}</div>
                </div>
                <div class="md:text-right">
                    <div class="mb-1 text-xs font-black tracking-widest uppercase text-base-content/40">Status Terkini</div>
                    <div class="flex items-center gap-2 md:justify-end">
                        <span class="px-2 py-1 text-[10px] font-black uppercase tracking-wider rounded-lg border border-base-300 bg-base-200">{{ formatAksi(pesanan.status_operasional) }}</span>
                        <span class="px-2 py-1 text-[10px] font-black uppercase tracking-wider rounded-lg border border-base-300 bg-base-200">{{ formatAksi(pesanan.status_pembayaran) }}</span>
                    </div>
                </div>
            </div>

            <div class="relative pb-10 pl-6 ml-4 space-y-8 border-l-2 border-base-300/50">

                <div v-for="(log, index) in pesanan.logs" :key="log.id" class="relative group">
                    <div class="absolute -left-7.75 top-1.5 flex items-center justify-center w-5 h-5 rounded-full bg-base-100 border-2 border-primary/50 group-hover:border-primary group-hover:bg-primary transition-colors ring-4 ring-base-100"></div>

                    <div class="p-5 transition-all border shadow-sm rounded-2xl bg-base-100 border-base-300 hover:shadow-md hover:border-base-300/80">

                        <div class="flex flex-col justify-between gap-4 mb-3 md:flex-row md:items-start">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg border" :class="getAksiColor(log.aksi)">
                                        {{ formatAksi(log.aksi) }}
                                    </span>
                                    <span class="text-[10px] font-bold text-base-content/40 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        {{ formatWaktu(log.created_at) }}
                                    </span>
                                </div>
                                <h3 class="text-sm font-bold leading-relaxed text-base-content">
                                    {{ log.keterangan || 'Sistem melakukan pembaruan data.' }}
                                </h3>
                            </div>

                            <div class="flex items-center gap-2 p-2 shrink-0 rounded-xl bg-base-200/50">
                                <div class="flex items-center justify-center w-8 h-8 text-xs font-black uppercase rounded-full bg-base-300 text-base-content/50">
                                    {{ getNamaPelaku(log).substring(0, 2) }}
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] font-black uppercase tracking-widest text-base-content/40">Pelaku/Staf</div>
                                    <div class="text-xs font-bold text-base-content">{{ getNamaPelaku(log) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Expand Perubahan Data -->
                        <div v-if="log.data_lama || log.data_baru" class="pt-4 mt-4 border-t border-base-200">
                            <button @click="toggleExpand(log.id)" class="flex items-center gap-2 text-xs font-bold transition-colors text-primary hover:text-primary-focus">
                                <svg class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': isExpanded(log.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                {{ isExpanded(log.id) ? 'Sembunyikan Rincian Perubahan' : 'Lihat Rincian Perubahan Data' }}
                            </button>

                            <!-- Accordion Content: Tabel Diff -->
                            <div v-show="isExpanded(log.id)" class="mt-4">
                                <div v-if="getDifferences(log.data_lama, log.data_baru).length > 0" class="overflow-x-auto border rounded-xl border-base-200">
                                    <table class="table w-full text-left table-xs">
                                        <thead class="bg-base-200/50 text-base-content/60">
                                            <tr>
                                                <th class="w-1/3 py-3 font-bold tracking-wider uppercase">Informasi (Field)</th>
                                                <th class="w-1/3 py-3 font-bold tracking-wider uppercase text-error">Data Sebelumnya</th>
                                                <th class="w-1/3 py-3 font-bold tracking-wider uppercase text-success">Data Sesudah (Baru)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="change in getDifferences(log.data_lama, log.data_baru)"
                                                :key="change.key"
                                                class="border-b hover:bg-base-200/30 border-base-200/50"
                                            >
                                                <td class="text-xs font-bold whitespace-normal text-base-content/80">
                                                    {{ change.key }}
                                                </td>
                                                <td class="text-xs whitespace-pre-wrap text-error/90">
                                                    <div v-if="change.old !== undefined" class="inline-block px-2 py-1 rounded bg-error/10">
                                                        <del>{{ formatDisplayValue(change.old) }}</del>
                                                    </div>
                                                    <span v-else class="italic opacity-30">-</span>
                                                </td>
                                                <td class="text-xs font-medium whitespace-pre-wrap text-success">
                                                    <div v-if="change.new !== undefined" class="inline-block px-2 py-1 rounded bg-success/10">
                                                        {{ formatDisplayValue(change.new) }}
                                                    </div>
                                                    <span v-else class="italic opacity-30">-</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="p-4 text-xs font-bold text-center border border-dashed bg-base-200/50 rounded-xl border-base-300 opacity-60">
                                    Tidak ada data spesifik yang berubah (Hanya status operasional atau sistem).
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div v-if="pesanan.logs.length === 0" class="py-10 text-center opacity-40">
                    <svg class="w-10 h-10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="text-xs font-black tracking-widest uppercase">Belum ada aktivitas yang direkam</p>
                </div>

            </div>
        </div>
    </StafLayout>
</template>
