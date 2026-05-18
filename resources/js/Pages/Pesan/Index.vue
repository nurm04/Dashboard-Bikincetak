<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomTable from '@/Components/CustomTable.vue';
import { alertStore } from '@/Utils/alertStore';
import CustomButton from '@/Components/CustomButton.vue';

const props = defineProps({
    pesanan: Array,
});

const headers = ['ID Pesanan', 'Customer', 'Total Tagihan', 'Pembayaran', 'Operasional', 'Aksi'];

const formOperasional = useForm({ status_operasional: '' });
const formPembayaran = useForm({ status_pembayaran: '' });

const updateOperasional = (id_pesan, value) => {
    formOperasional.status_operasional = value;
    formOperasional.put(route('pesan.updateOperasional', id_pesan), {
        preserveScroll: true,
        onSuccess: () => alertStore.show('Status Operasional berhasil diperbarui!', 'success'),
        onError: () => alertStore.show('Gagal memperbarui status operasional!', 'error')
    });
};

const updatePembayaran = (id_pesan, value) => {
    formPembayaran.status_pembayaran = value;
    formPembayaran.put(route('pesan.updatePembayaran', id_pesan), {
        preserveScroll: true,
        onSuccess: () => alertStore.show('Status Pembayaran berhasil diperbarui!', 'success'),
        onError: () => alertStore.show('Gagal memperbarui status pembayaran!', 'error')
    });
};

const hitungTotalTagihan = (pesan) => {
    const items = pesan.pesanan_item || [];

    return items.reduce((total, item) => {
        const costBarang = parseFloat(item.harga_satuan_snapshot || 0) * parseInt(item.jumlah || 0);
        const costSlaFlat = parseFloat(item.harga_pengerjaan_snapshot || 0);

        return total + costBarang + costSlaFlat;
    }, 0);
};

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka);
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

        <div class="px-4 py-6 mx-auto min-h-screen sm:px-6 lg:px-8 max-w-7xl">
            <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between">
                <Link :href="route('pos.katalog')" class="btn btn-primary rounded-xl font-black shadow-md tracking-wider">
                    + Tambah Pesanan (POS)
                </Link>
            </div>

            <CustomTable :headers="headers">
                <tr
                    v-for="p in pesanan"
                    :key="p.id_pesan"
                    :class="p.status_operasional === 'batal' ? 'opacity-40 bg-base-200/50 line-through' : 'hover:bg-base-200/50'"
                    class="transition-colors"
                >

                    <td class="px-6 py-4 font-mono text-xs font-bold text-primary">{{ p.id_pesan }}</td>

                    <td class="px-6 py-4">
                        <div class="font-bold text-base-content">{{ p.customer?.user?.name || 'Walk-in / Umum' }}</div>
                        <div class="text-[10px] opacity-40 font-mono tracking-wider">{{ p.id_customer }}</div>
                    </td>

                    <td class="px-6 py-4 font-black text-sm text-base-content">
                        {{ formatRupiah(hitungTotalTagihan(p)) }}
                    </td>

                    <td class="px-6 py-4">
                        <select
                            class="select select-bordered select-sm text-[10px] font-black uppercase tracking-wider rounded-xl shadow-sm"
                            :class="p.status_pembayaran === 'lunas' ? 'text-success border-success/30 bg-success/5' : p.status_pembayaran === 'dibayar_sebagian'  ? 'text-warning border-warning/30 bg-warning/5' : 'text-error border-error/30 bg-error/5'"
                            :value="p.status_pembayaran"
                            @change="updatePembayaran(p.id_pesan, $event.target.value)"
                            :disabled="formPembayaran.processing"
                        >
                            <option value="belum_lunas">BELUM LUNAS</option>
                            <option value="dibayar_sebagian">BAYAR SEBAGIAN</option>
                            <option value="lunas">LUNAS</option>
                        </select>
                    </td>

                    <td class="px-6 py-4">
                        <select
                            class="select select-bordered select-sm text-[10px] font-black uppercase tracking-wider text-base-content rounded-xl shadow-sm bg-base-100 border-base-300"
                            :value="p.status_operasional"
                            @change="updateOperasional(p.id_pesan, $event.target.value)"
                            :disabled="formOperasional.processing"
                        >
                            <option value="keranjang">Keranjang</option>
                            <option value="menunggu_diproses">Menunggu Diproses</option>
                            <option value="proses_pengerjaan">Proses Pengerjaan</option>
                            <option value="proses_pengantaran">Proses Pengantaran</option>
                            <option value="selesai">Selesai</option>
                            <option value="batal">Batal</option>
                        </select>
                    </td>

                    <td class="px-6 py-4 text-center">
                        <CustomButton type="link" :href="route('pesan.detail', p.id_pesan)" variant="info" size="sm">
                            Detail
                        </CustomButton>
                    </td>
                </tr>

                <tr v-if="pesanan.length === 0">
                    <td colspan="7" class="px-6 py-20 text-center">
                        <div class="flex flex-col items-center opacity-30 gap-2">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <p class="text-xs font-black uppercase tracking-widest">Belum ada Pesanan Masuk</p>
                        </div>
                    </td>
                </tr>
            </CustomTable>
        </div>
    </StafLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
