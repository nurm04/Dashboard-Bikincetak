<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({
            tipe_file: 'upload',
            file: null,
            link_file: ''
        })
    },
    error: String,
    disabled: {
        type: Boolean,
        default: false
    },
    label: {
        type: String,
        default: 'Metode File Desain'
    },
    showTipeFile: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['update:modelValue']);

const activeTab = ref(props.modelValue?.tipe_file || 'upload');
const selectedFile = ref(props.modelValue?.file || null);
const linkDrive = ref(props.modelValue?.link_file || '');
const internalError = ref(null);
const fileInputRef = ref(null);

const MAX_FILE_SIZE = 200 * 1024 * 1024;

watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        // Jika showTipeFile false, paksa activeTab selalu di 'upload'
        activeTab.value = props.showTipeFile ? (newVal.tipe_file || 'upload') : 'upload';
        selectedFile.value = newVal.file || null;
        linkDrive.value = newVal.link_file || '';
    }
}, { deep: true });

const emitUpdate = () => {
    emit('update:modelValue', {
        tipe_file: activeTab.value,
        file: activeTab.value === 'upload' ? selectedFile.value : null,
        link_file: activeTab.value === 'link' ? linkDrive.value : ''
    });
};

const setActiveTab = (tab) => {
    if (props.disabled || !props.showTipeFile) return;
    activeTab.value = tab;
    emitUpdate();
};

const handleFileChange = (e) => {
    if (props.disabled) return;

    const files = e.target.files;
    internalError.value = null;

    if (files && files.length > 0) {
        const allowedExtensions = ["pdf", "ai", "jpg", "jpeg", "png", "zip"];
        const file = files[0];
        const fileExtension = file.name.split(".").pop()?.toLowerCase();

        let hasError = false;

        if (!fileExtension || !allowedExtensions.includes(fileExtension)) {
            internalError.value = "FORMAT FILE TIDAK DIDUKUNG (PDF, AI, JPG, PNG, ZIP)";
            hasError = true;
        }

        if (!hasError && file.size > MAX_FILE_SIZE) {
            internalError.value = "UKURAN FILE TERLALU BESAR (MAKS 200MB)";
            hasError = true;
        }

        if (!hasError) {
            selectedFile.value = file;
            emitUpdate();
        }

        if (fileInputRef.value) fileInputRef.value.value = "";
    }
};

const removeFile = (e) => {
    if (props.disabled) return;
    if (e) e.stopPropagation();
    selectedFile.value = null;
    internalError.value = null;
    emitUpdate();
};

const updateLink = (e) => {
    linkDrive.value = e.target.value;
    emitUpdate();
};

const fileUrl = computed(() => {
    if (!selectedFile.value) return null;
    if (typeof selectedFile.value !== 'string') return null;
    return `/storage/${selectedFile.value}`;
});

const displayFileName = computed(() => {
    if (!selectedFile.value) return '';
    if (typeof selectedFile.value === 'string') {
        return selectedFile.value.split('/').pop();
    }
    return selectedFile.value.name;
});
</script>

<template>
    <div class="w-full max-w-full min-w-0 p-4 space-y-3 overflow-hidden transition-colors border bg-base-100 rounded-2xl" :class="[error ? 'border-error/50 bg-error/5' : 'border-base-content/10', disabled ? 'opacity-80' : '']">
        <div class="text-[10px] font-black uppercase opacity-40 mb-2 flex items-center gap-1">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
            {{ label }}
        </div>

        <!-- Render Tab Pilihan HANYA jika showTipeFile bernilai true -->
        <div v-if="showTipeFile" class="grid w-full grid-cols-3 p-1 rounded-lg join bg-base-200">
            <button type="button" @click="setActiveTab('upload')" :class="['join-item btn btn-xs border-none font-black text-[9px] uppercase', activeTab === 'upload' ? 'bg-base-100 shadow-sm text-primary' : 'bg-transparent text-base-content/50 hover:bg-base-300', disabled ? 'cursor-not-allowed' : '']">Upload</button>
            <button type="button" @click="setActiveTab('link')" :class="['join-item btn btn-xs border-none font-black text-[9px] uppercase', activeTab === 'link' ? 'bg-base-100 shadow-sm text-primary' : 'bg-transparent text-base-content/50 hover:bg-base-300', disabled ? 'cursor-not-allowed' : '']">Link</button>
            <button type="button" @click="setActiveTab('email')" :class="['join-item btn btn-xs border-none font-black text-[9px] uppercase', activeTab === 'email' ? 'bg-base-100 shadow-sm text-primary' : 'bg-transparent text-base-content/50 hover:bg-base-300', disabled ? 'cursor-not-allowed' : '']">Email</button>
        </div>

        <!-- Margin atas disesuaikan dengan muncul atau tidaknya Tab Pilihan -->
        <div v-if="activeTab === 'upload'" :class="showTipeFile ? 'mt-3' : 'mt-1'">
            <input type="file" class="hidden" ref="fileInputRef" @change="handleFileChange" accept=".pdf,.ai,.jpg,.jpeg,.png,.zip" :disabled="disabled" />
            <button v-if="!selectedFile" type="button" @click="!disabled && fileInputRef.click()" :class="['btn btn-outline border-dashed border-2 w-full flex items-center justify-start gap-3 rounded-xl h-11 bg-base-200/30 hover:bg-base-200 min-w-0 px-4 overflow-hidden', internalError ? 'border-error bg-error/5 text-error' : 'border-base-300', disabled ? 'cursor-not-allowed' : '']">
                <svg v-if="internalError" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-primary shrink-0"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                <span :class="['font-black uppercase text-[9px] tracking-tighter truncate w-full text-left', internalError ? 'opacity-100' : 'opacity-50']">{{ internalError || "Lampirkan File" }}</span>
            </button>
            <div v-else class="flex items-center w-full min-w-0 gap-3 p-2 border bg-primary/10 rounded-xl border-primary/20 h-11">
                <div class="bg-primary text-white p-1.5 rounded-lg shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><path d="M13 2H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                </div>
                <div class="flex-1 min-w-0 overflow-hidden">
                    <a
                        v-if="fileUrl"
                        :href="fileUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="block text-[10px] font-black truncate uppercase tracking-tighter leading-none text-primary hover:underline"
                    >
                        {{ displayFileName }}
                    </a>
                    <p
                        v-else
                        class="text-[10px] font-black truncate uppercase tracking-tighter leading-none"
                    >
                        {{ displayFileName }}
                    </p>
                </div>
                <button v-if="!disabled" type="button" @click="removeFile" class="mr-1 btn btn-ghost btn-circle btn-xs text-error shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-3 h-3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </div>

        <div v-if="activeTab === 'link'" class="mt-3">
            <input type="url" placeholder="https://drive.google.com/..." :value="linkDrive" @input="updateLink" :disabled="disabled" class="w-full text-xs font-bold transition-all input input-bordered h-11 rounded-xl bg-base-100 focus:border-primary focus:ring-2 focus:ring-primary/20 border-base-300 disabled:opacity-50 disabled:cursor-not-allowed" />
        </div>

        <div v-if="activeTab === 'email'" class="flex items-center gap-2 px-3 mt-3 border h-11 bg-base-200/50 border-base-300 rounded-xl">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 text-primary opacity-50 shrink-0"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <p class="text-[10px] font-bold opacity-70 truncate text-base-content/80">Kirim ke: <b class="select-all text-primary">cs@bikincetak.com</b></p>
        </div>

        <p v-if="error" class="text-error text-[10px] mt-1 font-bold">{{ error }}</p>
    </div>
</template>
