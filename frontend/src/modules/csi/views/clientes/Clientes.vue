<template>
  <v-container fluid class="management-container">
    <v-row>
      <v-col cols="12">
        <v-card class="management-card">
          <v-card-title class="management-title">
            Gestão de Clientes
            <v-spacer></v-spacer>
            <v-btn 
              v-if="clientePermissions.canCreate"
              class="management-new-btn" 
              @click="openForm()"
              :disabled="clienteStore.loading"
            >
              <v-icon left>mdi-plus</v-icon>
              Novo Cliente
            </v-btn>
          </v-card-title>
          <ClienteFilters />
          <v-data-table
            :headers="headers"
            :items="clienteStore.clientes"
            :loading="clienteStore.loading"
            :items-per-page="clienteStore.pagination.per_page"
            :page.sync="clienteStore.pagination.current_page"
            :server-items-length="clienteStore.pagination.total"
            class="management-data-table"
          >
            <template #item.tipo_perfil="{ item }">
              <v-chip 
                v-if="item.tipo_perfil"
                size="small"
                variant="outlined"
                color="default"
              >
                {{ item.tipo_perfil }}
              </v-chip>
              <span v-else>-</span>
            </template>
            <template #item.cliente_referencia="{ item }">
              <v-chip 
                size="small"
                variant="outlined"
                :color="item.cliente_referencia ? 'success' : 'default'"
              >
                {{ item.cliente_referencia ? 'Sim' : 'Não' }}
              </v-chip>
            </template>
            <template #item.classificacao="{ item }">
              <v-chip 
                v-if="item.classificacao"
                size="small"
                variant="outlined"
                :color="getClassificacaoColor(item.classificacao)"
              >
                {{ item.classificacao }}
              </v-chip>
              <span v-else>-</span>
            </template>
            <template #item.status="{ item }">
              <StatusBadge :status="item.status" />
            </template>
            <template #item.actions="{ item }">
              <v-btn 
                size="small" 
                class="management-action-btn view-btn" 
                @click="viewCliente(item)"
                :disabled="clienteStore.loading || !clientePermissions.canView"
              >
                Visualizar
              </v-btn>
              <v-btn 
                v-if="clientePermissions.canDelete"
                size="small" 
                class="management-action-btn delete-btn" 
                @click="deleteCliente(item.id)"
                :disabled="clienteStore.loading"
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
import { useClienteStore } from '@/modules/csi/stores/cliente';
import { usePermissions } from '@/composables/usePermissions';
import StatusBadge from '@/components/common/StatusBadge.vue';
import ClienteFilters from '@/components/common/ClienteFilters.vue';

const router = useRouter();
const clienteStore = useClienteStore();
const { clientePermissions } = usePermissions();

const headers = [
  { title: 'Código', key: 'id', sortable: true },
  { title: 'Razão Social', key: 'razao_social', sortable: true },
  { title: 'Nome Fantasia', key: 'nome_fantasia', sortable: true },
  { title: 'CNPJ/CPF', key: 'cnpj_cpf', sortable: true },
  { title: 'Tipo Perfil', key: 'tipo_perfil', sortable: true },
  { title: 'Cliente Ref.', key: 'cliente_referencia', sortable: true },
  { title: 'Classificação', key: 'classificacao', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Ações', key: 'actions', sortable: false },
];

function openForm() {
  router.push('/csi/clientes/novo');
}

function getClassificacaoColor(classificacao: string) {
  switch (classificacao) {
    case 'Promotor':
      return 'success';
    case 'Neutro':
      return 'default';
    case 'Detrator':
      return 'error';
    default:
      return 'default';
  }
}

function viewCliente(item: any) {
  const clienteId = item.id;
  
  if (!clienteId) {
    console.error('ID do cliente não encontrado:', item);
    alert('Erro: ID do cliente não encontrado');
    return;
  }
  
  // Se tem permissão de editar, vai direto para edição
  // Se só tem permissão de visualizar, vai para visualização
  if (clientePermissions.value.canEdit) {
    router.push(`/csi/clientes/editar/${clienteId}`);
  } else {
    router.push(`/csi/clientes/visualizar/${clienteId}`);
  }
}

async function deleteCliente(id: number | undefined) {
  if (!id) {
    console.error('ID do cliente inválido para exclusão');
    alert('Erro: ID do cliente inválido');
    return;
  }
  
  if (confirm('Deseja realmente excluir este cliente?')) {
    await clienteStore.deleteCliente(id);
  }
}

onMounted(async () => {
  await clienteStore.fetchClientes();
});
</script>
