<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import StafLayout from '@/Layouts/StafLayout.vue';
import CustomInput from '@/Components/Form/CustomInput.vue';
import { alertStore } from '@/Utils/alertStore';

import OrderInfoCards from './Partials/OrderInfoCards.vue';
import OrderItemsTable from './Partials/OrderItemsTable.vue';
import OrderSummary from './Partials/OrderSummary.vue';
import OrderFormCard from './Partials/OrderFormCard.vue';

const props = defineProps({
    pesanan: Object,
    total_tagihan: Number,
    kode_unik: Number,
    total_transfer: Number,
    total_dibayar: Number,
    sisa_tagihan: Number,
    enumPembayaran: Array,
    enumOperasional: Array,
});
const showOrderForm = ref(false);
const itemToEdit = ref(null);

const handleRequestEdit = (item) => {
    itemToEdit.value = { ...item };
    showOrderForm.value = true;
    window.scrollTo({ top: 300, behavior: 'smooth' });
};

const handleTambahItem = () => {
    itemToEdit.value = null;
    showOrderForm.value = true;
    window.scrollTo({ top: 300, behavior: 'smooth' });
};

const handleCancelForm = () => { itemToEdit.value = null; showOrderForm.value = false; };

const handleFormSubmit = (payload) => {
    if (itemToEdit.value) {
        const formUpdate = useForm({
            ...payload,
            id_pesan: props.pesanan.id_pesan,
            _method: 'PUT'
        });

        formUpdate.post(route('pesan.updateItem', itemToEdit.value.id), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                if (page.props.flash?.error) {
                    alertStore.show(page.props.flash.error, 'error');
                } else {
                    handleCancelForm();
                    alertStore.show('Item berhasil diperbarui!', 'success');
                }
            },
            onError: (e) => {
                console.error(e);
                alertStore.show('Gagal memperbarui item.', 'error');
            }
        });
    } else {
        const formAdd = useForm({
            ...payload,
            id_pesan: props.pesanan.id_pesan
        });

        formAdd.post(route('pesan.addItem', props.pesanan.id_pesan), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                if (page.props.flash?.error) {
                    alertStore.show(page.props.flash.error, 'error');
                } else {
                    handleCancelForm();
                    alertStore.show('Item baru berhasil ditambahkan!', 'success');
                }
            },
            onError: (e) => {
                console.error(e);
                alertStore.show('Gagal menambahkan item.', 'error');
            }
        });
    }
};

const handleDeleteItem = (id) => {
    useForm({}).delete(route('pesan.deleteItem', id), {
        preserveScroll: true,
        onSuccess: () => {
            alertStore.show('Item berhasil dihapus!', 'success');
        },
        onError: () => {
            alertStore.show('Gagal menghapus item!', 'error');
        }
    });
};

const modalResi = ref(null);
const formResi = useForm({
    nomor_resi: props.pesanan?.nomor_resi || '',
});

const openModalResi = () => {
    formResi.nomor_resi = props.pesanan?.nomor_resi || '';
    formResi.clearErrors();
    modalResi.value.showModal();
};

const closeModalResi = () => {
    modalResi.value.close();
};

const submitResi = () => {
    formResi.put(route('pesan.updateResi', props.pesanan.id_pesan), {
        preserveScroll: true,
        onSuccess: () => {
            closeModalResi();
            alertStore.show('Nomor Resi berhasil disimpan!', 'success');
        },
        onError: () => {
            alertStore.show('Gagal menyimpan Nomor Resi!', 'error');
        }
    });
};
const handlePrintLabel = (itemId) => {
    if (!itemId) {
        alertStore.show('Gagal mencetak, ID Item tidak ditemukan!', 'error');
        return;
    }
    window.open(route('pesan.cetakLabelItem', itemId), '_blank');
};
</script>

<template>
    <Head :title="`Detail Pesanan #${pesanan.id_pesan}`" />

    <StafLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-xl font-bold leading-tight text-base-content">
                    Detail Pesanan {{ pesanan.id_pesan }}
                </h2>
                <a :href="route('pesan.cetakLabel', pesanan.id_pesan)" target="_blank" class="btn btn-sm btn-outline shadow-sm font-black uppercase tracking-widest text-[10px]">
                    🖨️ Cetak Label
                </a>
            </div>
        </template>

        <div class="px-4 py-6 mx-auto max-w-350">

            <OrderInfoCards
                :pesanan="pesanan"
                :total_tagihan="total_tagihan"
                :total_dibayar="total_dibayar"
                :enumPembayaran="enumPembayaran"
                :enumOperasional="enumOperasional"
                @openResiModal="openModalResi"
            />

            <div class="grid items-start grid-cols-1 gap-6 lg:grid-cols-12">
                <div class="space-y-6 lg:col-span-8 xl:col-span-9">

                    <div v-show="showOrderForm" class="transition-all duration-300">
                        <OrderFormCard
                            v-if="showOrderForm"
                            :editData="itemToEdit"
                            @cancel="handleCancelForm"
                            @submit="handleFormSubmit"
                        />
                    </div>

                    <OrderItemsTable
                        :items="pesanan.pesanan_item"
                        :statusOperasional="pesanan.status_operasional"
                        @requestEdit="handleRequestEdit"
                        @deleteItem="handleDeleteItem"
                        @addItem="handleTambahItem"
                        @printLabel="handlePrintLabel"
                    />

                </div>

                <div class="lg:col-span-4 xl:col-span-3">
                    <div class="sticky top-24">
                        <OrderSummary
                            :total_tagihan="total_tagihan"
                            :harga_ongkir="pesanan.harga_ongkir"
                            :diskon_voucher_nominal="pesanan.diskon_voucher_nominal"
                            :kode_voucher="pesanan.kode_voucher"
                            :kode_unik="kode_unik"
                            :total_transfer="total_transfer"
                            :total_dibayar="total_dibayar"
                            :sisa_tagihan="sisa_tagihan"
                        />
                    </div>
                </div>
            </div>
        </div>

        <dialog ref="modalResi" class="modal modal-bottom sm:modal-middle">
            <div class="border shadow-xl modal-box bg-base-100 rounded-3xl border-base-200">
                <h3 class="text-lg font-black tracking-tight text-base-content">Update Nomor Resi</h3>
                <p class="py-2 text-xs font-medium opacity-60">Masukkan nomor resi yang valid dari pihak ekspedisi untuk dikirimkan ke pelanggan.</p>
                <div class="mt-4">
                    <CustomInput label="Nomor Resi" v-model="formResi.nomor_resi" placeholder="Contoh: JD0123456789" :error="formResi.errors.nomor_resi" :disabled="formResi.processing" />
                </div>
                <div class="mt-6 modal-action">
                    <button type="button" @click="closeModalResi" class="btn btn-sm btn-ghost rounded-xl font-bold tracking-wider text-[11px] uppercase">Batal</button>
                    <button @click="submitResi" class="btn btn-sm btn-primary rounded-xl font-black tracking-widest text-[11px] uppercase" :disabled="formResi.processing || !formResi.nomor_resi">
                        <span v-if="formResi.processing" class="loading loading-spinner loading-xs"></span>
                        Simpan Resi
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop"><button @click="closeModalResi">close</button></form>
        </dialog>
    </StafLayout>
</template>
