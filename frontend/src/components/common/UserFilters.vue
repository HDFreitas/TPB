<template>
    <BaseFilters
        :filters="filters"
        :expanded="filtersExpanded"
        @update:filters="updateFilters"
        @apply="applyFilters"
        @clear="clearFilters"
    >
        <template #filters="{ localFilters, applyFilters, clearFilters }">
            <v-col cols="12" md="4">
                <v-text-field
                    v-model="localFilters.name"
                    label="Nome"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-account"
                />
            </v-col>

            <v-col cols="12" md="4">
                <v-text-field
                    v-model="localFilters.usuario"
                    label="Login/Usuário"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-account-circle"
                />
            </v-col>

            <v-col cols="12" md="4">
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
import { useUserStore, type UserState } from '@/modules/plataforma/stores/user';
import BaseFilters from './BaseFilters.vue';

const userStore = useUserStore();

// Use the filter type from the store for consistency
type UserFilters = UserState['filters'];

const statusOptions = [
    { text: 'Todos', value: null },
    { text: 'Ativo', value: true },
    { text: 'Inativo', value: false },
];

// Computed properties para o BaseFilters
const filters = computed(() => userStore.filters);
const filtersExpanded = computed(() => userStore.filtersExpanded);

function updateFilters(newFilters: UserFilters) {
    // Atualiza o store com os filtros - abordagem dinâmica e type-safe
    (Object.keys(newFilters) as (keyof UserFilters)[]).forEach(key => {
        userStore.setFilter(key, newFilters[key]);
    });
}

function applyFilters() {
    userStore.applyFilters();
}

function clearFilters() {
    userStore.clearFilters();
}
</script>
