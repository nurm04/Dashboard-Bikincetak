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
        uniqueRows = { '1': { min: 1, max: 0 } }; // Default min 1 max 0
        uniqueCols.add('1 Hari'); // Default SLA pertama kali
        dataMap['1'] = { '1 Hari': { nilai: 0, tipe: 'nominal' } };
    } else {
        // Grouping berdasarkan MIN Qty
        raw.forEach(item => {
            const rowKey = String(item.min);

            if (!uniqueRows[rowKey]) {
                uniqueRows[rowKey] = { min: item.min, max: item.max };
            } else {
                if (uniqueRows[rowKey].max === 0 && item.max > 0) {
                    uniqueRows[rowKey].max = item.max;
                }
            }

            const colName = item.pengerjaan || '1 Hari';
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
    columns.value = Array.from(uniqueCols);

    // Pastikan semua cell terinisialisasi meskipun 0 biar bisa di-edit (Excel Style)
    rows.value.forEach(row => {
        const rowKey = String(row.min);
        if (!dataMap[rowKey]) dataMap[rowKey] = {};
        columns.value.forEach(col => {
            if (!dataMap[rowKey][col]) {
                dataMap[rowKey][col] = { nilai: 0, tipe: 'nominal' };
            }
        });
    });

    matrixData.value = dataMap;
};

initFromProps();

const emitChanges = () => {
    let result = [];
    rows.value.forEach((row, index) => {
        const rowKey = String(row.min);

        // Auto-koreksi Max Qty
        let calculatedMax = row.max;
        if (index < rows.value.length - 1) {
            const nextMin = rows.value[index + 1].min;
            if (calculatedMax === 0 || calculatedMax >= nextMin) {
                calculatedMax = nextMin - 1;
                row.max = calculatedMax;
            }
        } else {
            if (rows.value.length > 1 && calculatedMax !== 0 && calculatedMax < row.min) {
                calculatedMax = 0;
                row.max = 0;
            }
        }

        columns.value.forEach(col => {
            const cell = matrixData.value[rowKey]?.[col] || { nilai: 0, tipe: 'nominal' };
            // Jangan kirim kalau nilainya 0 ke database (anggap kosong)
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

// --- TAMBAH / HAPUS BARIS QTY ---
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
    delete matrixData.value[rowKey];
    rows.value.splice(index, 1);
    emitChanges();
};

// --- TAMBAH / UBAH / HAPUS KOLOM SLA ---
const addColumn = () => {
    let newColName = "1 Hari";

    // Logika Auto-Increment Angka pada Nama Kolom (Tanpa Popup)
    if (columns.value.length > 0) {
        const lastCol = columns.value[columns.value.length - 1];
        // Mencari angka di awal teks (contoh: "1 Hari" -> dapet "1" dan "Hari")
        const match = lastCol.match(/^(\d+)\s*(.*)$/);
        if (match) {
            const nextNum = parseInt(match[1], 10) + 1;
            newColName = `${nextNum} ${match[2]}`; // Hasil: "2 Hari", "3 Jam", dll
        } else {
            newColName = `${lastCol} 2`;
        }
    }

    // Cegah duplikat nama
    let counter = 1;
    let finalColName = newColName;
    while (columns.value.includes(finalColName)) {
        finalColName = `${newColName} (${counter})`;
        counter++;
    }

    columns.value.push(finalColName);

    rows.value.forEach(row => {
        const rowKey = String(row.min);
        if (!matrixData.value[rowKey]) matrixData.value[rowKey] = {};
        matrixData.value[rowKey][finalColName] = { nilai: 0, tipe: 'nominal' };
    });

    emitChanges();
};

// Fungsi Edit Header Kolom SLA (Mulus kayak Excel)
const updateColumnName = (colIndex, newName) => {
    newName = newName.trim();
    const oldName = columns.value[colIndex];

    if (!newName || oldName === newName) {
        columns.value = [...columns.value]; // Revert kalau kosong/sama
        return;
    }

    if (columns.value.includes(newName)) {
        alert('Nama Estimasi Pengerjaan sudah ada!');
        columns.value = [...columns.value]; // Revert kalau duplikat
        return;
    }

    // Pindahkan Data Key di MatrixData
    rows.value.forEach(row => {
        const rowKey = String(row.min);
        if (matrixData.value[rowKey] && matrixData.value[rowKey][oldName] !== undefined) {
            matrixData.value[rowKey][newName] = matrixData.value[rowKey][oldName];
            delete matrixData.value[rowKey][oldName];
        }
    });

    columns.value[colIndex] = newName;
    emitChanges();
};

const removeColumn = (colIndex) => {
    if (columns.value.length <= 1) return;
    const colName = columns.value[colIndex];

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
                <!-- Popup Modal Dihapus, Langsung Auto-Increment SLA -->
                <button type="button" @click="addColumn" class="btn btn-xs btn-outline btn-primary font-bold">
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
                        <th class="border-r border-base-300 font-black text-[10px] uppercase p-3 w-32 tracking-widest">Min Qty</th>
                        <th class="border-r border-base-300 font-black text-[10px] uppercase p-3 w-32 tracking-widest">Max Qty</th>

                        <!-- Header Kolom SLA BISA DIEDIT LANGSUNG -->
                        <th v-for="(col, colIdx) in columns" :key="'head-'+colIdx" class="border-r border-base-300 p-0 relative min-w-35">
                            <div class="flex items-center justify-between h-full bg-base-200/70 focus-within:bg-base-200 transition-colors group">
                                <input
                                    type="text"
                                    :value="col"
                                    @change="e => updateColumnName(colIdx, e.target.value)"
                                    class="w-full h-full bg-transparent border-0 text-[11px] font-black uppercase text-center focus:ring-2 focus:ring-inset focus:ring-primary py-3 outline-none"
                                    title="Edit Nama Estimasi"
                                />
                                <!-- Icon Tong Sampah cuma muncul pas di Hover -->
                                <button type="button" @click="removeColumn(colIdx)" class="text-error hover:bg-error/20 px-3 h-full transition-all absolute right-0 opacity-0 group-hover:opacity-100" title="Hapus Kolom SLA">
                                    <Trash2 class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </th>

                        <th class="p-3 w-16 font-black text-[10px] uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, rowIdx) in rows" :key="rowIdx" class="hover:bg-base-200/30 border-b border-base-300/50">
                        <!-- Min Qty -->
                        <td class="border-r border-base-300 p-0 relative focus-within:bg-base-100">
                            <input
                                v-model.number="row.min"
                                @change="emitChanges"
                                type="number"
                                min="1"
                                class="w-full h-full text-xs font-bold text-center bg-transparent border-0 focus:ring-2 focus:ring-inset focus:ring-primary py-3 outline-none"
                            />
                        </td>
                        <!-- Max Qty -->
                        <td class="border-r border-base-300 p-0 relative focus-within:bg-base-100">
                            <input
                                v-model.number="row.max"
                                @change="emitChanges"
                                type="number"
                                min="0"
                                class="w-full h-full text-xs font-bold text-center bg-transparent border-0 focus:ring-2 focus:ring-inset focus:ring-primary py-3 outline-none"
                                placeholder="0 = Inf"
                            />
                        </td>

                        <!-- Cell Harga (Excel Style) -->
                        <td v-for="(col, colIdx) in columns" :key="'cell-'+rowIdx+'-'+colIdx" class="border-r border-base-300 p-0 relative group">
                            <div v-if="matrixData[String(row.min)]?.[col]" class="w-full h-full">
                                <!-- Pakai CustomInputNumber lu, tapi di custom CSS-nya biar nyatu sama Table -->
                                <CustomInputNumber
                                    v-model="matrixData[String(row.min)][col].nilai"
                                    @update:modelValue="emitChanges"
                                    prefix="Rp"
                                    :min="0"
                                    class="w-full h-full border-0! rounded-none! focus:ring-2 focus:ring-inset focus:ring-primary text-center py-2 bg-transparent"
                                    placeholder="0"
                                />
                            </div>
                        </td>

                        <!-- Tombol Hapus Baris -->
                        <td class="p-0 text-center relative group">
                            <button type="button" @click="removeRow(rowIdx)" class="btn btn-ghost btn-block h-full rounded-none! text-error hover:bg-error/10">
                                <Trash2 class="w-4 h-4 mx-auto" />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
