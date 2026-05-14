<script setup>
import { ref } from 'vue';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomTableForm from '@/Components/CustomTableForm.vue';
import { Head, useForm } from '@inertiajs/vue3';
import CustomInputNumber from '@/Components/CustomInputNumber.vue';

const props = defineProps({
    sku: Object,
    finishings: Array,
});

const form = useForm({
    // Load data lama dari database
    finishing: props.sku.sku_finishing?.map(f => ({
        id_pilihan_finishing: f.id_pilihan_finishing,
        nama_pilihan: f.pilihan_finishing?.nama_pilihan,
        minimum_pesan: f.minimum_pesan,
        harga_tambahan: f.harga_tambahan,
    })) || []
});

const addFinishingToTable = (pilihan) => {
    // Cek biar gak double input pilihan yang sama
    const exists = form.finishing.find(f => f.id_pilihan_finishing === pilihan.id_pilihan_finishing);
    if (exists) return alert('Pilihan ini sudah ada di tabel!');

    form.finishing.push({
        id_pilihan_finishing: pilihan.id_pilihan_finishing,
        nama_pilihan: pilihan.nama_pilihan,
        minimum_pesan: 1,
        harga_tambahan: 0
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
            <h2 class="text-xl font-bold uppercase tracking-tighter italic">
                Finishing: <span class="text-primary">{{ sku.nama_sku }}</span>
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-8 px-4">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

                <div class="lg:col-span-1 space-y-4">
                    <div v-for="f in finishings" :key="f.id" class="p-5 border rounded-3xl shadow-lg bg-base-100 border-base-300">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-primary mb-4 border-b border-base-300 pb-2">
                            {{ f.nama_finishing }}
                        </h3>
                        <div class="space-y-2">
                            <button
                                v-for="p in f.pilihan_finishing" :key="p.id_pilihan_finishing"
                                @click="addFinishingToTable(p)"
                                type="button"
                                class="w-full text-left p-3 text-xs font-bold uppercase rounded-xl border border-base-300 bg-base-200/50 hover:bg-primary/10 hover:border-primary transition-all flex justify-between items-center group"
                            >
                                <span>{{ p.nama_pilihan }}</span>
                                <span class="opacity-0 group-hover:opacity-100 text-primary">+</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="p-8 border rounded-3xl shadow-xl bg-base-100 border-base-300">
                        <div class="mb-6">
                            <h2 class="text-lg font-black uppercase tracking-tight">Finishing Terpilih</h2>
                            <p class="text-[10px] opacity-50 uppercase font-bold tracking-widest">Atur biaya tambahan per jenis finishing</p>
                        </div>

                        <CustomTableForm
                            v-model="form.finishing"
                            :headers="['Jenis Finishing', 'Min. Pesan', 'Biaya Tambahan']"
                        >
                            <template #row="{ row, index }">
                                <td class="px-4 py-4">
                                    <div class="text-sm font-black uppercase tracking-widest text-primary leading-tight">
                                        {{ row.nama_pilihan }}
                                    </div>
                                    <div class="text-[9px] font-bold opacity-30 italic">ID: {{ row.id_pilihan_finishing }}</div>
                                </td>
                                <td class="px-4 py-4 min-w-37.5">
                                    <CustomInputNumber v-model="form.finishing[index].minimum_pesan" :min="1" />
                                </td>
                                <td class="px-4 py-4 min-w-50">
                                    <CustomInputNumber v-model="form.finishing[index].harga_tambahan" :min="0" prefix="Rp" />
                                </td>
                            </template>
                        </CustomTableForm>

                        <div class="flex gap-4 pt-8 mt-10 border-t border-base-300">
                            <CustomButton @click="submit" class="flex-1 py-4 rounded-2xl" :disabled="form.processing">
                                Simpan Setting Finishing
                            </CustomButton>
                            <CustomButton type="link" :href="route('produk.detailSku', sku.id_produk)" variant="secondary" class="py-4 rounded-2xl">
                                Kembali
                            </CustomButton>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </StafLayout>
</template>
