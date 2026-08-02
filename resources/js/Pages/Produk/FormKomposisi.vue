<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomTableForm from '@/Components/CustomTableForm.vue';
import CustomInputNumber from '@/Components/Form/CustomInputNumber.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import CustomSelectSearch from '@/Components/Form/CustomSelectSearch.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    sku: Object,
    bahan_baku: Array,
    pilihan_finishing: Array,
});

const form = useForm({
    id_produk: props.sku.id_produk,
    komposisi: props.sku.komposisi?.map(k => ({
        id_bahan_baku: k.id_bahan_baku,
        id_pilihan_finishing: k.id_pilihan_finishing || '',
        jumlah_pakai: k.jumlah_pakai,
    })) || []
});

const addRow = () => {
    form.komposisi.push({
        id_bahan_baku: '',
        id_pilihan_finishing: '',
        jumlah_pakai: 1
    });
};

// Modifikasi data dropdown untuk pilihan finishing
const opsiPenggunaan = computed(() => {
    let options = [{ id_pilihan: '', nama: 'BAHAN UTAMA (Wajib)' }];
    if (props.pilihan_finishing) {
        props.pilihan_finishing.forEach(pf => {
            options.push({ id_pilihan: pf.id_pilihan_finishing, nama: `Finishing: ${pf.nama_pilihan}` });
        });
    }
    return options;
});

// Fungsi buat nyari harga asli bahan dari array props.bahan_baku
const getHargaBahan = (id_bahan) => {
    const bahan = props.bahan_baku.find(b => b.id_bahan_baku === id_bahan);
    return bahan ? bahan.harga_beli : 0;
};

// Fungsi narik satuan bahan
const getSatuanBahan = (id_bahan) => {
    const bahan = props.bahan_baku.find(b => b.id_bahan_baku === id_bahan);
    return bahan ? bahan.satuan : '-';
};

// Menghitung estimasi total HPP seluruh bahan untuk 1 produk
const totalHPP = computed(() => {
    return form.komposisi.reduce((acc, item) => {
        return acc + (item.jumlah_pakai * getHargaBahan(item.id_bahan_baku));
    }, 0);
});

const submit = () => {
    form.post(route('sku.syncKomposisi', props.sku.id_sku));
};
</script>

<template>
    <Head title="Resep Komposisi SKU" />
    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('produk.detailSku', sku.id_produk)" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Komposisi Produk: {{ sku.nama_sku }}
                    </h2>
                </div>
            </div>
        </template>
        <div class="max-w-6xl mx-auto py-8 px-4">
            <!-- REVISI 1: Responsive padding p-4 untuk mobile, p-8 untuk desktop -->
            <div class="p-4 md:p-8 border rounded-3xl shadow-xl bg-base-100 border-base-300">

                <!-- REVISI 2: Flexbox responsif (flex-col di mobile, flex-row di desktop) -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                    <div>
                        <h2 class="text-lg font-black uppercase tracking-tight">Resep Bahan Baku (BOM)</h2>
                        <p class="text-[10px] opacity-50 uppercase font-bold tracking-widest mt-1">Input jumlah pemakaian bahan untuk 1 Pcs / Lembar produk ini</p>
                    </div>

                    <div class="px-6 py-4 w-full md:w-auto border bg-base-200 border-base-300 rounded-2xl md:text-right">
                        <p class="text-[9px] font-black uppercase tracking-widest opacity-50">Estimasi Total HPP Keseluruhan</p>
                        <p class="text-xl md:text-lg font-black text-primary mt-1 md:mt-0">Rp {{ totalHPP.toLocaleString() }}</p>
                    </div>
                </div>

                <form @submit.prevent="submit">
                    <CustomTableForm
                        v-model="form.komposisi"
                        :headers="['Pilih Bahan Baku', 'Digunakan Untuk', 'Jumlah Pemakaian', 'Estimasi HPP']"
                        @add="addRow"
                    >
                        <template #row="{ row, index }">
                            <!-- REVISI 3: Ganti class width mutlak jadi min-width biar solid saat discroll -->
                            <td class="px-4 py-4 min-w-62.5">
                                <CustomSelectSearch
                                    v-model="form.komposisi[index].id_bahan_baku"
                                    :options="bahan_baku"
                                    label-key="nama_bahan_baku"
                                    value-key="id_bahan_baku"
                                    placeholder="Cari & Pilih Bahan..."
                                    :add-option="false"
                                />
                            </td>

                            <td class="px-4 py-4 min-w-50">
                                <CustomSelect
                                    v-model="form.komposisi[index].id_pilihan_finishing"
                                    :options="opsiPenggunaan"
                                    label-key="nama"
                                    value-key="id_pilihan"
                                    placeholder="Pilih Peruntukan..."
                                />
                            </td>

                            <td class="px-4 py-4 min-w-37.5">
                                <div class="flex items-center gap-2">
                                    <CustomInputNumber
                                        v-model="form.komposisi[index].jumlah_pakai"
                                        :min="0.01"
                                        class="w-full"
                                    />
                                    <span class="text-[10px] font-black uppercase opacity-40 shrink-0 w-12 whitespace-nowrap">
                                        {{ getSatuanBahan(row.id_bahan_baku) }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-4 py-4 text-right min-w-45">
                                <div class="text-sm font-bold text-base-content/50 flex flex-col">
                                    <span>Rp {{ (row.jumlah_pakai * getHargaBahan(row.id_bahan_baku)).toLocaleString() }}</span>
                                    <span class="text-[8px] opacity-50 mt-1 uppercase">@ Rp {{ getHargaBahan(row.id_bahan_baku).toLocaleString() }} / {{ getSatuanBahan(row.id_bahan_baku) }}</span>
                                </div>
                            </td>
                        </template>
                    </CustomTableForm>

                    <!-- REVISI 4: Tata letak tombol jadi flex-col-reverse (mobile) dan flex-row (desktop) -->
                    <div class="flex flex-col-reverse md:flex-row gap-4 pt-8 mt-10 border-t border-base-300">
                        <CustomButton type="link" :href="route('produk.detailSku', sku.id_produk)" variant="secondary" class="w-full md:w-auto py-4 rounded-2xl md:px-10" block>
                            Kembali
                        </CustomButton>
                        <CustomButton @click="submit" variant="primary" class="w-full md:flex-1 py-4 rounded-2xl" :disabled="form.processing" block>
                            Simpan Komposisi & Kalkulasi HPP
                        </CustomButton>
                    </div>
                </form>
            </div>
        </div>
    </StafLayout>
</template>
