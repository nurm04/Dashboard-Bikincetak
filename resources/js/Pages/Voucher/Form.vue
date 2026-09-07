<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import CustomSelectSearch from '@/Components/Form/CustomSelectSearch.vue';
import CustomRadioButton from '@/Components/Form/CustomRadioButton.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    voucher: Object,
    skus: Array,
    produks: Array,
    roles: Array,
});

const isEdit = !!props.voucher;

// Mapping Opsi Dropdown
const targetOptions = [
    { label: 'Berlaku untuk Semua Pesanan (Global)', value: 'semua_pesanan' },
    { label: 'Hanya 1 Produk Global (Semua Variannya)', value: 'produk_tertentu' },
    { label: 'Hanya 1 SKU Spesifik / Varian Khusus', value: 'sku_tertentu' },
];

const statusOptions = [
    { label: 'Aktif (Bisa Digunakan)', value: 1 },
    { label: 'Nonaktif (Disembunyikan)', value: 0 }
];

// Map Dropdown Options
const skuOptions = computed(() => props.skus?.map(sku => ({ label: `${sku.nama_sku} (${sku.id_sku})`, value: sku.id_sku })) || []);
const produkOptions = computed(() => props.produks?.map(prd => ({ label: `${prd.nama_produk} (${prd.id_produk})`, value: prd.id_produk })) || []);

