<script setup>
const props = defineProps({
    modelValue: Array,
    headers: Array,
    label: String,
    // Prop untuk kontrol tambah data
    canAdd: {
        type: Boolean,
        default: true
    },
    // Prop untuk kontrol hapus data
    canDelete: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['update:modelValue', 'add', 'remove']);

const removeRow = (index) => {
    const newValue = [...props.modelValue];
    newValue.splice(index, 1);
    emit('update:modelValue', newValue);
};
</script>

<template>
    <div class="space-y-3">
        <div v-if="label" class="flex items-center justify-between ml-1">
            <h2 class="text-xs font-black text-base-content/50 uppercase tracking-[0.2em]">{{ label }}</h2>
            <button
                v-if="canAdd"
                @click="$emit('add')"
                type="button"
                class="font-bold btn btn-ghost btn-xs text-primary hover:bg-primary/10"
            >
                + Tambah Baris
            </button>
        </div>

        <div class="overflow-visible border border-base-300 rounded-3xl bg-base-200/30">
            <table class="w-full text-left border-collapse rounded-3xl">
                <thead class="bg-base-200/50">
                    <tr>
                        <th v-for="(header, index) in headers" :key="header"
                            class="px-4 py-3 text-[10px] font-black text-base-content/40 uppercase tracking-widest"
                            :class="{
                                'first:rounded-tl-3xl': true,
                                // Jika tidak bisa hapus, header terakhir dapet rounded kanan
                                'last:rounded-tr-3xl': !canDelete && index === headers.length - 1
                            }"
                        >
                            {{ header }}
                        </th>
                        <th v-if="canDelete" class="w-16 px-4 py-3 text-[10px] font-black text-base-content/40 uppercase tracking-widest last:rounded-tr-3xl text-center">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-300">
                    <tr v-for="(row, index) in modelValue" :key="index" class="transition-colors group hover:bg-base-100/50">
                        <slot name="row" :row="row" :index="index"></slot>

                        <td v-if="canDelete" class="w-16 px-4 py-2 text-center border-l border-base-300/30">
                            <button
                                @click="removeRow(index)"
                                type="button"
                                class="p-2 transition-all text-error/40 hover:text-error hover:bg-error/10 rounded-xl"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="modelValue.length === 0">
                        <td :colspan="canDelete ? headers.length + 1 : headers.length" class="px-4 py-8 text-xs italic text-center text-base-content/30">
                            Belum ada data.
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="canAdd" class="p-2 border-t border-base-300 bg-base-200/50 rounded-b-3xl">
                <button
                    @click="$emit('add')"
                    type="button"
                    class="w-full py-2 text-[10px] font-black text-primary/70 uppercase hover:text-primary transition-all tracking-widest"
                >
                    + Masukkan Data Baru
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
:deep(td input),
:deep(td select),
:deep(td textarea) {
    border: none !important;
    box-shadow: none !important;
    outline: none !important;
    background-color: transparent !important;
    padding-left: 0 !important;
    width: 100%;
}

:deep(td input:focus),
:deep(td select:focus),
:deep(td textarea:focus) {
    --tw-ring-offset-width: 0px !important;
    --tw-ring-width: 0px !important;
    outline: none !important;
}
</style>
