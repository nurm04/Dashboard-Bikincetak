<script setup>
import { ref, watch, computed } from 'vue';
import CustomInputNumber from '@/Components/Form/CustomInputNumber.vue';
import { Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue']);

const rows = ref([]);
const columns = ref([]);
const matrixData = ref({});

const initFromProps = () => {
    const raw = props.modelValue || [];

    let uniqueRows = {};
    let uniqueCols = new Set();
    let dataMap = {};

    if (raw.length === 0) {
        uniqueRows = { '1': { min: 1, max: 1 } };
        uniqueCols.add('Reguler');
        dataMap['1'] = { 'Reguler': { nilai: 0, tipe: 'nominal' } };
    } else {
        // KITA UBAH LOGIC GROUPING-NYA: Grouping HANYA Bds. MIN Qty.
        raw.forEach(item => {
            const rowKey = String(item.min);

            // Jika baris ini belum ada, buatkan. Jika max-nya 0 (tak terhingga) tapi
            // di SLA lain max-nya angka spesifik (misal 1), kita prioritaskan angka spesifik tersebut.
            if (!uniqueRows[rowKey]) {
                uniqueRows[rowKey] = { min: item.min, max: item.max };
            } else {
                if (uniqueRows[rowKey].max === 0 && item.max > 0) {
                    uniqueRows[rowKey].max = item.max;
                }
            }

            const colName = item.pengerjaan || 'Reguler';
            uniqueCols.add(colName);

            if (!dataMap[rowKey]) dataMap[rowKey] = {};
            dataMap[rowKey][colName] = {
                nilai: item.nilai ?? 0,
                tipe: item.tipe ?? 'nominal'
            };
        });
    }

    // Sortir baris berdasarkan Min Qty
    rows.value = Object.values(uniqueRows).sort((a, b) => a.min - b.min);

    // Sortir kolom SLA dari yang paling lama ke paling cepat
    // (Bisa disesuaikan kalau mau bebas, tapi ini default-nya alfabet)
    columns.value = Array.from(uniqueCols);
    matrixData.value = dataMap;
};

initFromProps();

const emitChanges = () => {
    let result = [];
    rows.value.forEach((row, index) => {
        const rowKey = String(row.min);

        // Auto-koreksi Max Qty jika admin lupa ngisi dengan benar
        let calculatedMax = row.max;
        if (index < rows.value.length - 1) {
            const nextMin = rows.value[index + 1].min;
            if (calculatedMax === 0 || calculatedMax >= nextMin) {
                calculatedMax = nextMin - 1;
                row.max = calculatedMax; // Update UI sekalian
            }
        } else {
            // Baris terakhir idealnya max = 0
            if (rows.value.length > 1 && calculatedMax !== 0 && calculatedMax < row.min) {
                calculatedMax = 0;
                row.max = 0;
            }
        }

        columns.value.forEach(col => {
            const cell = matrixData.value[rowKey]?.[col] || { nilai: 0, tipe: 'nominal' };
            // Jangan kirim kalau nilainya 0 (Artinya SLA ini gak tersedia untuk Qty tsb)
            if (cell.nilai > 0) {
                result.push({
                    min: row.min,
                    max: calculatedMax,
                    pengerjaan: col,
                    tipe: cell.tipe,
                    nilai: cell.nilai
                });
            }
        });
    });
    emit('update:modelValue', result);
};

// --- TAMBAH / HAPUS BARIS ---
const addRow = () => {
    const lastRow = rows.value[rows.value.length - 1];
    const newMin = lastRow ? (lastRow.max > 0 ? lastRow.max + 1 : lastRow.min + 1) : 1;
    const newMax = newMin;

    const rowKey = String(newMin);
    rows.value.push({ min: newMin, max: newMax });

    if (!matrixData.value[rowKey]) matrixData.value[rowKey] = {};
    columns.value.forEach(col => {
        matrixData.value[rowKey][col] = { nilai: 0, tipe: 'nominal' };
    });
    emitChanges();
};

const removeRow = (index) => {
    if (rows.value.length <= 1) return;
    const rowKey = String(rows.value[index].min);
    delete matrixData.value[rowKey]; // Bersihkan memory
    rows.value.splice(index, 1);
    emitChanges();
};

// --- TAMBAH / HAPUS KOLOM ---
const newColumnName = ref('');
const isAddColModalOpen = ref(false);

const addColumn = () => {
    const colName = newColumnName.value.trim();
    if (!colName || columns.value.includes(colName)) return;

    columns.value.push(colName);
    rows.value.forEach(row => {
        const rowKey = String(row.min);
        if (!matrixData.value[rowKey]) matrixData.value[rowKey] = {};
        matrixData.value[rowKey][colName] = { nilai: 0, tipe: 'nominal' };
    });

    newColumnName.value = '';
    isAddColModalOpen.value = false;
    emitChanges();
};

const removeColumn = (colIndex) => {
    if (columns.value.length <= 1) return;
    const colName = columns.value[colIndex];

    // Hapus dari data
    rows.value.forEach(row => {
        const rowKey = String(row.min);
        if (matrixData.value[rowKey]) {
            delete matrixData.value[rowKey][colName];
        }
    });

    columns.value.splice(colIndex, 1);
    emitChanges();
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-black uppercase tracking-widest opacity-70">Matriks Harga (Spreadsheet Mode)</h3>
            <div class="flex gap-2">
                <button type="button" @click="isAddColModalOpen = true" class="btn btn-xs btn-outline btn-primary font-bold">
                    + Tambah Kolom SLA
                </button>
                <button type="button" @click="addRow" class="btn btn-xs btn-primary font-bold">
                    + Tambah Baris Qty
                </button>
            </div>
        </div>

        <div class="overflow-x-auto border border-base-300 rounded-xl bg-base-100 shadow-sm">
            <table class="table table-sm w-full text-center border-collapse">
                <thead>
                    <tr class="bg-base-200/70 border-b border-base-300">
                        <th class="border-r border-base-300 font-black text-xs uppercase p-3 w-32">Min Qty</th>
                        <th class="border-r border-base-300 font-black text-xs uppercase p-3 w-32">Max Qty</th>

                        <th v-for="(col, colIdx) in columns" :key="col" class="border-r border-base-300 font-black text-xs uppercase p-3 min-w-45">
                            <div class="flex items-center justify-between gap-2">
                                <span class="badge badge-neutral badge-sm font-bold">{{ col }}</span>
                                <button type="button" @click="removeColumn(colIdx)" class="text-error hover:scale-110 transition-transform" title="Hapus Kolom SLA">
                                    <Trash2 class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </th>

                        <th class="p-3 w-16 font-black text-xs uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, rowIdx) in rows" :key="rowIdx" class="hover:bg-base-200/30 border-b border-base-300/50">
                        <!-- Min Qty -->
                        <td class="border-r border-base-300 p-2">
                            <input
                                v-model.number="row.min"
                                @change="emitChanges"
                                type="number"
                                min="1"
                                class="w-full text-xs font-bold text-center bg-transparent border border-base-300 rounded-md py-1 focus:ring-primary focus:border-primary"
                            />
                        </td>
                        <!-- Max Qty -->
                        <td class="border-r border-base-300 p-2">
                            <input
                                v-model.number="row.max"
                                @change="emitChanges"
                                type="number"
                                min="0"
                                class="w-full text-xs font-bold text-center bg-transparent border border-base-300 rounded-md py-1 focus:ring-primary focus:border-primary"
                                placeholder="0 = Inf"
                            />
                        </td>

                        <!-- Cell Harga untuk setiap Kolom SLA -->
                        <td v-for="col in columns" :key="col" class="border-r border-base-300 p-2">
                            <div v-if="matrixData[String(row.min)]?.[col]">
                                <CustomInputNumber
                                    v-model="matrixData[String(row.min)][col].nilai"
                                    @update:modelValue="emitChanges"
                                    prefix="Rp"
                                    :min="0"
                                />
                            </div>
                        </td>

                        <!-- Tombol Hapus Baris -->
                        <td class="p-2 text-center">
                            <button type="button" @click="removeRow(rowIdx)" class="btn btn-ghost btn-xs text-error hover:bg-error/10">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- MODAL TAMBAH KOLOM SLA -->
        <dialog :class="['modal', { 'modal-open': isAddColModalOpen }]">
            <div class="modal-box rounded-2xl max-w-sm">
                <h3 class="font-black text-sm mb-3">Tambah Kolom Estimasi SLA</h3>
                <input
                    v-model="newColumnName"
                    type="text"
                    placeholder="Contoh: 1 Hari, 3 Jam"
                    class="input input-bordered input-sm w-full font-bold mb-4"
                    @keyup.enter="addColumn"
                />
                <div class="modal-action gap-2">
                    <button type="button" class="btn btn-xs uppercase font-bold" @click="isAddColModalOpen = false">Batal</button>
                    <button type="button" class="btn btn-xs btn-primary uppercase font-black" @click="addColumn">Tambah</button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop"><button @click="isAddColModalOpen = false">close</button></form>
        </dialog>
    </div>
</template>
