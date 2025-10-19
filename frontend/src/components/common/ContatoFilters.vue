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
                    v-model="localFilters.nome"
                    label="Nome"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-account"
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
                    v-model="localFilters.email"
                    label="Email"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-email"
                />
            </v-col>

            <v-col cols="12" md="3">
                <v-text-field
                    v-model="localFilters.cargo"
                    label="Cargo"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-briefcase"
                />
            </v-col>

            <v-col cols="12" md="3">
                <v-select
                    v-model="localFilters.tipo_perfil"
                    :items="tipoPerfilOptions"
                    item-title="text"
                    item-value="value"
                    label="Tipo do Perfil"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-shield-account"
                />
            </v-col>

            <v-col cols="12" md="3">
                <v-select
                    v-model="localFilters.promotor"
                    :items="promotorOptions"
                    item-title="text"
                    item-value="value"
                    label="Promotor"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-account-star"
                />
            </v-col>

            <v-col cols="12" class="d-flex gap-2">
                <v-btn color="primary" @click="applyFilters">
                    <v-icon left>mdi-magnify</v-icon>
                    Filtrar
                </v-btn>
                <v-btn class="ml-3" color="secondary" @click="clearFilters">
                    <v-icon left>mdi-close</v-icon>
                    Limpar
                </v-btn>
            </v-col>
        </template>
    </BaseFilters>
</template>

<script setup lang="ts">
import BaseFilters from './BaseFilters.vue';

interface Props {
    filters: Record<string, any>;
    filtersExpanded: boolean;
}

interface Emits {
    (e: 'update:filters', filters: Record<string, any>): void;
    (e: 'apply'): void;
    (e: 'clear'): void;
}

defineProps<Props>();
const emit = defineEmits<Emits>();

const tipoPerfilOptions = [
    { text: 'Relacional', value: 'Relacional' },
    { text: 'Transacional', value: 'Transacional' }
];

const promotorOptions = [
    { text: 'Todos', value: null },
    { text: 'Sim', value: true },
    { text: 'Não', value: false }
];

function updateFilters(filters: Record<string, any>) {
    emit('update:filters', filters);
}

function applyFilters() {
    emit('apply');
}

function clearFilters() {
    emit('clear');
}
</script>
