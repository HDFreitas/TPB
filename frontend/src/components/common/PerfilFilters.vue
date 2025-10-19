<template>
    <BaseFilters
        :filters="filters"
        :expanded="filtersExpanded"
        @update:filters="updateFilters"
        @apply="applyFilters"
        @clear="clearFilters"
    >
        <template #filters="{ localFilters, applyFilters, clearFilters }">
            <v-col cols="12" md="6">
                <v-text-field
                    v-model="localFilters.name"
                    label="Nome do Perfil"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-shield-account"
                />
            </v-col>

            <v-col cols="12" md="6">
                <v-select
                    v-model="localFilters.status"
                    :items="statusOptions"
                    item-title="text"
                    item-value="value"
                    label="Status"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-check-circle"
                />
            </v-col>
        </template>
    </BaseFilters>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { usePerfilStore, type PerfilState } from '@/modules/plataforma/stores/perfil';
import BaseFilters from './BaseFilters.vue';

const perfilStore = usePerfilStore();

// Use the filter type from the store for consistency
type PerfilFilters = PerfilState['filters'];

const statusOptions = [
    { text: 'Todos', value: null },
    { text: 'Ativo', value: true },
    { text: 'Inativo', value: false },
];

// Computed properties para o BaseFilters
const filters = computed(() => perfilStore.filters);
const filtersExpanded = computed(() => perfilStore.filtersExpanded);

function updateFilters(newFilters: PerfilFilters) {
    // Atualiza o store com os filtros - abordagem dinâmica e type-safe
    (Object.keys(newFilters) as (keyof PerfilFilters)[]).forEach(key => {
        perfilStore.setFilter(key, newFilters[key]);
    });
}

function applyFilters() {
    perfilStore.applyFilters();
}

function clearFilters() {
    perfilStore.clearFilters();
}
</script>
