<template>
  <v-container fluid class="management-container">
    <v-row>
      <v-col cols="12">
        <v-card class="management-card">
          <v-card-title class="management-title">
            Logs de Auditoria
          </v-card-title>
          
          <!-- Componente de Filtros -->
          <LogFilters />
          <v-data-table
            :headers="headers"
            :items="logStore.logs"
            :loading="logStore.loading"
            :items-per-page="logStore.pagination.per_page"
            :page.sync="logStore.pagination.current_page"
            :server-items-length="logStore.pagination.total"
            class="management-data-table"
            @click:row="(_, { item }) => viewLogDetails(item)"
            hover
          >
            <template #item.created_at="{ item }">
              <span class="log-date">{{ formatDate(item.created_at) }}</span>
            </template>
            <template #item.action="{ item }">
              <v-chip 
                :class="getActionChipClass(item.action)"
                size="small"
                variant="outlined"
              >
                {{ item.action }}
              </v-chip>
            </template>
            <template #item.actions="{ item }">
              <v-btn 
                size="small" 
                class="management-action-btn view-btn mr-2" 
                @click.stop="viewLogDetails(item)"
              >
                Visualizar
              </v-btn>
              <v-btn 
                size="small" 
                class="management-action-btn delete-btn" 
                @click.stop="deleteLog(item.id)"
              >
                Excluir
              </v-btn>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useLogStore } from '@/modules/plataforma/stores/log';
import LogFilters from '@/components/common/LogFilters.vue';

const router = useRouter();
const logStore = useLogStore();

const headers = [
  { title: 'ID', key: 'id', sortable: true },
  { title: 'Usuário', key: 'user_id', sortable: true },
  { title: 'Ação', key: 'action', sortable: true },
  { title: 'Descrição', key: 'description', sortable: true },
  { title: 'IP', key: 'ip_address', sortable: true },
  { title: 'Data e Hora', key: 'created_at', sortable: true },
  { title: 'Ações', key: 'actions', sortable: false },
];

function formatDate(dateString: string) {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
}

function getActionChipClass(action: string) {
  const actionLower = action?.toLowerCase() || '';
  
  if (actionLower.includes('create') || actionLower.includes('criar')) {
    return ['management-status-chip', 'action-create'];
  } else if (actionLower.includes('update') || actionLower.includes('edit') || actionLower.includes('atualiz')) {
    return ['management-status-chip', 'action-update'];
  } else if (actionLower.includes('delete') || actionLower.includes('exclu') || actionLower.includes('remov')) {
    return ['management-status-chip', 'action-delete'];
  } else if (actionLower.includes('login') || actionLower.includes('auth')) {
    return ['management-status-chip', 'action-auth'];
  } else {
    return ['management-status-chip', 'action-other'];
  }
}

async function deleteLog(id: number) {
  if (confirm('Deseja realmente excluir este log?')) {
    await logStore.deleteLog(id);
  }
}

function viewLogDetails(log: any) {
  router.push(`/logs/${log.id}`);
}

onMounted(() => {
  logStore.fetchLogs();
});
</script>

<style scoped>
/* Estilos específicos da view de Logs */
/* Os estilos globais estão em src/styles/pages/_management-views.scss */

.management-filter-section {
  background: #f8f9fa;
  padding: 24px !important;
  border-bottom: 1px solid #e9ecef;
}

.log-date {
  font-family: 'Courier New', monospace;
  font-size: 0.85rem;
  color: #495057;
  background: #f8f9fa;
  padding: 4px 8px;
  border-radius: 4px;
  border: 1px solid #e9ecef;
}

/* Chips para diferentes tipos de ação - Estilo dos botões sem hover */
.action-create {
  background: white !important;
  color: #1976D2 !important;
  border: 2px solid #1976D2 !important;
  font-weight: 500 !important;
}

.action-update {
  background: white !important;
  color: #616161 !important;
  border: 2px solid #616161 !important;
  font-weight: 500 !important;
}

.action-delete {
  background: white !important;
  color: #D32F2F !important;
  border: 2px solid #D32F2F !important;
  font-weight: 500 !important;
}

.action-auth {
  background: white !important;
  color: #7B1FA2 !important;
  border: 2px solid #7B1FA2 !important;
  font-weight: 500 !important;
}

.action-other {
  background: white !important;
  color: #757575 !important;
  border: 2px solid #757575 !important;
  font-weight: 500 !important;
}
</style> 