<template>
  <v-container fluid class="management-container">
    <v-row>
      <v-col cols="12">
        <v-card class="management-card">
          <v-card-title class="management-title">
            Gestão de Usuários
            <v-spacer></v-spacer>
            <v-btn 
              v-if="userPermissions.canCreate"
              class="management-new-btn" 
              @click="openForm()"
              :disabled="userStore.loading"
            >
              <v-icon left>mdi-plus</v-icon>
              Novo Usuário
            </v-btn>
          </v-card-title>
          
          <!-- Componente de Filtros -->
          <UserFilters />
          
          <!-- Exibir erro se houver -->
          <v-alert
            v-if="userStore.error"
            type="error"
            variant="tonal"
            density="compact"
            class="ma-4"
            closable
            @click:close="clearError"
          >
            <div class="d-flex align-center justify-space-between">
              <span>{{ userStore.error }}</span>
              <v-btn
                v-if="userStore.error.includes('Sessão expirada') || userStore.error.includes('conexão') || userStore.error.includes('servidor')"
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
            :items="userStore.users"
            :loading="userStore.loading"
            :items-per-page="userStore.pagination.per_page"
            :page.sync="userStore.pagination.current_page"
            :server-items-length="userStore.pagination.total"
            class="management-data-table"
          >
            <!-- Template para status -->
            <template #item.status="{ item }">
              <StatusBadge :status="item.status" />
            </template>
            
            <template #item.actions="{ item }">
              <v-btn 
                size="small" 
                class="management-action-btn view-btn" 
                @click="viewUser(item)"
                :disabled="userStore.loading || !userPermissions.canView"
              >
                Visualizar
              </v-btn>
              <v-btn 
                v-if="userPermissions.canDelete"
                size="small" 
                class="management-action-btn delete-btn" 
                @click="deleteUser(item.id)"
                :disabled="userStore.loading"
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
import { useUserStore } from '@/modules/plataforma/stores/user';
import { useAuthStore } from '@/modules/plataforma/stores/auth';
import { usePermissions } from '@/composables/usePermissions';
import StatusBadge from '@/components/common/StatusBadge.vue';
import UserFilters from '@/components/common/UserFilters.vue';

const router = useRouter();
const userStore = useUserStore();
const authStore = useAuthStore();
const { userPermissions } = usePermissions();

// Função para limpar erro
function clearError() {
  userStore.error = null;
}

// Função para tentar novamente
function retryFetch() {
  clearError();
  userStore.fetchUsers();
}

const headers = [
  { title: 'Nome', key: 'name', sortable: true },
  { title: 'Login', key: 'usuario', sortable: true },
  { title: 'Domínio', key: 'dominio', sortable: true },
  { title: 'Email', key: 'email', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Ações', key: 'actions', sortable: false },
];

function openForm() {
  router.push('/plataforma/usuarios/novo');
}

function viewUser(item: any) {
  const userId = item.id;
  
  if (!userId) {
    console.error('ID do usuário não encontrado:', item);
    alert('Erro: ID do usuário não encontrado');
    return;
  }
  
  // Se tem permissão de editar, vai direto para edição
  // Se só tem permissão de visualizar, vai para visualização
  if (userPermissions.value.canEdit) {
    router.push(`/plataforma/usuarios/editar/${userId}`);
  } else {
    router.push(`/plataforma/usuarios/visualizar/${userId}`);
  }
}

async function deleteUser(id: number) {
  if (!id) {
    console.error('ID do usuário inválido para exclusão');
    alert('Erro: ID do usuário inválido');
    return;
  }
  
  if (confirm('Deseja realmente excluir este usuário?')) {
    await userStore.deleteUser(id);
  }
}

onMounted(async () => {
  // Aguardar o carregamento do usuário antes de carregar dados
  if (!authStore.user) {
    await authStore.checkAuth();
  }
  
  await userStore.fetchUsers();
});
</script>

<style scoped>
/* Estilos específicos da view de Usuários (se necessário) */
/* Os estilos globais estão em src/styles/pages/_management-views.scss */
</style> 