// Helper untuk format ISO date (YYYY-MM-DDThh:mm) khusus input datetime-local
const formatForInput = (dateString) => {
    if (!dateString) return '';
    const d = new Date(dateString);
    return new Date(d.getTime() - (d.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
};

const form = useForm({
    kode_voucher: props.voucher?.kode_voucher ?? '',
    nama_promo: props.voucher?.nama_promo ?? '',
    tipe_target: props.voucher?.tipe_target ?? 'semua_pesanan',
    id_produk_target: props.voucher?.id_produk_target ?? '',
    id_sku_target: props.voucher?.id_sku_target ?? '',

    // Inisialisasi format array dari JSON database
    role_customer_targets: Array.isArray(props.voucher?.role_customer_targets)
        ? props.voucher.role_customer_targets.map(String)
        : [],

    persentase_diskon: props.voucher?.persentase_diskon ?? '',
    maksimal_potongan_rupiah: props.voucher?.maksimal_potongan_rupiah ?? '',
    minimal_transaksi_rupiah: props.voucher?.minimal_transaksi_rupiah ?? 0,
    kuota_penggunaan: props.voucher?.kuota_penggunaan ?? '',
    berlaku_dari: formatForInput(props.voucher?.berlaku_dari),
    berlaku_sampai: formatForInput(props.voucher?.berlaku_sampai),
    is_active: props.voucher?.is_active ?? 1,
});

// Fungsi Toggling Checkbox Role Customer
const toggleRole = (roleId) => {
    const stringId = String(roleId);
    const idx = form.role_customer_targets.indexOf(stringId);
    if (idx === -1) {
        form.role_customer_targets.push(stringId);
    } else {
        form.role_customer_targets.splice(idx, 1);
    }
};

const submit = () => {
    if (isEdit) {
        form.put(route('voucher.update', props.voucher.id_voucher));
    } else {
        form.post(route('voucher.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Voucher' : 'Tambah Voucher Baru'" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('voucher.index')" class="btn btn-sm btn-circle btn-ghost ring-1 ring-base-300">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-base-content">
                        {{ isEdit ? 'Edit Voucher: ' + voucher.kode_voucher : 'Buat Voucher Promo' }}
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 border rounded-lg shadow-xl bg-base-100 border-base-300">
                    <form @submit.prevent="submit" class="space-y-6">

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <CustomInput
                                v-model="form.kode_voucher"
                                label="Kode Voucher (Tanpa Spasi)"
                                placeholder="Contoh: MERDEKA45"
                                :error="form.errors.kode_voucher"
                                class="uppercase"
                                required
                            />
                            <CustomInput
                                v-model="form.nama_promo"
                                label="Nama Promo (Internal)"
                                placeholder="Contoh: Promo 17 Agustus Spanduk"
                                :error="form.errors.nama_promo"
                                required
                            />
                        </div>

                        <!-- TARGET PRODUK/SKU -->
                        <div class="p-6 border rounded-xl border-base-300 bg-base-200/30">
                            <h3 class="mb-4 text-sm font-black tracking-widest uppercase opacity-50">Kategori Target Barang</h3>

                            <CustomSelect
                                v-model="form.tipe_target"
                                label="Pilih Tipe Target Diskon"
                                :options="targetOptions"
                                labelKey="label"
                                valueKey="value"
                                :error="form.errors.tipe_target"
                                class="mb-4"
                            />

                            <div v-if="form.tipe_target === 'produk_tertentu'" class="p-4 border-l-4 rounded-lg bg-base-100 border-info">
                                <CustomSelectSearch
                                    v-model="form.id_produk_target"
                                    label="Pilih Produk Utama"
                                    placeholder="Ketik nama produk..."
                                    :options="produkOptions"
                                    labelKey="label"
                                    valueKey="value"
                                    :error="form.errors.id_produk_target"
                                />
                                <p class="mt-2 text-[10px] font-bold text-info tracking-wider">Diskon berlaku untuk semua varian SKU di dalam produk ini.</p>
                            </div>

                            <div v-if="form.tipe_target === 'sku_tertentu'" class="p-4 border-l-4 rounded-lg bg-base-100 border-warning">
                                <CustomSelectSearch
                                    v-model="form.id_sku_target"
                                    label="Pilih Varian (SKU) Spesifik"
                                    placeholder="Ketik nama atau ID varian..."
                                    :options="skuOptions"
                                    labelKey="label"
                                    valueKey="value"
                                    :error="form.errors.id_sku_target"
                                />
                                <p class="mt-2 text-[10px] font-bold text-warning tracking-wider">Diskon ini sangat spesifik hanya memotong subtotal SKU terpilih.</p>
                            </div>
                        </div>

                        <!-- BATAS ROLE CUSTOMER -->
                        <div class="p-6 border rounded-xl border-base-300 bg-base-200/30">
                            <h3 class="mb-1 text-sm font-black tracking-widest uppercase opacity-50">Filter Role Customer</h3>
                            <p class="mb-4 text-xs font-medium opacity-60">Pilih level member yang berhak mengklaim voucher ini. Kosongkan (Jangan centang satupun) jika voucher bersifat publik / untuk siapa saja.</p>

                            <div class="flex flex-wrap gap-3">
                                <label
                                    v-for="r in roles"
                                    :key="r.id_role_customer"
                                    class="flex items-center gap-2 px-4 py-2 transition-colors border cursor-pointer rounded-xl"
                                    :class="form.role_customer_targets.includes(String(r.id_role_customer)) ? 'bg-primary text-primary-content border-primary shadow-sm' : 'bg-base-100 hover:border-primary border-base-300'"
                                >
                                    <input
                                        type="checkbox"
                                        class="checkbox checkbox-sm"
                                        :class="form.role_customer_targets.includes(String(r.id_role_customer)) ? 'checkbox-primary border-primary-content' : ''"
                                        :checked="form.role_customer_targets.includes(String(r.id_role_customer))"
                                        @change="toggleRole(r.id_role_customer)"
                                    />
                                    <span class="text-xs font-bold tracking-wider uppercase">{{ r.role }}</span>
                                </label>
                            </div>
                            <p v-if="form.errors.role_customer_targets" class="mt-2 text-[10px] font-bold text-error uppercase">{{ form.errors.role_customer_targets }}</p>
                        </div>

                        <!-- ATURAN NOMINAL -->
                        <div class="p-6 border rounded-xl border-base-300 bg-base-200/30">
                            <h3 class="mb-4 text-sm font-black tracking-widest uppercase opacity-50">Besaran & Syarat Nominal</h3>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                <CustomInput
                                    v-model="form.persentase_diskon"
                                    label="Diskon (%)"
                                    type="number"
                                    step="0.01"
                                    placeholder="Cth: 15"
                                    :error="form.errors.persentase_diskon"
                                    required
                                />
                                <CustomInput
                                    v-model="form.maksimal_potongan_rupiah"
                                    label="Maks Potongan (Rp)"
                                    type="number"
                                    placeholder="Kosongkan jika bebas"
                                    :error="form.errors.maksimal_potongan_rupiah"
                                />
                                <CustomInput
                                    v-model="form.minimal_transaksi_rupiah"
                                    label="Min. Belanja (Rp)"
                                    type="number"
                                    placeholder="Cth: 100000"
                                    :error="form.errors.minimal_transaksi_rupiah"
                                    required
                                />
                            </div>
                        </div>

                        <!-- ATURAN KUOTA & WAKTU -->
                        <div class="p-6 border rounded-xl border-base-300 bg-base-200/30">
                            <h3 class="mb-4 text-sm font-black tracking-widest uppercase opacity-50">Masa Berlaku & Kuota</h3>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                <CustomInput
                                    v-model="form.berlaku_dari"
                                    label="Mulai Berlaku"
                                    type="datetime-local"
                                    :error="form.errors.berlaku_dari"
                                    required
                                />
                                <CustomInput
                                    v-model="form.berlaku_sampai"
                                    label="Sampai Dengan"
                                    type="datetime-local"
                                    :error="form.errors.berlaku_sampai"
                                    required
                                />
                                <CustomInput
                                    v-model="form.kuota_penggunaan"
                                    label="Kuota Klaim"
                                    type="number"
                                    placeholder="Batas klaim global"
                                    :error="form.errors.kuota_penggunaan"
                                />
                            </div>
                        </div>

                        <CustomRadioButton
                            v-model="form.is_active"
                            label="Status Voucher"
                            name="is_active"
                            :options="statusOptions"
                            :error="form.errors.is_active"
                        />

                        <div class="flex flex-col items-center gap-4 pt-6 mt-8 border-t sm:flex-row border-base-300">
                            <CustomButton
                                type="submit"
                                variant="primary"
                                class="flex-1 w-full py-4 sm:w-auto rounded-2xl"
                                :disabled="form.processing"
                            >
                                <template #icon v-if="!form.processing">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </template>
                                {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Buat Voucher') }}
                            </CustomButton>

                            <CustomButton
                                type="link"
                                :href="route('voucher.index')"
                                variant="secondary"
                                class="w-full py-4 sm:w-auto rounded-2xl"
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
