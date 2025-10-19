<template>
  <BaseFilters :filters="localFilters" @update:filters="updateFilters" @apply="applyFilters" @clear="clearFilters">
    <template #filters="{ localFilters }">
      <v-col cols="12" md="3">
        <v-text-field
          v-model="localFilters.nome"
          label="Nome"
          variant="outlined"
          density="compact"
          clearable
          prepend-icon="mdi-message-text"
        />
      </v-col>
      <v-col cols="12" md="3">
        <v-select
          v-model="localFilters.conector_id"
          :items="conectorOptions"
          item-title="nome"
          item-value="id"
          label="Conector"
          variant="outlined"
          density="compact"
          clearable
          prepend-icon="mdi-connection"
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
import { ref, computed, onMounted } from 'vue';
import { useTipoInteracaoStore } from '@/modules/csi/stores/tipoInteracao';
import { useConectorStore } from '@/modules/csi/stores/conector';
import BaseFilters from '@/components/common/BaseFilters.vue';
import type { TipoInteracaoSearchFilters } from '@/types/tipoInteracao';

const tipoInteracaoStore = useTipoInteracaoStore();
const conectorStore = useConectorStore();
const localFilters = ref<TipoInteracaoSearchFilters>({
  nome: '',
  conector_id: undefined,
  status: undefined,
});

const conectorOptions = computed(() => {
  return conectorStore.conectores
    .filter(conector => conector.status === true)
    .map(conector => ({
      id: conector.id,
      nome: conector.nome
    }));
});

const statusOptions = [
  { text: 'Ativo', value: true },
  { text: 'Inativo', value: false }
];

function updateFilters(newFilters: TipoInteracaoSearchFilters) {
  localFilters.value = { ...newFilters };
}

async function applyFilters() {
  const cleanFilters = Object.fromEntries(
    Object.entries(localFilters.value).filter(([_, value]) =>
      value !== null && value !== undefined && value !== ''
    )
  );
  tipoInteracaoStore.setFilters(cleanFilters);
  await tipoInteracaoStore.fetchTiposInteracao();
}

function clearFilters() {
  localFilters.value = {
    nome: '',
    conector_id: undefined,
    status: undefined,
  };
  tipoInteracaoStore.clearFilters();
  tipoInteracaoStore.fetchTiposInteracao();
}

onMounted(async () => {
  await conectorStore.fetchConectores();
  await tipoInteracaoStore.fetchTiposInteracao();
});
</script>

<style scoped>
.gap-2 {
  gap: 8px;
}
</style>
