<template>
  <v-container fluid class="management-container">
    <v-row>
      <v-col cols="12">
        <v-card class="management-card">
          <v-card-title class="management-title">
            Gestão de Conectores
          </v-card-title>
          <ConectorFilters />
          
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
            :items="conectores"
            :loading="loading"
            :items-per-page="pagination.per_page"
            :page.sync="pagination.current_page"
            :server-items-length="pagination.total"
            class="management-data-table"
            loading-text="Carregando conectores..."
          >
            <template #item.status="{ item }">
              <StatusBadge 
                :status="item.status === true" 
                active-text="Ativo"
                inactive-text="Inativo"
              />
            </template>
            <template #item.actions="{ item }">
              <v-btn 
                size="small" 
                class="management-action-btn view-btn" 
                @click="editConector(item)"
              >
                Visualizar
              </v-btn>
              <v-btn 
                v-if="canTestConnection"
                size="small" 
                class="management-action-btn test-btn" 
                @click="testConnection(item.id)"
                :loading="loading"
              >
                <v-icon left>mdi-connection</v-icon>
                Testar
              </v-btn>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { onMounted, computed } from 'vue';
import { toast } from 'vue3-toastify';
import { useRouter } from 'vue-router';
import { useConectorStore } from '@/modules/csi/stores/conector';
import { useAuthStore } from '@/modules/plataforma/stores/auth';
import StatusBadge from '@/components/common/StatusBadge.vue';
import ConectorFilters from '@/components/common/ConectorFilters.vue';
import 'vue3-toastify/dist/index.css';

const router = useRouter();
const conectorStore = useConectorStore();
const authStore = useAuthStore();

// Computed para garantir reatividade
const conectores = computed(() => {
  return conectorStore.conectores;
});

const pagination = computed(() => {
  return conectorStore.pagination;
});

const loading = computed(() => conectorStore.loading);
const error = computed(() => conectorStore.error);

// Verificação de permissões
const canTestConnection = computed(() => {
  return authStore.hasPermission('conectores.testar');
});

// Força reatividade com key reativo
const tableKey = computed(() => {
  return `table-${conectorStore.conectores.length}-${conectorStore.pagination.total}`;
});

const headers = [
  { title: 'Nome', key: 'nome', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Ações', key: 'actions', sortable: false },
];


function editConector(item: any) {
  const conectorId = item.id;
  
  if (!conectorId) {
    alert('Erro: ID do conector não encontrado');
    return;
  }
  
  router.push(`/csi/conectores/editar/${conectorId}`);
}

async function testConnection(id: number) {
  if (!id) {
    toast('Erro: ID do conector inválido', {
      type: 'error',
      position: 'top-right',
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      icon: false
    });
    return;
  }

  try {
    const result = await conectorStore.testConnection(id);
    if (result.success && (!result.data || result.data.status !== 'error')) {
      toast(result.message || 'Conexão testada com sucesso!', {
        type: 'success',
        position: 'top-right',
        autoClose: 5000,
        hideProgressBar: false,
        closeOnClick: true,
        pauseOnHover: true,
        icon: false
      });
    } else if (result.data && result.data.status === 'error') {
      toast('Erro ao testar conexão: ' + (result.data.message || result.message || 'Erro desconhecido'), {
        type: 'error',
        position: 'top-right',
        autoClose: 5000,
        hideProgressBar: false,
        closeOnClick: true,
        pauseOnHover: true,
        icon: false
      });
    } else {
      toast('Erro ao testar conexão: ' + (result.message || 'Erro desconhecido'), {
        type: 'error',
        position: 'top-right',
        autoClose: 5000,
        hideProgressBar: false,
        closeOnClick: true,
        pauseOnHover: true,
        icon: false
      });
    }
  } catch (error: any) {
    toast('Erro ao testar conexão: ' + (error.response?.data?.message || error.message), {
      type: 'error',
      position: 'top-right',
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      icon: false
    });
  }
}

function clearError() {
  conectorStore.error = null;
}

async function retryFetch() {
  clearError();
  await conectorStore.fetchConectores();
}


onMounted(async () => {
  await conectorStore.fetchConectores();
});
</script>

<style scoped>
/* Estilos específicos da view de Conectores (se necessário) */
/* Os estilos globais estão em src/styles/pages/_management-views.scss */
</style>
