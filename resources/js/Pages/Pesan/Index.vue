<script setup>
import { ref, watch, computed } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomTable from '@/Components/CustomTable.vue';
import { alertStore } from '@/Utils/alertStore';
import CustomButton from '@/Components/CustomButton.vue';
import CustomInputNumber from '@/Components/CustomInputNumber.vue';
import CustomInput from '@/Components/CustomInput.vue';
import CustomInputSearch from '@/Components/CustomInputSearch.vue';
import CustomSelect from '@/Components/CustomSelect.vue';

const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const props = defineProps({
    pesanan: Array,
    enumPembayaran: Array,
    enumOperasional: Array,
    filters: Object
});

const headers = ['ID Pesanan', 'Customer', 'Total Tagihan', 'Pembayaran', 'Operasional', 'Aksi'];

const showBayarSebagianModal = ref(false);
const selectedPesanId = ref(null);
const selectedPesan = ref(null);

const formOperasional = useForm({ status_operasional: '' });
const formPembayaran = useForm({ status_pembayaran: '', nominal_bayar: null });

const search = ref(props.filters?.search || '');
const filterPembayaran = ref(props.filters?.status_pembayaran || 'semua');
const filterOperasional = ref(props.filters?.status_operasional || 'semua');

const operasionalOptions = computed(() => {
    let opts = [{ value: 'semua', label: 'SEMUA OPERASIONAL' }];
    if (props.enumOperasional) {
        props.enumOperasional.forEach(opt => {
            if (opt !== 'keranjang') {
                opts.push({ value: opt, label: formatEnum(opt).toUpperCase() });
            }
        });
    }
    return opts;
});

const pembayaranOptions = computed(() => {
    let opts = [{ value: 'semua', label: 'SEMUA PEMBAYARAN' }];
    if (props.enumPembayaran) {
        props.enumPembayaran.forEach(opt => {
            opts.push({ value: opt, label: formatEnum(opt).toUpperCase() });
        });
    }
    return opts;
});

