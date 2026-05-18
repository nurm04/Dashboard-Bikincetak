<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomButton from '@/Components/CustomButton.vue';
import CustomSelect from '@/Components/CustomSelect.vue';
import ProdukRow from '@/Components/ProdukRow.vue';
import { alertStore } from '@/Utils/alertStore';

const props = defineProps({
    kategoris: Array,
    produks: Array,
    customers: Array,
});

const filterKategori = ref('semua');
const cartItems = ref([]);

const pembayaranOptions = [
    { id: 'belum_lunas', nama: 'Belum Lunas' },
    { id: 'lunas', nama: 'Lunas (Bayar Sekarang)' },
];

const form = useForm({
    id_customer: '',
    id_alamat: '',
    status_pembayaran: 'belum_lunas',
    items: [],
});
const customerOptions = computed(() => {
    return props.customers.map(c => ({
        id_customer: c.id_customer,
        nama_tampilan: `${c.user?.name || 'No Name'} (${c.id_customer})`
    }));
});
const alamatOptions = computed(() => {
    if (!form.id_customer) return [];
    const selectedCust = props.customers.find(c => c.id_customer === form.id_customer);

    if (!selectedCust || !selectedCust.alamat) return [];

    return selectedCust.alamat.map(a => ({
        id_alamat: a.id_alamat,
        alamat_lengkap: a.alamat
    }));
});

watch(() => form.id_customer, (newCustomerId) => {
    form.id_alamat = '';

    if (newCustomerId) {

        const listAlamat = alamatOptions.value;
        if (listAlamat.length > 0) {

            form.id_alamat = listAlamat[0].id_alamat;
        } else {
            alertStore.show('Customer ini belum mendaftarkan alamat pengiriman!', 'info');
        }
    }
});

const loadCart = () => {
    cartItems.value = JSON.parse(localStorage.getItem('pos_cart')) || [];
};

onMounted(() => {
    loadCart();
});

const hapusItem = (index) => {
    cartItems.value.splice(index, 1);
    localStorage.setItem('pos_cart', JSON.stringify(cartItems.value));
    alertStore.show('Item berhasil dihapus dari keranjang', 'success');
};

const kosongkanKeranjang = () => {
    if (confirm('Yakin ingin membersihkan semua isi keranjang belanja?')) {
        cartItems.value = [];
        localStorage.removeItem('pos_cart');
        alertStore.show('Keranjang belanja dibersihkan', 'success');
    }
};

const grandTotal = computed(() => {
    return cartItems.value.reduce((total, item) => {
        const subtotalBarang = item.jumlah * item.harga_satuan_snapshot;
        const flatFeeSla = item.harga_pengerjaan_snapshot || 0;
        return total + subtotalBarang + flatFeeSla;
    }, 0);
});

const groupedProduks = computed(() => {
    const groups = {};
    props.produks.forEach(prod => {
        const idKat = prod.id_kategori;
        const kat = props.kategoris.find(k => k.id_kategori === idKat);
        const namaKat = kat ? kat.nama_kategori : 'Lainnya';

        if (!groups[idKat]) {
            groups[idKat] = {
                id_kategori: idKat,
                nama_kategori: namaKat,
                data: []
            };
        }
        groups[idKat].data.push(prod);
    });
    return Object.values(groups);
});

const filteredGroupedProduks = computed(() => {
    if (filterKategori.value === 'semua') return groupedProduks.value;
    return groupedProduks.value.filter(g => g.id_kategori === filterKategori.value);
});

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka);
};

