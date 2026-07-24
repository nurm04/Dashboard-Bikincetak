<script setup>
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import CustomSelect from '@/Components/Form/CustomSelect.vue';
import CustomSelectSearch from '@/Components/Form/CustomSelectSearch.vue';
import CustomRadioButton from '@/Components/Form/CustomRadioButton.vue';
import CustomButton from '@/Components/Form/CustomButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    voucher: Object,
    skus: Array,
});

const isEdit = !!props.voucher;

// Mapping Opsi Dropdown
const targetOptions = [
    { label: 'Berlaku untuk Semua Pesanan (Grand Total)', value: 'semua_pesanan' },
    { label: 'Berlaku Khusus 1 Produk Tertentu', value: 'produk_tertentu' },
];

const statusOptions = [
    { label: 'Aktif (Bisa Digunakan)', value: 1 },
    { label: 'Nonaktif (Disembunyikan)', value: 0 }
];

// Map SKU jadi format dropdown
const skuOptions = computed(() => {
    return props.skus?.map(sku => ({
        label: `${sku.nama_sku} (${sku.id_sku})`,
        value: sku.id_sku
    })) || [];
});

// Helper untuk format ISO date (YYYY-MM-DDThh:mm) khusus input datetime-local
const formatForInput = (dateString) => {
    if (!dateString) return '';
    const d = new Date(dateString);
    // Sesuaikan zona waktu lokal (WIB/dll)
    return new Date(d.getTime() - (d.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
};

const form = useForm({
    kode_voucher: props.voucher?.kode_voucher ?? '',
    nama_promo: props.voucher?.nama_promo ?? '',
    tipe_target: props.voucher?.tipe_target ?? 'semua_pesanan',
    id_sku_target: props.voucher?.id_sku_target ?? '',
    persentase_diskon: props.voucher?.persentase_diskon ?? '',
    maksimal_potongan_rupiah: props.voucher?.maksimal_potongan_rupiah ?? '',
    minimal_transaksi_rupiah: props.voucher?.minimal_transaksi_rupiah ?? 0,
    kuota_penggunaan: props.voucher?.kuota_penggunaan ?? '',
    berlaku_dari: formatForInput(props.voucher?.berlaku_dari),
    berlaku_sampai: formatForInput(props.voucher?.berlaku_sampai),
    is_active: props.voucher?.is_active ?? 1,
});

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
            <h2 class="text-xl font-bold leading-tight text-base-content">
                {{ isEdit ? 'Edit Voucher: ' + voucher.kode_voucher : 'Buat Voucher Promo' }}
            </h2>
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

                        <div class="p-6 border rounded-xl border-base-300 bg-base-200/30">
                            <h3 class="mb-4 text-sm font-black tracking-widest uppercase opacity-50">Target Diskon</h3>

                            <CustomSelect
                                v-model="form.tipe_target"
                                label="Tipe Target"
                                :options="targetOptions"
                                labelKey="label"
                                valueKey="value"
                                :error="form.errors.tipe_target"
                                class="mb-4"
                            />

                            <div v-if="form.tipe_target === 'produk_tertentu'" class="p-4 border-l-4 rounded-lg bg-base-100 border-warning">
                                <CustomSelectSearch
                                    v-model="form.id_sku_target"
                                    label="Pilih Produk Target"
                                    placeholder="Ketik nama atau ID produk..."
                                    :options="skuOptions"
                                    labelKey="label"
                                    valueKey="value"
                                    :error="form.errors.id_sku_target"
                                />
                                <p class="mt-2 text-xs font-bold text-warning">Diskon hanya akan memotong subtotal produk ini saja.</p>
                            </div>
                        </div>

                        <div class="p-6 border rounded-xl border-base-300 bg-base-200/30">
                            <h3 class="mb-4 text-sm font-black tracking-widest uppercase opacity-50">Besaran & Syarat</h3>

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
