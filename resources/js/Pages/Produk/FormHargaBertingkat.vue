<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import FormMatriksHarga from '@/Components/Form/FormMatriksHarga.vue'; // 👈 IMPORT KOMPONEN BARU
import CustomButton from '@/Components/Form/CustomButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    sku: Object,
});

const form = useForm({
    id_produk: props.sku.id_produk,
    hargas: props.sku.harga_bertingkat?.length > 0
        ? props.sku.harga_bertingkat.map(h => ({
            pengerjaan: h.pengerjaan ?? 'Reguler',
            min: h.min,
            max: h.max,
            tipe: h.tipe ?? 'nominal',
            nilai: h.nilai ?? 0
          }))
        : [{ pengerjaan: 'Reguler', min: 1, max: 1, tipe: 'nominal', nilai: 0 }],
});

const submit = () => {
    form.post(route('sku.syncHargaBertingkat', props.sku.id_sku));
};
</script>

<template>
    <Head title="Matriks Harga & SLA" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('produk.detailSku', sku.id_produk)" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Matriks Harga & SLA: {{ sku.nama_sku }}
                    </h2>
                </div>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-8 border rounded-lg shadow-xl bg-base-100 border-base-300">

                    <div class="p-4 mb-6 border border-info/20 bg-info/5 rounded-2xl">
                        <p class="text-[10px] font-black uppercase tracking-widest text-info opacity-70">Petunjuk Spreadsheet Mode</p>
                        <p class="text-sm font-medium text-base-content/60">Kolom ke kanan adalah Estimasi SLA, baris ke bawah adalah rentang Qty. Data ini langsung sinkron dengan format CSV matriks.</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- 👇 KOMPONEN TABEL MATRIKS DINAMIS 👇 -->
                        <FormMatriksHarga v-model="form.hargas" />

                        <div class="flex flex-col items-center gap-4 pt-6 border-t border-base-300 sm:flex-row">
                            <CustomButton
                                type="submit"
                                variant="primary"
                                class="flex-1 w-full py-4 sm:w-auto rounded-2xl font-black"
                                :disabled="form.processing"
                            >
                                Simpan Seluruh Matriks Harga
                            </CustomButton>

                            <CustomButton
                                type="link"
                                :href="route('produk.detailSku', sku.id_produk)"
                                variant="secondary"
                                class="w-full py-4 sm:w-auto rounded-2xl font-bold"
                            >
                                Batal
                            </CustomButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
