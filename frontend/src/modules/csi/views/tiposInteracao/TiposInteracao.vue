<template>
  <v-container fluid class="management-container">
    <v-row>
      <v-col cols="12">
        <v-card class="management-card">
          <v-card-title class="management-title">
            Gestão de Tipos de Interação
            <v-spacer></v-spacer>
            <v-btn 
              v-if="canCreate"
              class="management-new-btn" 
              @click="createTipoInteracao"
              :disabled="loading"
            >
              <v-icon left>mdi-plus</v-icon>
              Novo Tipo
            </v-btn>
          </v-card-title>

          <!-- Filtros -->
          <TipoInteracaoFilters />
          
          <!-- Exibir erro se houver -->
          <v-alert
            v-if="error"
            type="error"
            variant="tonal"
            density="compact"
            class="ma-4"
            closable
            @click:close="clearError"
          >
            <div class="d-flex align-center justify-space-between">
              <span>{{ error }}</span>
              <v-btn
                v-if="error.includes('Sessão expirada') || error.includes('conexão') || error.includes('servidor')"
                size="small"
                color="error"
                variant="outlined"
                @click="retryFetch"
              >
                <v-icon left>mdi-refresh</v-icon>
                Tentar Novamente
              </v-btn>
            </div>
          </v-alert>
          
          <v-data-table
            :key="tableKey"
            :headers="headers"
            :items="tiposInteracao"
            :loading="loading"
            :items-per-page="pagination.per_page"
            :page.sync="pagination.current_page"
            :server-items-length="pagination.total"
            class="management-data-table"
          >
            
            <template #item.conector="{ item }">
              <div v-if="item.conector">
                <v-chip size="small" color="default" variant="outlined">
                  {{ item.conector.nome }}
                </v-chip>
                <!-- <div v-if="item.porta || item.rota" class="text-caption mt-1">
                  <span v-if="item.porta">Porta: {{ item.porta }}</span>
                  <span v-if="item.rota">Rota: {{ item.rota }}</span>
                </div> -->
              </div>
              <span v-else class="text-grey">Sem conector</span>
            </template>
            
            
            <template #item.status="{ item }">
              <StatusBadge 
                :status="item.status === true" 
                active-text="Ativo"
                inactive-text="Inativo"
              />
            </template>
            
            <template #item.actions="{ item }">
              <div class="d-flex gap-2">
                <v-btn 
                  size="small" 
                  class="management-action-btn view-btn" 
                  @click="viewTipoInteracao(item)"
                  :disabled="loading || !canView"
                >
                  Visualizar
                </v-btn>
                
                <v-btn 
                  v-if="canDelete"
                  size="small" 
                  class="management-action-btn delete-btn" 
                  @click="deleteTipoInteracao(item.id)"
                  :disabled="loading"
                >
                  Excluir
                </v-btn>
              </div>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>

  </v-container>
</template>

<script setup lang="ts">
import { onMounted, computed} from 'vue';
import { useRouter } from 'vue-router';
import { useTipoInteracaoStore } from '@/modules/csi/stores/tipoInteracao';
import { usePermissions } from '@/composables/usePermissions';
import StatusBadge from '@/components/common/StatusBadge.vue';
import TipoInteracaoFilters from '@/components/common/TipoInteracaoFilters.vue';
import type { TipoInteracao } from '@/types/tipoInteracao';

const router = useRouter();
const tipoInteracaoStore = useTipoInteracaoStore();
const { tipoInteracaoPermissions } = usePermissions();

// Estados locais

// Computed para garantir reatividade
const tiposInteracao = computed(() => tipoInteracaoStore.tiposInteracao);
const pagination = computed(() => tipoInteracaoStore.pagination);
const loading = computed(() => tipoInteracaoStore.loading);
const error = computed(() => tipoInteracaoStore.error);

// Verificação de permissões
const canView = computed(() => tipoInteracaoPermissions.value.canView);
const canCreate = computed(() => tipoInteracaoPermissions.value.canCreate);
const canEdit = computed(() => tipoInteracaoPermissions.value.canEdit);
const canDelete = computed(() => tipoInteracaoPermissions.value.canDelete);

// Força reatividade com key reativo
const tableKey = computed(() => {
  return `table-${tipoInteracaoStore.tiposInteracao.length}-${tipoInteracaoStore.pagination.total}`;
});

const headers = [
  { title: 'Nome', key: 'nome', sortable: true },
  { title: 'Conector', key: 'conector', sortable: false },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Ações', key: 'actions', sortable: false },
];

// Métodos
function createTipoInteracao() {
  router.push('/csi/tipos-interacao/novo');
}

function viewTipoInteracao(item: TipoInteracao) {
  if (!item.id) {
    alert('Erro: ID do tipo de interação não encontrado');
    return;
  }
  
  // Se tem permissão de editar, vai direto para edição
  // Se só tem permissão de visualizar, vai para visualização
  if (canEdit.value) {
    router.push(`/csi/tipos-interacao/editar/${item.id}`);
  } else {
    router.push(`/csi/tipos-interacao/visualizar/${item.id}`);
  }
}

async function deleteTipoInteracao(id: number) {
  if (!id) {
    alert('Erro: ID do tipo de interação inválido');
    return;
  }
  
  if (confirm('Deseja realmente excluir este tipo de interação?')) {
    await tipoInteracaoStore.deleteTipoInteracao(id);
  }
}


function clearError() {
  tipoInteracaoStore.clearError();
}

async function retryFetch() {
  clearError();
  await tipoInteracaoStore.fetchTiposInteracao();
}

onMounted(async () => {
  if (canView.value) {
    await tipoInteracaoStore.fetchTiposInteracao();
  } else {
    tipoInteracaoStore.tiposInteracao = [];
  }
});
</script>

<style scoped>
/* Estilos específicos da view de Tipos de Interação (se necessário) */
/* Os estilos globais estão em src/styles/pages/_management-views.scss */


.gap-2 {
  gap: 8px;
}
</style>
