<script setup>
import { ref } from 'vue';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomTable from '@/Components/CustomTable.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { alertStore } from '@/Utils/alertStore';
import CustomAlertConfirm from '@/Components/CustomAlertConfirm.vue';

const props = defineProps({
    vouchers: Array,
});

const headers = ['Kode Voucher', 'Info Promo', 'Diskon & Syarat', 'Masa Berlaku', 'Status', 'Aksi'];

const isDeleteModalOpen = ref(false);
const selectedId = ref(null);
const form = useForm({});

const openDeleteModal = (id) => {
    selectedId.value = id;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    selectedId.value = null;
    form.reset();
};

const doDelete = () => {
    if (!selectedId.value) return;

    form.delete(route('voucher.destroy', selectedId.value), {
        onSuccess: () => {
            closeDeleteModal();
            alertStore.show('Voucher berhasil dihapus!', 'success');
        },
        onError: () => alertStore.show('Ada kesalahan saat menghapus voucher!', 'error')
    });
};

const formatRupiah = (angka) => {
    if(!angka) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka);
};

const formatDate = (dateString) => {
    if(!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(date);
};
</script>

<template>
    <CustomAlertConfirm
        :show="isDeleteModalOpen"
        type="error"
        title="Hapus Voucher"
        message="Apakah Anda yakin ingin menghapus Voucher ini? Data yang dihapus tidak dapat dikembalikan."
        confirmText="Ya, Hapus"
        @close="closeDeleteModal"
        @confirm="doDelete"
    />
    <Head title="Manajemen Voucher" />
    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Database Voucher & Promo
            </h2>
        </template>

        <div class="min-h-screen px-4 py-3 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">

                <div class="flex flex-col gap-4 mb-8 md:flex-row md:items-center md:justify-between">
                    <CustomButton v-if="$can('voucher', 'tambah')" type="link" :href="route('voucher.create')" variant="primary" class="px-6 py-2 rounded-xl">
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        </template>
                        Tambah Voucher
                    </CustomButton>
                </div>

                <CustomTable :headers="headers">
                    <tr v-for="item in vouchers" :key="item.id_voucher">
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm font-black tracking-widest text-primary">{{ item.kode_voucher }}</span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-bold text-base-content">{{ item.nama_promo }}</div>
                            <div class="text-[10px] uppercase font-bold tracking-wider mt-1" :class="item.tipe_target === 'semua_pesanan' ? 'text-success' : 'text-warning'">
                                Target: {{ item.tipe_target === 'semua_pesanan' ? 'Semua Pesanan' : 'Produk Spesifik' }}
                            </div>
                            <div v-if="item.tipe_target === 'produk_tertentu'" class="text-[10px] opacity-50">
                                SKU: {{ item.produk_sku?.nama_sku || item.id_sku_target }}
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="text-lg font-black text-error">{{ item.persentase_diskon }}%</div>
                            <div class="text-[10px] opacity-70 mt-1">
                                Maks: <span class="font-bold">{{ item.maksimal_potongan_rupiah ? formatRupiah(item.maksimal_potongan_rupiah) : 'Tanpa Batas' }}</span>
                            </div>
                            <div class="text-[10px] opacity-70">
                                Min Order: <span class="font-bold">{{ formatRupiah(item.minimal_transaksi_rupiah) }}</span>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="text-xs font-bold">{{ formatDate(item.berlaku_dari) }}</div>
                            <div class="text-xs opacity-50">s/d</div>
                            <div class="text-xs font-bold">{{ formatDate(item.berlaku_sampai) }}</div>
                            <div v-if="item.kuota_penggunaan" class="text-[10px] font-bold text-info mt-1 tracking-wider uppercase">
                                Sisa Kuota: {{ item.kuota_penggunaan }}
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-tight border"
                                :class="item.is_active ? 'bg-success text-base-content/70 border-base-300' : 'bg-error/10 text-error border-error/30'">
                                {{ item.is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <CustomButton v-if="$can('voucher', 'ubah')" type="link" :href="route('voucher.edit', item.id_voucher)" variant="info" size="sm">
                                    Edit
                                </CustomButton>
                                <CustomButton v-if="$can('voucher', 'hapus')" @click="openDeleteModal(item.id_voucher)" variant="error" size="sm">
                                    Hapus
                                </CustomButton>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="vouchers.length === 0">
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-sm font-bold tracking-widest uppercase">Belum ada Master Voucher</p>
                            </div>
                        </td>
                    </tr>
                </CustomTable>
            </div>
        </div>
    </StafLayout>
</template>