const submitCheckout = () => {
    if (cartItems.value.length === 0) {
        alertStore.show('Keranjang kasir masih kosong!', 'error');
        return;
    }

    if (!form.id_customer) {
        alertStore.show('Pilih pelanggan terlebih dahulu bray!', 'error');
        return;
    }

    if (!form.id_alamat) {
        alertStore.show('Pilih alamat pengiriman nota terlebih dahulu!', 'error');
        return;
    }

    // 1. Rakit data pakai Native FormData bawaan JavaScript bray
    const formData = new FormData();
    formData.append('id_customer', form.id_customer);
    formData.append('id_alamat', form.id_alamat);
    formData.append('status_pembayaran', form.status_pembayaran);

    // 2. Map dan masukkan items keranjang ke FormData satu per satu
    cartItems.value.forEach((item, index) => {
        formData.append(`items[${index}][id_sku]`, item.id_sku);
        formData.append(`items[${index}][jumlah]`, item.jumlah);
        formData.append(`items[${index}][nama_produk_snapshot]`, item.nama_produk_snapshot);
        formData.append(`items[${index}][harga_satuan_snapshot]`, item.harga_satuan_snapshot);
        formData.append(`items[${index}][estimasi_pengerjaan]`, item.estimasi_pengerjaan || 'Reguler');
        formData.append(`items[${index}][harga_pengerjaan_snapshot]`, item.harga_pengerjaan_snapshot || 0);
        formData.append(`items[${index}][catatan]`, item.catatan || '');

        // Append file desain fisik jika ada bray
        if (item.file_desain) {
            formData.append(`items[${index}][file_desain]`, item.file_desain);
        }

        // Stringify nested array finishing biar gak hancur pas dibaca PHP
        const finishingData = Array.isArray(item.finishings) ? item.finishings : [];
        formData.append(`items[${index}][finishings]`, JSON.stringify(finishingData));
    });

    // 3. Kirim murni pakai router.post bawaan Inertia (Bypass useForm)
    router.post(route('pesan.store'), formData, {
        forceFormData: true,
        onSuccess: () => {
            alertStore.show('Transaksi Berhasil Disimpan!', 'success');
            localStorage.removeItem('pos_cart'); // Bersihkan local storage keranjang
            cartItems.value = [];
            form.reset();
        },
        onError: (errors) => {
            console.error('Validation Errors Backend:', errors);
            alertStore.show('Gagal memproses checkout, periksa log error backend!', 'error');
        }
    });
};
</script>

