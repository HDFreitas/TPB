<template>
  <v-container fluid class="management-container">
    <v-row>
      <v-col cols="12">
        <v-card class="management-card">
          <v-card-title class="management-title">
            Gestão de Perfis
            <v-spacer></v-spacer>
                    <v-btn 
                      v-if="perfilPermissions.canCreate"
                      class="management-new-btn" 
                      @click="openForm()"
                      :disabled="perfilStore.loading"
                    >
                      <v-icon left>mdi-plus</v-icon>
                      Novo Perfil
                    </v-btn>
          </v-card-title>
          
          <!-- Componente de Filtros -->
          <PerfilFilters />
          
          <!-- Exibir erro se houver -->
          <v-alert
            v-if="perfilStore.error"
            type="error"
            variant="tonal"
            density="compact"
            class="ma-4"
            closable
            @click:close="clearError"
          >
            <div class="d-flex align-center justify-space-between">
              <span>{{ perfilStore.error }}</span>
              <v-btn
                v-if="perfilStore.error.includes('Sessão expirada') || perfilStore.error.includes('conexão') || perfilStore.error.includes('servidor')"
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
            :items="perfilStore.perfis"
            :loading="perfilStore.loading"
            :items-per-page="perfilStore.pagination.per_page"
            :page.sync="perfilStore.pagination.current_page"
            :server-items-length="perfilStore.pagination.total"
            class="management-data-table"
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
                        @click="viewPerfil(item)"
                        :disabled="perfilStore.loading || !perfilPermissions.canView"
                      >
                        Visualizar
                      </v-btn>
                      <v-btn 
                        v-if="perfilPermissions.canDelete"
                        size="small" 
                        class="management-action-btn delete-btn" 
                        @click="deletePerfil(item.id)"
                        :disabled="perfilStore.loading"
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
import { usePerfilStore } from '@/modules/plataforma/stores/perfil';
import { useAuthStore } from '@/modules/plataforma/stores/auth';
import { usePermissions } from '@/composables/usePermissions';
import PerfilFilters from '@/components/common/PerfilFilters.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';

const router = useRouter();
const perfilStore = usePerfilStore();
const authStore = useAuthStore();
const { perfilPermissions } = usePermissions();

// Função para limpar erro
function clearError() {
  perfilStore.error = null;
}

// Função para tentar novamente
function retryFetch() {
  clearError();
  perfilStore.fetchPerfis();
}

const headers = [
  { title: 'Nome', key: 'name', sortable: true },
  { title: 'Descrição', key: 'description', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Ações', key: 'actions', sortable: false },
];

function openForm() {
  router.push('/plataforma/perfis/novo');
}

function viewPerfil(item: any) {
  const perfilId = item.id;
  
  if (!perfilId) {
    console.error('ID do perfil não encontrado:', item);
    alert('Erro: ID do perfil não encontrado');
    return;
  }
  
  // Se tem permissão de editar, vai direto para edição
  // Se só tem permissão de visualizar, vai para visualização
  if (perfilPermissions.value.canEdit) {
    router.push(`/plataforma/perfis/editar/${perfilId}`);
  } else {
    router.push(`/plataforma/perfis/visualizar/${perfilId}`);
  }
}

async function deletePerfil(id: number) {
  if (!id) {
    console.error('ID do perfil inválido para exclusão');
    alert('Erro: ID do perfil inválido');
    return;
  }
  
  if (confirm('Deseja realmente excluir este perfil?')) {
    await perfilStore.deletePerfil(id);
  }
}


onMounted(async () => {
  // Aguardar o carregamento do usuário antes de carregar dados
  if (!authStore.user) {
    await authStore.checkAuth();
  }
  
  // Agora o backend filtra automaticamente por tenant do usuário logado
  await perfilStore.fetchPerfis();
});
</script>