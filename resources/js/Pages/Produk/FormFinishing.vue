<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomTableForm from '@/Components/CustomTableForm.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomInputNumber from '@/Components/Form/CustomInputNumber.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import CustomCheckbox from '@/Components/Form/CustomCheckbox.vue'; // <-- Tambahkan import ini

const props = defineProps({
    sku: Object,
    finishings: Array,
});

const form = useForm({
    id_produk: props.sku.id_produk,
    finishing: props.sku.sku_finishing?.map(f => ({
        id_pilihan_finishing: f.id_pilihan_finishing,
        nama_pilihan: f.pilihan_finishing?.nama_pilihan,
        minimum_pesan: f.minimum_pesan,
        harga_tambahan: f.harga_tambahan,
        tipe: f.tipe ?? 'persen',
        kali_jumlah_pesan: f.kali_jumlah_pesan ? true : false,
    })) || []
});

const tipeOptions = [
    { label: 'Rupiah (Rp)', value: 'nominal' },
    { label: 'Persen (%)', value: 'persen' }
];

const addFinishingToTable = (pilihan) => {
    const exists = form.finishing.find(f => f.id_pilihan_finishing === pilihan.id_pilihan_finishing);
    if (exists) return alert('Pilihan ini sudah ada di tabel!');

    form.finishing.push({
        id_pilihan_finishing: pilihan.id_pilihan_finishing,
        nama_pilihan: pilihan.nama_pilihan,
        minimum_pesan: 1,
        harga_tambahan: 0,
        tipe: 'persen',
        kali_jumlah_pesan: false
    });
};

const submit = () => {
    form.post(route('sku.syncFinishing', props.sku.id_sku));
};
</script>

<template>
    <Head title="Setting Finishing SKU" />
    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Finishing: <span class="text-primary">{{ sku.nama_sku }}</span>
            </h2>
        </template>

        <div class="max-w-360 mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-4">

                <!-- Sidebar Kiri: Pilihan Finishing -->
                <div class="space-y-4 lg:col-span-1">
                    <div v-for="f in finishings" :key="f.id" class="p-5 border shadow-xl rounded-2xl bg-base-100 border-base-300">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-primary mb-4 border-b border-base-300 pb-2">
                            {{ f.nama_finishing }}
                        </h3>
                        <div class="space-y-2">
                            <button
                                v-for="p in f.pilihan_finishing" :key="p.id_pilihan_finishing"
                                @click="addFinishingToTable(p)"
                                type="button"
                                class="flex items-center justify-between w-full p-3 text-xs font-bold uppercase transition-all border text-left rounded-xl border-base-300 bg-base-200/50 hover:bg-primary/10 hover:border-primary group"
                            >
                                <span>{{ p.nama_pilihan }}</span>
                                <span class="opacity-0 group-hover:opacity-100 text-primary">+</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Konten Kanan: Tabel Form -->
                <div class="lg:col-span-3">
                    <div class="p-10 border shadow-xl rounded-2xl bg-base-100 border-base-300">

                        <div class="p-4 mb-8 border border-info/20 bg-info/5 rounded-2xl">
                            <p class="text-[10px] font-black uppercase tracking-widest text-info opacity-70">Petunjuk Konfigurasi</p>
                            <p class="text-sm font-medium text-base-content/60">Pilih jenis finishing di panel samping kiri, lalu atur biaya tambahan, tipe penyesuaian (Nominal Rp/Persen %), dan centang "Kali Qty" jika biaya dikalikan dengan jumlah pesanan.</p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-8">
                            <CustomTableForm
                                v-model="form.finishing"
                                label="Finishing Terpilih"
                                :headers="['Jenis Finishing', 'Min Qty', 'Tipe Penyesuaian', 'Kali Qty', 'Biaya Tambahan']"
                            >
                                <template #row="{ row, index }">
                                    <td class="px-4 py-4 min-w-37.5">
                                        <div class="text-sm font-black uppercase tracking-widest text-primary leading-tight">
                                            {{ row.nama_pilihan }}
                                        </div>
                                        <div class="text-[9px] font-bold opacity-30 italic">ID: {{ row.id_pilihan_finishing }}</div>
                                    </td>

                                    <td class="px-2 py-4 w-24 border-l border-base-300/30">
                                        <input
                                            v-model.number="form.finishing[index].minimum_pesan"
                                            type="number"
                                            class="w-full text-sm font-bold text-center bg-transparent border-none focus:ring-0"
                                        />
                                    </td>

                                    <td class="px-2 py-4 w-48 border-l border-base-300/30">
                                        <CustomSelect
                                            v-model="form.finishing[index].tipe"
                                            :options="tipeOptions"
                                            label-key="label"
                                            value-key="value"
                                        />
                                    </td>

                                    <!-- Implementasi CustomCheckbox di sini -->
                                    <td class="px-2 py-4 w-24 border-l border-base-300/30">
                                        <CustomCheckbox
                                            v-model="form.finishing[index].kali_jumlah_pesan"
                                            color="primary"
                                        />
                                    </td>

                                    <td class="px-4 py-4 min-w-50 border-l border-base-300/30">
                                        <CustomInputNumber
                                            v-model="form.finishing[index].harga_tambahan"
                                            :prefix="form.finishing[index].tipe === 'nominal' ? 'Rp' : ''"
                                            :suffix="form.finishing[index].tipe === 'persen' ? '%' : ''"
                                            :min="0"
                                            :max="form.finishing[index].tipe === 'persen' ? 100 : undefined"
                                        />
                                    </td>
                                </template>
                            </CustomTableForm>

                            <div class="flex flex-col items-center gap-4 pt-8 mt-10 border-t border-base-300 sm:flex-row">
                                <CustomButton
                                    type="submit"
                                    class="flex-1 w-full py-4 sm:w-auto rounded-2xl"
                                    :disabled="form.processing"
                                >
                                    Simpan Setting Finishing
                                </CustomButton>
                                <CustomButton
                                    type="link"
                                    :href="route('produk.detailSku', sku.id_produk)"
                                    variant="secondary"
                                    class="w-full py-4 sm:w-auto rounded-2xl"
                                >
                                    Kembali
                                </CustomButton>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </StafLayout>
</template>