watch(
    [search, filterPembayaran, filterOperasional],
    debounce(([newSearch, newPembayaran, newOperasional]) => {
        router.get(route('pesan.index'), {
            search: newSearch,
            status_pembayaran: newPembayaran,
            status_operasional: newOperasional
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300)
);


const sudahAdaPembayaran = (pesan) => (pesan.total_dibayar ?? 0) > 0;
const sudahLunas = (pesan) => (pesan.total_dibayar ?? 0) >= (pesan.total_tagihan ?? 0);

const resetModal = () => {
    formPembayaran.reset();
    formPembayaran.clearErrors();
    selectedPesan.value = null;
    selectedPesanId.value = null;
    showBayarSebagianModal.value = false;
};

const updateOperasional = (id_pesan, value) => {
    formOperasional.status_operasional = value;
    formOperasional.put(route('pesan.updateOperasional', id_pesan), {
        preserveScroll: true,
        onSuccess: () => alertStore.show('Status Operasional berhasil diperbarui!', 'success'),
        onError: () => alertStore.show('Gagal memperbarui status operasional!', 'error')
    });
};

const updatePembayaran = (pesan, value) => {
    if (sudahLunas(pesan)) return;

    if (value === 'dibayar_sebagian') {
        selectedPesan.value = pesan;
        selectedPesanId.value = pesan.id_pesan;
        formPembayaran.reset();
        formPembayaran.status_pembayaran ='dibayar_sebagian';
        showBayarSebagianModal.value = true;
        return;
    }

    formPembayaran.status_pembayaran = value;
    formPembayaran.put(route('pesan.updatePembayaran', pesan.id_pesan), {
        preserveScroll: true,
        onSuccess: () => alertStore.show('Status Pembayaran berhasil diperbarui!','success'),
        onError: () => alertStore.show('Gagal memperbarui status pembayaran!','error')
    });
};

const submitBayarSebagian = () => {
    formPembayaran.put(route('pesan.updatePembayaran',selectedPesanId.value), {
        preserveScroll: true,
        onSuccess: () => {
            resetModal();
            showBayarSebagianModal.value = false;
            alertStore.show('Pembayaran berhasil dicatat!','success');
        },
        onError: () => alertStore.show('Gagal mencatat pembayaran!','error')
    });
};

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka);
};

const formatEnum = (text) => {
    if (!text) return '';
    return text.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const getAllowedOperasional = (statusSaatIni) => {
    if (!props.enumOperasional || props.enumOperasional.length === 0) return [];

    const alurProses = props.enumOperasional;
    const currentIndex = alurProses.indexOf(statusSaatIni);
    const indexBatal = alurProses.length - 1;
    const indexSelesai = alurProses.length - 2;

    if (currentIndex === -1 || currentIndex >= indexSelesai) {
        return [statusSaatIni];
    }

    return [statusSaatIni, alurProses[currentIndex + 1], alurProses[indexBatal]];
};
</script>

<template>
    <Head title="Manajemen Pesanan" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Daftar Pesanan & Transaksi
            </h2>
        </template>

        <div class="min-h-screen px-4 py-6 mx-auto sm:px-6 lg:px-8 max-w-7xl">

            <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between">
                <Link :href="route('pos.katalog')" class="font-black tracking-wider shadow-md btn btn-primary rounded-xl shrink-0">
                    + Tambah Pesanan (POS)
                </Link>
            </div>
            <div class="mb-3 flex flex-col w-full gap-3 sm:flex-row sm:items-center md:w-auto">
                <CustomInputSearch
                    v-model="search"
                    class="w-full sm:w-64"
                    placeholder="Cari ID / Nama Customer..."
                />
                <div class="w-full sm:w-48 md:w-56">
                    <CustomSelect
                        v-model="filterPembayaran"
                        :options="pembayaranOptions"
                        valueKey="value"
                        labelKey="label"
                        placeholder="Semua Pembayaran"
                    />
                </div>
                <div class="w-full sm:w-48 md:w-56">
                    <CustomSelect
                        v-model="filterOperasional"
                        :options="operasionalOptions"
                        valueKey="value"
                        labelKey="label"
                        placeholder="Semua Operasional"
                    />
                </div>
            </div>

            <CustomTable :headers="headers">
                <tr
                    v-for="p in pesanan"
                    :key="p.id_pesan"
                    :class="p.status_operasional === 'batal' ? 'bg-base-200/30' : 'hover:bg-base-200/50'"
                    class="transition-colors"
                >
                    <td class="px-6 py-4 font-mono text-xs font-bold" :class="p.status_operasional === 'batal' ? 'opacity-40 line-through' : 'text-primary'">
                        {{ p.id_pesan }}
                    </td>

                    <td class="px-6 py-4" :class="p.status_operasional === 'batal' ? 'opacity-40 line-through' : ''">
                        <div class="font-bold text-base-content">{{ p.customer?.user?.name || 'Walk-in / Umum' }}</div>
                        <div class="text-[10px] opacity-40 font-mono tracking-wider">{{ p.id_customer }}</div>
                    </td>

                    <td class="px-6 py-4 text-sm font-black" :class="p.status_operasional === 'batal' ? 'opacity-40 line-through' : 'text-base-content'">
                        {{ formatRupiah(p.total_transfer ?? p.total_tagihan) }}
                    </td>

                    <td class="px-6 py-4" :class="p.status_operasional === 'batal' ? 'opacity-50' : ''">
                        <select
                            class="select select-bordered select-sm text-[10px] font-black uppercase tracking-wider rounded-xl shadow-sm"
                            :class="
                                p.status_pembayaran === 'lunas' ? 'text-success border-success/30 bg-success/5'
                                : p.status_pembayaran === 'dibayar_sebagian' ? 'text-warning border-warning/30 bg-warning/5' : 'text-error border-error/30 bg-error/5'
                            "
                            :value="p.status_pembayaran"
                            @change="updatePembayaran(p, $event.target.value)"
                            :disabled="formPembayaran.processing || sudahLunas(p) || p.status_operasional === 'batal'"
                        >
                            <option
                                v-for="status in enumPembayaran"
                                :key="status"
                                :value="status"
                                :disabled="(status === 'belum_lunas' && sudahAdaPembayaran(p)) || (status === 'dibayar_sebagian' && sudahLunas(p))"
                            >
                                {{ status.replace(/_/g, ' ').toUpperCase() }}
                            </option>
                        </select>
                        <div class="mt-1 text-[10px] font-bold opacity-60" :class="p.status_operasional === 'batal' ? 'line-through' : ''">
                            {{ formatRupiah(p.total_dibayar ?? 0) }}
                            /
                            {{ formatRupiah(p.total_tagihan ?? 0) }}
                        </div>
                    </td>

                    <td class="px-6 py-4" :class="p.status_operasional === 'batal' ? 'opacity-50' : ''">
                        <select
                            class="select select-bordered select-sm text-[10px] font-black uppercase tracking-wider text-base-content rounded-xl shadow-sm bg-base-100 border-base-300"
                            :value="p.status_operasional"
                            @change="updateOperasional(p.id_pesan, $event.target.value)"
                            :disabled="formOperasional.processing || p.status_operasional === 'batal' || p.status_operasional === 'selesai'"
                        >
                            <option
                                v-for="status in getAllowedOperasional(p.status_operasional)"
                                :key="status"
                                :value="status"
                                :disabled="status === p.status_operasional"
                            >
                                {{ formatEnum(status) }} <template v-if="status === p.status_operasional"></template>
                            </option>
                        </select>
                    </td>

                    <td class="px-6 py-4 text-center">
                        <CustomButton type="link" :href="route('pesan.detail', p.id_pesan)" variant="info" size="sm">
                            Detail
                        </CustomButton>
                    </td>
                </tr>

                <tr v-if="pesanan.length === 0">
                    <td colspan="6" class="px-6 py-20 text-center">
                        <div class="flex flex-col items-center gap-2 opacity-30">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <p class="text-xs font-black tracking-widest uppercase">
                                {{ search || filterOperasional !== 'semua' || filterPembayaran !== 'semua' ? 'Pencarian Tidak Ditemukan' : 'Belum ada Pesanan Masuk' }}
                            </p>
                        </div>
                    </td>
                </tr>
            </CustomTable>
        </div>

        <dialog class="modal" :class="{ 'modal-open': showBayarSebagianModal }">
            <div class="modal-box">
                <h3 class="text-lg font-black">Pembayaran Sebagian</h3>

                <div v-if="selectedPesan" class="space-y-4">
                    <div>
                        <CustomInput
                            label="Total Tagihan"
                            :model-value="formatRupiah(selectedPesan.total_tagihan)"
                            disabled
                        />
                    </div>
                    <div>
                        <CustomInputNumber
                            v-model="formPembayaran.nominal_bayar"
                            label="Nominal Bayar"
                            type="number"
                            placeholder="Masukkan nominal pembayaran"
                            :error="formPembayaran.errors.nominal_bayar"
                        />
                    </div>
                    <div class="p-3 text-xs rounded-lg bg-base-200">
                        <div>
                            Sudah Dibayar: <b>{{formatRupiah(selectedPesan.total_dibayar ?? 0)}}</b>
                        </div>
                        <div>
                            Sisa Tagihan: <b>{{formatRupiah(selectedPesan.total_tagihan - (selectedPesan.total_dibayar ?? 0))}}</b>
                        </div>
                    </div>
                </div>

                <div class="justify-end mt-6 modal-action">
                    <CustomButton variant="secondary" @click="resetModal">Batal</CustomButton>
                    <CustomButton variant="primary" :disabled="!formPembayaran.nominal_bayar || formPembayaran.processing" @click="submitBayarSebagian">Simpan Pembayaran</CustomButton>
                </div>
            </div>
        </dialog>
    </StafLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
