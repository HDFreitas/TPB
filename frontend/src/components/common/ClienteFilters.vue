<template>
    <BaseFilters
        :filters="filters"
        :expanded="filtersExpanded"
        @update:filters="updateFilters"
        @apply="applyFilters"
        @clear="clearFilters"
    >
        <template #filters="{ localFilters, applyFilters, clearFilters }">
            <v-col cols="12" md="3">
                <v-text-field
                    v-model="localFilters.nome_fantasia"
                    label="Nome Fantasia"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-domain"
                />
            </v-col>

            <v-col cols="12" md="3">
                <v-text-field
                    v-model="localFilters.codigo"
                    label="Código"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-tag"
                />
            </v-col>

            <v-col cols="12" md="3">
                <v-text-field
                    v-model="localFilters.cnpj_cpf"
                    label="CNPJ/CPF"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-card-account-details"
                />
            </v-col>

            <v-col cols="12" md="3">
                <v-text-field
                    v-model="localFilters.codigo_senior"
                    label="Código Senior"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-numeric"
                />
            </v-col>

            <v-col cols="12" md="3">
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
import { useClienteStore, type ClienteState } from '@/modules/csi/stores/cliente';
import BaseFilters from './BaseFilters.vue';

const clienteStore = useClienteStore();

// Use the filter type from the store for consistency
type ClienteFilters = ClienteState['filters'];

const statusOptions = [
    { text: 'Todos', value: null },
    { text: 'Ativo', value: true },
    { text: 'Inativo', value: false },
];

// Computed properties para o BaseFilters
const filters = computed(() => clienteStore.filters);
const filtersExpanded = computed(() => clienteStore.filtersExpanded);

function updateFilters(newFilters: ClienteFilters) {
    // Atualiza o store com os filtros - abordagem dinâmica e type-safe
    (Object.keys(newFilters) as (keyof ClienteFilters)[]).forEach(key => {
        clienteStore.setFilter(key, newFilters[key]);
    });
}

function applyFilters() {
    clienteStore.applyFilters();
}

function clearFilters() {
    clienteStore.clearFilters();
}
</script>