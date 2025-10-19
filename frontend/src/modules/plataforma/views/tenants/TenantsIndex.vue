<template>
  <v-container fluid class="management-container">
    <v-row>
      <v-col cols="12">
        <!-- Verificar se usuário tem permissão -->
        <v-alert
          v-if="!hasTenantsPermission"
          type="warning"
          variant="tonal"
          class="mb-4"
        >
          <v-icon>mdi-shield-alert</v-icon>
          <span class="ml-2">Acesso negado. Você não tem permissão para gerenciar tenants.</span>
        </v-alert>
        
        <v-card v-else class="management-card">
          <v-card-title class="management-title">
            Gestão de Tenants
            <v-spacer></v-spacer>
            <v-btn 
              v-if="tenantPermissions.canCreate"
              class="management-new-btn" 
              @click="openForm()"
              :disabled="tenantStore.loading"
            >
              <v-icon left>mdi-plus</v-icon>
              Novo Tenant
            </v-btn>
          </v-card-title>
          <BaseFilters :filters="tenantStore.filters" :expanded="tenantStore.filtersExpanded" @update:filters="updateTenantFilters" @apply="applyTenantFilters" @clear="clearTenantFilters">
            <template #filters="{ localFilters }">
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="localFilters.nome"
                  label="Nome"
                  variant="outlined"
                  density="compact"
                  clearable
                  prepend-icon="mdi-domain"
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
          
          <!-- Exibir erro se houver -->
          <v-alert
            v-if="tenantStore.error"
            type="error"
            variant="tonal"
            density="compact"
            class="ma-4"
            closable
            @click:close="clearError"
          >
            <div class="d-flex align-center justify-space-between">
              <span>{{ tenantStore.error }}</span>
              <v-btn
                v-if="tenantStore.error.includes('Sessão expirada') || tenantStore.error.includes('conexão') || tenantStore.error.includes('servidor')"
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
            :items="tenantStore.tenants"
            :loading="tenantStore.loading"
            :items-per-page="tenantStore.pagination.per_page"
            :page.sync="tenantStore.pagination.current_page"
            :server-items-length="tenantStore.pagination.total"
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
                @click="viewTenant(item)"
                :disabled="tenantStore.loading || !tenantPermissions.canView"
              >
                Visualizar
              </v-btn>
              <v-btn 
                v-if="tenantPermissions.canDelete"
                size="small" 
                class="management-action-btn delete-btn" 
                @click="deleteTenant(item.id)"
                :disabled="tenantStore.loading"
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
import BaseFilters from '@/components/common/BaseFilters.vue';
const statusOptions = [
  { text: 'Todos', value: null },
  { text: 'Ativo', value: true },
  { text: 'Inativo', value: false },
];

function updateTenantFilters(filters: Record<string, any>) {
  tenantStore.setFilters(filters);
  tenantStore.fetchTenants({ ...tenantStore.filters });
}

function applyTenantFilters() {
  tenantStore.fetchTenants({ ...tenantStore.filters });
}

function clearTenantFilters() {
  tenantStore.clearFilters();
  tenantStore.fetchTenants();
}
import { onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useTenantStore } from '@/modules/plataforma/stores/tenant';
import { useAuthStore } from '@/modules/plataforma/stores/auth';
import { usePermissions } from '@/composables/usePermissions';
import StatusBadge from '@/components/common/StatusBadge.vue';

const router = useRouter();
const tenantStore = useTenantStore();
const authStore = useAuthStore();
const { tenantPermissions } = usePermissions();

// Verificar se o usuário tem permissão para visualizar tenants
const hasTenantsPermission = computed(() => {
  return tenantPermissions.value.canView;
});

const headers = [
  { title: 'Nome', key: 'nome', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Ações', key: 'actions', sortable: false },
];

function openForm() {
  router.push('/plataforma/tenants/novo');
}

function viewTenant(item: any) {
  const tenantId = item.id;
  
  if (!tenantId) {
    console.error('ID do tenant não encontrado:', item);
    alert('Erro: ID do tenant não encontrado');
    return;
  }
  
  // Se tem permissão de editar, vai direto para edição
  // Se só tem permissão de visualizar, vai para visualização
  if (tenantPermissions.value.canEdit) {
    router.push(`/plataforma/tenants/editar/${tenantId}`);
  } else {
    router.push(`/plataforma/tenants/visualizar/${tenantId}`);
  }
}

async function deleteTenant(id: number) {
  if (!id) {
    console.error('ID do tenant inválido para exclusão');
    alert('Erro: ID do tenant inválido');
    return;
  }
  
  if (confirm('Deseja realmente excluir este tenant?')) {
    await tenantStore.deleteTenant(id);
  }
}

function clearError() {
  tenantStore.error = null;
}

async function retryFetch() {
  clearError();
  await tenantStore.fetchTenants();
}

onMounted(async () => {
  // Aguardar o carregamento do usuário antes de verificar permissões
  if (!authStore.user) {
    await authStore.checkAuth();
  }
  
  // Só carrega tenants se o usuário tiver a permissão
  if (hasTenantsPermission.value) {
    await tenantStore.fetchTenants();
  }
});
</script>

<style scoped>
/* Estilos específicos da view de Tenants (se necessário) */
/* Os estilos globais estão em src/styles/pages/_management-views.scss */
</style>