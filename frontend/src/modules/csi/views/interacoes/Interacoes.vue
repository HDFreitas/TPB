<template>
  <v-container fluid class="management-container">
    <v-row>
      <v-col cols="12">
        <v-card class="management-card">
          <v-card-title class="management-title">
            Gestão de Interações
            <v-spacer></v-spacer>
            <v-btn 
              class="management-new-btn" 
              @click="openForm()"
              :disabled="interacaoStore.loading"
            >
              <v-icon left>mdi-plus</v-icon>
              Nova Interação
            </v-btn>
          </v-card-title>
          
          <!-- Filtros -->
          <InteracaoFilters class="px-4" />

          <!-- Exibir erro se houver -->
          <v-alert
            v-if="interacaoStore.error"
            type="error"
            variant="tonal"
            density="compact"
            class="ma-4"
            closable
            @click:close="clearError"
          >
            <div class="d-flex align-center justify-space-between">
              <span>{{ interacaoStore.error }}</span>
              <v-btn
                v-if="interacaoStore.error.includes('Sessão expirada') || interacaoStore.error.includes('conexão') || interacaoStore.error.includes('servidor')"
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
            :headers="headers"
            :items="interacaoStore.interacoes"
            :loading="interacaoStore.loading"
            :items-per-page="interacaoStore.pagination.per_page"
            :page.sync="interacaoStore.pagination.current_page"
            :server-items-length="interacaoStore.pagination.total"
            class="management-data-table"
          >
            <template #item.data_interacao="{ item }">
              {{ formatDate(item.data_interacao) }}
            </template>
            <template #item.valor="{ item }">
              {{ formatCurrency(item.valor) }}
            </template>
           <template #item.tipo_interacao="{ item }">
            {{ item.tipo_interacao?.nome || '' }}
          </template>
            <template #item.actions="{ item }">
              <v-btn size="small" variant="flat" color="white" elevation="2" class="management-action-btn view-btn" @click="editInteracao(item)">
                Visualizar
              </v-btn>
              <v-btn size="small" class="management-action-btn delete-btn" @click="deleteInteracao(item.id)">
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
import { useInteracaoStore } from '@/modules/csi/stores/interacao';
import InteracaoFilters from '@/components/common/InteracaoFilters.vue';
import { useTipoInteracaoStore } from '@/modules/csi/stores/tipoInteracao';

const tipoInteracaoStore = useTipoInteracaoStore();
const router = useRouter();
const interacaoStore = useInteracaoStore();


// Função para limpar erro
function clearError() {
  interacaoStore.error = null;
}

// Função para tentar novamente
function retryFetch() {
  clearError();
  interacaoStore.fetchInteracoes();
}


const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Cliente', key: 'cliente.razao_social' },
  { title: 'Tipo', key: 'tipo_interacao' },
  { title: 'Origem', key: 'origem' },
  { title: 'Data', key: 'data_interacao' },
  { title: 'Título', key: 'titulo' },
  { title: 'Valor', key: 'valor' },
  { title: 'Usuário', key: 'user.name' },
  { title: 'Ações', key: 'actions', sortable: false },
];

function openForm() {
  router.push('/csi/interacoes/nova');
}

function editInteracao(item: any) {
  const interacaoId = item.id;
  
  if (!interacaoId) {
    alert('Erro: ID da interação não encontrado');
    return;
  }
  
  router.push(`/csi/interacoes/editar/${interacaoId}`);
}

async function deleteInteracao(id: number) {
  if (!id) {
    alert('Erro: ID da interação inválido');
    return;
  }
  
  if (confirm('Deseja realmente excluir esta interação?')) {
    await interacaoStore.deleteInteracao(id);
  }
}

onMounted(async () => {
  await interacaoStore.applyFilters();
  await tipoInteracaoStore.fetchTiposInteracao();
});

function formatCurrency(value: number | null | undefined) {
  if (value === null || value === undefined) return '';
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value));
}

function formatDate(value: string | Date | null | undefined) {
  if (!value) return '';
  const d = typeof value === 'string' ? new Date(value) : value;
  if (isNaN(d as any)) return '';
  return d.toLocaleDateString('pt-BR');
}
</script>

<style scoped>
/* Estilos específicos da view de Interações (se necessário) */
/* Os estilos globais estão em src/styles/pages/_management-views.scss */
</style>