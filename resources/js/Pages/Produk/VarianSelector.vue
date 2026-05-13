<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomCheckbox from '@/Components/CustomCheckbox.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ produk: Object, master_varians: Array });

const form = useForm({
    selected_varians: (props.produk.produk_varian ?? []).map(v => v.id_varian)
});

const handleCheckboxChange = (id, isChecked) => {
    if (isChecked) {
        form.selected_varians.push(id);
    } else {
        const index = form.selected_varians.indexOf(id);
        if (index > -1) form.selected_varians.splice(index, 1);
    }
};

const submit = () => {
    form.post(route('produk.syncVarian', props.produk.id_produk));
};
</script>

<template>
    <Head title="Pilih Varian Produk" />
    <StafLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-base-content">
                Pilih Varian {{ produk.nama_produk }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 border rounded-lg shadow-xl bg-base-100 border-base-300">
                    <div class="mb-6">
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-primary mb-1">Daftar Master Varian</h3>
                        <p class="text-sm text-base-content/50">Pilih satu atau lebih varian yang akan digunakan untuk produk ini.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label
                            v-for="v in master_varians"
                            :key="v.id_varian"
                            :class="[
                                'relative flex items-center p-5 rounded-2xl border-2 transition-all duration-200 cursor-pointer group',
                                form.selected_varians.includes(v.id_varian)
                                    ? 'border-primary bg-primary/5 ring-1 ring-primary/20'
                                    : 'border-base-300 bg-base-100 hover:border-primary/40 hover:bg-base-200/50'
                            ]"
                        >
                            <div class="flex items-center w-full">
                                <CustomCheckbox
                                    :label="v.nama_varian"
                                    :modelValue="form.selected_varians.includes(v.id_varian)"
                                    @update:modelValue="(val) => handleCheckboxChange(v.id_varian, val)"
                                />
                            </div>

                            <div
                                class="absolute transition-all duration-300 right-5"
                                :class="form.selected_varians.includes(v.id_varian) ? 'scale-100 opacity-100' : 'scale-50 opacity-0'"
                            >
                                <div class="p-1 rounded-full bg-primary text-primary-content">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </label>

                        <div v-if="master_varians.length === 0" class="py-12 text-center border-2 border-dashed col-span-full border-base-300 rounded-3xl">
                            <p class="text-sm italic font-bold opacity-30">Belum ada data master varian.</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 pt-8 mt-12 border-t sm:flex-row border-base-300">
                        <CustomButton
                            @click="submit"
                            class="flex-1 w-full py-4 rounded-2xl"
                            :disabled="form.processing"
                        >
                            <template #icon v-if="!form.processing">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </template>
                            Simpan Konfigurasi
                        </CustomButton>

                        <CustomButton
                            type="link"
                            :href="route('produk.index')"
                            variant="secondary"
                            class="w-full px-10 py-4 sm:w-auto rounded-2xl"
                        >
                            Batal
                        </CustomButton>
                    </div>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
