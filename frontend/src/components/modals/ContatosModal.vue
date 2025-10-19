<template>
  <v-dialog v-model="isOpen" max-width="1200px" persistent>
    <v-card class="management-card">
      <v-card-title class="management-title">
        Contatos do Cliente
        <v-spacer></v-spacer>
        <v-btn 
          class="management-new-btn" 
          @click="openCreateModal"
          :disabled="!canCreate"
        >
          <v-icon left>mdi-plus</v-icon>
          Novo Contato
        </v-btn>
        <v-btn 
          class="ml-2" 
          color="secondary" 
          @click="closeModal"
        >
          <v-icon left>mdi-close</v-icon>
          Fechar
        </v-btn>
      </v-card-title>

      <v-card-text>
        <!-- Filtros -->
        <ContatoFilters
          :filters="contatoStore.filters"
          :filters-expanded="contatoStore.filtersExpanded"
          @update:filters="updateFilters"
          @apply="applyFilters"
          @clear="clearFilters"
        />

        <!-- Tabela de Contatos -->
        <v-data-table
          :headers="headers"
          :items="contatoStore.contatos"
          :loading="contatoStore.loading"
          :items-per-page="contatoStore.pagination.per_page"
          :page.sync="contatoStore.pagination.current_page"
          :server-items-length="contatoStore.pagination.total"
          class="management-data-table"
          @click:row="editContato"
        >
          <template #item.promotor="{ item }">
            <v-chip 
              :color="item.promotor ? 'success' : 'default'" 
              size="small"
            >
              {{ item.promotor ? 'Sim' : 'Não' }}
            </v-chip>
          </template>

          <template #item.tipo_perfil="{ item }">
            <v-chip 
              :color="item.tipo_perfil === 'Relacional' ? 'primary' : 'secondary'" 
              size="small"
              v-if="item.tipo_perfil"
            >
              {{ item.tipo_perfil }}
            </v-chip>
          </template>

          <template #item.actions="{ item }">
            <v-btn 
              size="small"
              class="management-action-btn edit-btn"
              @click.stop="editContato(item)"
              :disabled="!canEdit"
            >
              Editar
            </v-btn>
            <v-btn 
              variant="outlined"
              class="management-action-btn delete-btn"
              size="small"
              @click.stop="deleteContato(item.id)"
              :disabled="!canDelete"
            >
              Excluir
            </v-btn>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <!-- Modal de Criação/Edição -->
    <ContatoFormModal
      v-model="showFormModal"
      :contato="selectedContato"
      :cliente-id="clienteId"
      :tenant-id="tenantId"
      @saved="onContatoSaved"
      @closed="onFormModalClosed"
    />
  </v-dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useContatoStore } from '@/modules/csi/stores/contato';
import { useAuthStore } from '@/modules/plataforma/stores/auth';
import ContatoFilters from '@/components/common/ContatoFilters.vue';
import ContatoFormModal from './ContatoFormModal.vue';
import { Contato } from '@/types/contato';

interface Props {
  modelValue: boolean;
  clienteId: number;
  tenantId?: number;
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const contatoStore = useContatoStore();
const authStore = useAuthStore();

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const showFormModal = ref(false);
const selectedContato = ref<Contato | null>(null);

// Verifica permissões
const canCreate = computed(() => authStore.hasPermission('contatos.criar'));
const canEdit = computed(() => authStore.hasPermission('contatos.editar'));
const canDelete = computed(() => authStore.hasPermission('contatos.excluir'));
const canAccess = computed(() => authStore.canAccessContatos());

const headers = [
  { text: 'Nome', value: 'nome' },
  { text: 'Código', value: 'codigo' },
  { text: 'Email', value: 'email' },
  { text: 'Cargo', value: 'cargo' },
  { text: 'Telefone', value: 'telefone' },
  { text: 'Tipo Perfil', value: 'tipo_perfil' },
  { text: 'Promotor', value: 'promotor' },
  { text: 'Ações', value: 'actions', sortable: false },
];

function openCreateModal() {
  selectedContato.value = null;
  showFormModal.value = true;
}

function editContato(contato: Contato) {
  selectedContato.value = contato;
  showFormModal.value = true;
}

async function deleteContato(id: number | undefined) {
  if (!id) return;
  
  if (confirm('Deseja realmente excluir este contato?')) {
    try {
      await contatoStore.deleteContato(id);
      // Recarrega a lista
      await loadContatos();
    } catch (error) {
      console.error('Erro ao excluir contato:', error);
    }
  }
}

function closeModal() {
  isOpen.value = false;
}

function updateFilters(filters: any) {
  contatoStore.filters = { ...filters };
}

function applyFilters() {
  // Sempre incluir filtros de segurança (cliente_id e tenant_id)
  const securityFilters = {
    cliente_id: props.clienteId,
    tenant_id: props.tenantId
  };
  
  // Combinar filtros de segurança com filtros do usuário
  const allFilters = { ...contatoStore.filters, ...securityFilters };
  
  // Remover filtros vazios
  const cleanFilters = Object.fromEntries(
    Object.entries(allFilters).filter(([_, value]) =>
      value !== null && value !== undefined && value !== ''
    )
  );
  
  contatoStore.searchContatos(cleanFilters);
}

function clearFilters() {
  contatoStore.clearFilters();
}

async function loadContatos() {
  // Sempre filtrar por cliente_id e tenant_id para segurança
  const filters = {
    cliente_id: props.clienteId,
    tenant_id: props.tenantId
  };
  await contatoStore.searchContatos(filters);
}

function onContatoSaved() {
  loadContatos();
}

function onFormModalClosed() {
  selectedContato.value = null;
}

// Carrega contatos quando o modal abre
watch(isOpen, (newValue) => {
  if (newValue) {
    if (!canAccess.value) {
      console.warn('Usuário não tem permissão para acessar contatos');
      closeModal();
      return;
    }
    loadContatos();
  }
});
</script>

<style scoped>
.management-card {
  max-height: 90vh;
  overflow-y: auto;
}

.management-title {
  background: white;
  color: #333;
  padding: 16px 24px;
  font-weight: 600;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.management-new-btn {
  background: white;
  color: #333;
  font-weight: 600;
  border: 1px solid #e0e0e0;
  text-transform: none;
  letter-spacing: normal;
}

.management-data-table {
  margin-top: 16px;
}
</style>
