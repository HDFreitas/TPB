<template>
  <BaseFilters
    :filters="filters"
    :expanded="filtersExpanded"
    @update:filters="updateFilters"
    @apply="applyFilters"
    @clear="clearFilters"
  >
    <template #filters="{ localFilters }">
      <v-col cols="12" md="3">
        <v-select
          v-model="localFilters.tipo_interacao_id"
          :items="tipoInteracaoOptions"
          item-title="nome"
          item-value="id"
          label="Tipo de Interação"
          variant="outlined"
          density="compact"
          clearable
          prepend-icon="mdi-tag"
        />
      </v-col>

      <v-col cols="12" md="3">
        <v-text-field
          v-model="localFilters.data_interacao_from"
          type="date"
          label="Data de"
          variant="outlined"
          density="compact"
          clearable
          prepend-icon="mdi-calendar"
        />
      </v-col>

      <v-col cols="12" md="3">
        <v-text-field
          v-model="localFilters.data_interacao_to"
          type="date"
          label="Data até"
          variant="outlined"
          density="compact"
          clearable
          prepend-icon="mdi-calendar"
        />
      </v-col>

      <v-col cols="12" md="3">
        <v-text-field
          v-model="localFilters.cliente_nome"
          label="Cliente (nome)"
          variant="outlined"
          density="compact"
          clearable
          prepend-icon="mdi-account"
        />
      </v-col>

      <v-col cols="12" md="3">
        <v-text-field
          v-model.number="localFilters.valor_from"
          type="number"
          step="0.01"
          label="Valor de"
          variant="outlined"
          density="compact"
          clearable
          prepend-icon="mdi-currency-brl"
        />
      </v-col>

      <v-col cols="12" md="3">
        <v-text-field
          v-model.number="localFilters.valor_to"
          type="number"
          step="0.01"
          label="Valor até"
          variant="outlined"
          density="compact"
          clearable
          prepend-icon="mdi-currency-brl"
        />
      </v-col>
    </template>
  </BaseFilters>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import BaseFilters from './BaseFilters.vue';
import { useInteracaoStore } from '@/modules/csi/stores/interacao';
import { useTipoInteracaoStore } from '@/modules/csi/stores/tipoInteracao';

const interacaoStore = useInteracaoStore();
const tipoInteracaoStore = useTipoInteracaoStore();

const filters = computed(() => interacaoStore.filters);
const filtersExpanded = computed(() => interacaoStore.filtersExpanded);
const tipoInteracaoOptions = computed(() => tipoInteracaoStore.tiposInteracao);

function updateFilters(newFilters: Record<string, any>) {
  (Object.keys(newFilters) as (keyof typeof interacaoStore.filters)[]).forEach(key => {
    interacaoStore.setFilter(key, newFilters[key]);
  });
}

function applyFilters() {
  interacaoStore.applyFilters();
}

function clearFilters() {
  interacaoStore.clearFilters();
}
</script>