<template>
    <Head title="POS Kasir Utama" />

    <StafLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-base-content">
                Sistem Kasir (Point of Sale)
            </h2>
        </template>

        <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

            <div class="p-6 border rounded-3xl shadow-sm bg-base-100 border-base-300">
                <h3 class="text-xs font-black uppercase tracking-widest opacity-50 mb-4 border-b pb-2">Informasi Nota Transaksi</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="text-[10px] font-bold uppercase opacity-70 ml-1 block mb-1">Pelanggan / Customer</label>
                        <CustomSelect
                            v-model="form.id_customer"
                            :options="customerOptions"
                            labelKey="nama_tampilan"
                            valueKey="id_customer"
                            placeholder="Pilih Pelanggan..."
                            required
                        />
                    </div>

                    <div>
                        <label class="text-[10px] font-bold uppercase opacity-70 ml-1 block mb-1">Alamat Pengiriman / Nota</label>
                        <CustomSelect
                            v-model="form.id_alamat"
                            :options="alamatOptions"
                            labelKey="alamat_lengkap"
                            valueKey="id_alamat"
                            :placeholder="form.id_customer ? 'Pilih Alamat...' : 'Pilih customer terlebih dahulu'"
                            :disabled="!form.id_customer || alamatOptions.length === 0"
                            required
                        />
                    </div>

                    <div>
                        <label class="text-[10px] font-bold uppercase opacity-70 ml-1 block mb-1">Status Pembayaran</label>
                        <CustomSelect v-model="form.status_pembayaran" :options="pembayaranOptions" labelKey="nama" valueKey="id" required />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <div class="lg:col-span-7 p-6 border rounded-3xl shadow-sm bg-base-100 border-base-300 space-y-6">
                    <div class="flex gap-2 overflow-x-auto pb-3 border-b border-base-200 scrollbar-hide">
                        <button
                            @click="filterKategori = 'semua'"
                            class="btn btn-sm rounded-xl font-bold px-6 border-none"
                            :class="filterKategori === 'semua' ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content hover:bg-base-300'"
                        >
                            Semua Kategori
                        </button>
                        <button
                            v-for="kat in kategoris" :key="kat.id_kategori"
                            @click="filterKategori = kat.id_kategori"
                            class="btn btn-sm rounded-xl font-bold px-6 border-none"
                            :class="filterKategori === kat.id_kategori ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content hover:bg-base-300'"
                        >
                            {{ kat.nama_kategori }}
                        </button>
                    </div>

                    <div class="max-h-125 overflow-y-auto pr-2 scrollbar-hide">
                        <ProdukRow
                            v-for="group in filteredGroupedProduks"
                            :key="group.id_kategori"
                            :title="group.nama_kategori"
                            :data="group.data"
                        />
                        <div v-if="filteredGroupedProduks.length === 0" class="py-20 text-center opacity-40 font-bold uppercase text-xs">
                            Tidak ada produk di kategori ini.
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 border rounded-3xl shadow-sm bg-base-100 border-base-300 overflow-hidden flex flex-col max-h-147.5">
                    <div class="p-5 border-b border-base-200 bg-base-200/40 flex justify-between items-center">
                        <div>
                            <h3 class="font-black uppercase tracking-widest text-sm text-base-content">Struk Keranjang</h3>
                            <span class="text-[10px] font-bold opacity-50 uppercase tracking-wider">Total Item: {{ cartItems.length }}</span>
                        </div>
                        <button
                            v-if="cartItems.length > 0"
                            @click="kosongkanKeranjang"
                            type="button"
                            class="text-[10px] font-black uppercase text-error tracking-widest hover:underline"
                        >
                            ✕ Kosongkan
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-base-200/20 min-h-62.5">
                        <div
                            v-for="(item, idx) in cartItems"
                            :key="idx"
                            class="p-4 bg-base-100 rounded-2xl border border-base-300 shadow-sm relative flex flex-col gap-1 group"
                        >
                            <div class="absolute top-3 right-3 flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a
                                    v-if="item.id_produk"
                                    :href="route('pos.detail', { id_produk: item.id_produk }) + '?edit_item_id=' + item.cart_item_id"
                                    class="btn btn-xs btn-circle btn-warning text-base-100"
                                    title="Edit Spesifikasi Item"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </a>
                                <button
                                    @click="hapusItem(idx)"
                                    type="button"
                                    class="btn btn-xs btn-circle btn-error text-white"
                                    title="Hapus Item"
                                >
                                    ✕
                                </button>
                            </div>

                            <span class="text-[9px] font-black uppercase tracking-wider text-warning">
                                ⏳ {{ item.estimasi_pengerjaan }}
                                <span v-if="item.harga_pengerjaan_snapshot > 0">(+{{ formatRupiah(item.harga_pengerjaan_snapshot) }} Flat)</span>
                            </span>

                            <h4 class="font-black text-sm text-base-content pr-16 truncate">{{ item.nama_produk_snapshot }}</h4>

                            <div v-if="item.finishings?.length" class="text-[10px] opacity-60 font-bold pl-2 border-l-2 border-base-300 mt-1">
                                <p v-for="f in item.finishings" :key="f.id_sku_finishing">
                                    • {{ f.nama_finishing_snapshot }}
                                </p>
                            </div>

                            <div class="flex justify-between items-center mt-3 pt-2 border-t border-base-200">
                                <span class="text-xs font-bold opacity-60">{{ item.jumlah }} pcs × {{ formatRupiah(item.harga_satuan_snapshot) }}</span>
                                <span class="font-black text-sm text-base-content">
                                    {{ formatRupiah((item.jumlah * item.harga_satuan_snapshot) + (item.harga_pengerjaan_snapshot || 0)) }}
                                </span>
                            </div>
                        </div>

                        <div v-if="cartItems.length === 0" class="flex flex-col items-center justify-center py-20 opacity-30 gap-2">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <p class="text-xs font-black uppercase tracking-widest">Belum Ada Item Terpilih</p>
                        </div>
                    </div>

                    <div class="p-5 border-t border-base-200 bg-base-100 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-black uppercase tracking-widest opacity-60">Grand Total Nota</span>
                            <span class="text-2xl font-black text-primary">{{ formatRupiah(grandTotal) }}</span>
                        </div>

                        <CustomButton
                            @click="submitCheckout"
                            :disabled="form.processing || cartItems.length === 0"
                            variant="primary"
                            class="w-full py-4 rounded-2xl font-black text-center tracking-widest"
                        >
                            {{ form.processing ? 'MEMPROSES...' : 'SIMPAN & CETAK NOTA' }}
                        </CustomButton>
                    </div>
                </div>

            </div>
        </div>
    </StafLayout>
</template>
