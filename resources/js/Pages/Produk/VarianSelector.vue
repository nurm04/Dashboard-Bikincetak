<script setup>
import { ref, computed } from 'vue';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import CustomCheckbox from '@/Components/Form/CustomCheckbox.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Search } from 'lucide-vue-next'; // <-- Tambahkan icon Search

const props = defineProps({ produk: Object, master_varians: Array });

const form = useForm({
    selected_varians: (props.produk.produk_varian ?? []).map(v => v.id_varian)
});

// STATE & FUNGSI PENCARIAN
const searchQuery = ref('');

const filteredVarians = computed(() => {
    if (!searchQuery.value) return props.master_varians;
    const query = searchQuery.value.toLowerCase();

    return props.master_varians.filter(v =>
        v.nama_varian.toLowerCase().includes(query)
    );
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
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('produk.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        Pilih Varian: {{ produk.nama_produk }}
                    </h2>
                </div>
            </div>
        </template>

        <!-- Padding luar disamakan standarnya (p-4 mobile, max-w dilebarkan sedikit biar lega) -->
        <div class="max-w-4xl px-4 py-8 mx-auto sm:px-6 lg:px-8">

            <!-- Kotak UI dengan p-4 (mobile) & p-10 (desktop), plus rounded-2xl -->
            <div class="p-4 border shadow-xl md:p-10 rounded-2xl bg-base-100 border-base-300">

                <!-- Layout Header & Search: Sejajar di Desktop, Numpuk di Mobile -->
                <div class="flex flex-col justify-between gap-4 mb-8 md:flex-row md:items-center">
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-primary mb-1">Daftar Master Varian</h3>
                        <p class="text-sm text-base-content/50">Pilih satu atau lebih varian yang akan digunakan untuk produk ini.</p>
                    </div>

                    <div class="w-full md:w-72 shrink-0">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                <Search class="w-4 h-4 opacity-40 text-base-content" />
                            </div>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari varian..."
                                class="w-full py-3 pr-4 text-sm transition-all border shadow-sm pl-11 border-base-300 bg-base-100 rounded-2xl focus:ring-primary focus:border-primary"
                            />
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <label
                        v-for="v in filteredVarians"
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

                    <!-- Empty State Handling -->
                    <div v-if="filteredVarians.length === 0" class="py-12 text-center border-2 border-dashed col-span-full border-base-300 rounded-2xl">
                        <p class="text-[10px] font-black uppercase tracking-widest opacity-40">
                            {{ master_varians.length === 0 ? 'Belum ada data master varian.' : 'Pencarian tidak ditemukan.' }}
                        </p>
                    </div>
                </div>

                <!-- Layout Tombol Responsif -->
                <div class="flex flex-col items-center gap-4 pt-8 mt-12 border-t border-base-300 sm:flex-row">
                    <CustomButton
                        @click="submit"
                        class="flex-1 w-full py-4 rounded-2xl sm:w-auto"
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
                        class="w-full py-4 sm:px-10 sm:w-auto rounded-2xl"
                    >
                        Batal
                    </CustomButton>
                </div>
            </div>
        </div>
    </StafLayout>
</template>
