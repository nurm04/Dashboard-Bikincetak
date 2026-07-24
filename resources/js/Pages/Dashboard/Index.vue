<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminPanel from './Partials/AdminPanel.vue';
import KasirPanel from './Partials/KasirPanel.vue';
import ProduksiPanel from './Partials/ProduksiPanel.vue';

const page = usePage();

const currentPanel = computed(() => {
    const roleView = page.props.role_view;
    if (roleView === 'admin') return AdminPanel;
    if (roleView === 'kasir') return KasirPanel;
    if (roleView === 'produksi') return ProduksiPanel;
    return ProduksiPanel;
});
</script>

<template>
    <component :is="currentPanel" v-bind="$page.props" />
</template>
