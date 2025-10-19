<template>
  <v-container fluid class="management-container">
    <v-row>
      <v-col cols="12">
        <v-card class="management-card">
          <v-card-title class="management-title">
            <v-btn
              icon
              @click="goBack"
              class="mr-3"
            >
              <v-icon>mdi-arrow-left</v-icon>
            </v-btn>
            <div>
              <h2 class="text-h5">{{ formTitle }}</h2>
            </div>
            <v-spacer></v-spacer>
            
            <!-- Botão Importar (apenas quando tem conector e permissão) -->
            <v-btn
              v-if="isEdit && form.conector_id && canImport"
              color="success"
              variant="outlined"
              @click="importFromConector"
              :loading="tipoInteracaoStore.loading"
              class="mr-2"
              style="background: #fff; color: #43a047; border-color: #43a047;"
            >
              <v-icon left style="color: #43a047;">mdi-download</v-icon>
              <span style="color: #43a047;">Importar</span>
            </v-btn>
            
            <!-- Botão Salvar no canto superior direito (apenas quando pode editar) -->
            <v-btn
              v-if="canEdit"
              color="primary"
              @click="saveTipoInteracao"
              :loading="tipoInteracaoStore.loading"
              :disabled="!isFormValid"
            >
              <v-icon left>mdi-content-save</v-icon>
              Salvar
            </v-btn>
          </v-card-title>
          
          <v-card-text class="management-form-container">
            <!-- Exibir mensagem de erro se houver -->
            <v-alert
              v-if="tipoInteracaoStore.error"
              type="error"
              dismissible
              @click:close="tipoInteracaoStore.clearError()"
              class="mb-4"
            >
              {{ tipoInteracaoStore.error }}
            </v-alert>
            
            <v-form ref="formRef" v-model="isFormValid" @submit.prevent="saveTipoInteracao" class="management-form">
              <!-- Dados Básicos -->
              <v-row>
                <v-col cols="12">
                  <h3 class="section-title">Dados Básicos</h3>
                </v-col>
                
                

                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.nome" 
                    label="Nome *" 
                    required
                    :rules="canEdit ? [rules.required, rules.minLength(2), rules.maxLength(255)] : []"
                    :disabled="!canEdit"
                    prepend-icon="mdi-message-text"
                    @input="clearError"
                    :error-messages="formRef?.errors?.nome"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-select
                    class="management-form-field"
                    v-model="form.conector_id"
                    label="Conector"
                    :items="conectorOptions"
                    item-title="nome"
                    item-value="id"
                    clearable
                    :disabled="!canEdit"
                    prepend-icon="mdi-connection"
                    @update:model-value="onConectorChange"
                    @input="clearError"
                    :error-messages="formRef?.errors?.conector_id"
                  />
                </v-col>
                
                <!-- Campo Porta para ERP -->
                <v-col v-if="isErpConector" cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.porta" 
                    label="Porta (host:port ou host)" 
                    type="text"
                    :rules="canEdit && isErpConector ? [rules.required] : []"
                    :disabled="!canEdit"
                    prepend-icon="mdi-ethernet"
                    @input="clearError"
                  />
                </v-col>

                <!-- Campo Rota para Movidesk -->
                <v-col v-if="isMovideskConector" cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.rota" 
                    label="Rota" 
                    :rules="canEdit && isMovideskConector ? [rules.required, rules.maxLength(255)] : []"
                    :disabled="!canEdit"
                    prepend-icon="mdi-routes"
                    placeholder="/api/v1/tickets"
                    @input="clearError"
                  />
                </v-col>
                
                <v-col cols="12" md="6">
                  <StatusSwitch
                    v-model="form.status"
                    label="Status do Tipo de Interação"
                    :disabled="!canEdit"
                  />
                </v-col>
              </v-row>


              <!-- Observações -->
              <v-row>
                <v-col cols="12">
                  <h3 class="section-title">Observações</h3>
                </v-col>
                <v-col cols="12">
                  <v-textarea 
                    class="management-form-field" 
                    v-model="form.observacoes" 
                    label="Observações" 
                    rows="3"
                    :disabled="!canEdit"
                    prepend-icon="mdi-note-text"
                  />
                </v-col>
              </v-row>
              
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useTipoInteracaoStore } from '@/modules/csi/stores/tipoInteracao';
import { useConectorStore } from '@/modules/csi/stores/conector';
import { useAuthStore } from '@/modules/plataforma/stores/auth';
import { usePermissions } from '@/composables/usePermissions';
import StatusSwitch from '@/components/common/StatusSwitch.vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import type { TipoInteracaoFormData } from '@/types/tipoInteracao';

const route = useRoute();
const router = useRouter();
const tipoInteracaoStore = useTipoInteracaoStore();
const conectorStore = useConectorStore();
const authStore = useAuthStore();
const { tipoInteracaoPermissions } = usePermissions();

const formRef = ref();
const isFormValid = ref(false);
const isEdit = computed(() => !!route.params.id);
const isViewMode = computed(() => route.name === 'VisualizarTipoInteracao');
const canEdit = computed(() => {
  if (isViewMode.value) {
    return false; // Em modo visualização, nunca pode editar
  }
  
  if (!isEdit.value) {
    // Criando novo tipo - verificar permissão de criar
    return tipoInteracaoPermissions.value.canCreate;
  }
  
  // Editando tipo existente - verificar permissão de editar
  return tipoInteracaoPermissions.value.canEdit;
});

const canImport = computed(() => {
  return authStore.hasPermission('tipos_interacao.importar');
});


// Opções de conectores ativos
const conectorOptions = computed(() => {
  return conectorStore.conectores
    .filter(conector => conector.status === true)
    .map(conector => ({
      id: conector.id,
      nome: conector.nome,
      codigo: conector.codigo
    }));
});

const form = ref<TipoInteracaoFormData>({
  tenant_id: 1, // Será definido baseado no perfil do usuário
  nome: '',
  conector_id: undefined,
  porta: undefined,
  rota: '',
  status: true,
  observacoes: ''
});

// Computed para campos específicos do conector
const selectedConector = computed(() => {
  if (!form.value.conector_id) return null;
  return conectorStore.conectores.find(c => c.id === form.value.conector_id);
});

const isErpConector = computed(() => {
  return selectedConector.value?.codigo === '1-ERP';
});

const isMovideskConector = computed(() => {
  return selectedConector.value?.codigo === '2-Movidesk';
});

const formTitle = computed(() => {
  if (isViewMode.value) return 'Visualizar Tipo de Interação';
  return isEdit.value ? 'Editar Tipo de Interação' : 'Novo Tipo de Interação';
});

const rules = {
  required: (value: any) => !!value || 'Campo obrigatório',
  minLength: (min: number) => (value: string) => 
    (value && value.length >= min) || `Deve ter pelo menos ${min} caracteres`,
  maxLength: (max: number) => (value: string) => 
    (!value || value.length <= max) || `Deve ter no máximo ${max} caracteres`,
  portRange: (value: number) => {
    if (!value) return 'Porta é obrigatória';
    return true;
  }
};

function onConectorChange(conectorId: number | undefined) {
  // Atualiza o campo do formulário
  form.value.conector_id = conectorId;
  // Limpa campos específicos quando mudar o conector
  form.value.porta = undefined;
  form.value.rota = '';
}

async function saveTipoInteracao() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  try {
    if (isEdit.value) {
      await tipoInteracaoStore.updateTipoInteracao(Number(route.params.id), form.value);
    } else {
      await tipoInteracaoStore.createTipoInteracao(form.value);
    }
    
    if (!tipoInteracaoStore.error) {
      router.push('/csi/tipos-interacao');
    }
  } catch (error) {
    showToast('Erro ao salvar tipo de interação', 'error');
  }
}

function goBack() {
  router.push('/csi/tipos-interacao');
}

function clearError() {
  tipoInteracaoStore.clearError();
}

  const showToast = (message: string, type: 'success' | 'error' | 'warning' | 'info') => {
  
  toast(message, {
    type,
    position: 'top-right',
    autoClose: 5000,
    hideProgressBar: false,
    closeOnClick: true,
    pauseOnHover: true,
    icon: false
  });
};

async function importFromConector() {
  
  // Validações
  if (!isEdit.value || !route.params.id || !form.value.conector_id) {
    showToast('Tipo de interação deve ter um conector configurado para importação.', 'error');
    return;
  }
  
  // Executa importação
  const result = await tipoInteracaoStore.importFromConector(Number(route.params.id));
  
  // Verifica erro na store
  if (tipoInteracaoStore.error) {
    showToast(tipoInteracaoStore.error, 'error');
    return;
  }
  
  // Valida retorno
  if (!result) {
    showToast('Nenhum dado retornado pela importação.', 'warning');
    return;
  }
  
  // Extrai dados
  const { sucesso = 0, erro = 0 } = result;
  const total = sucesso + erro;
  
  // Nenhum registro processado
  if (total === 0) {
    console.log('ℹ️ Nenhum registro'); // DEBUG
    showToast('Nenhum registro encontrado para importação.', 'info');
    return;
  }
  
  // Monta mensagem e tipo baseado no resultado
  if (erro === 0) {
    showToast(`✓ ${sucesso} registro(s) importado(s) com sucesso!`, 'success');
  } else if (sucesso > 0) {
    showToast(`⚠ Parcial: ${sucesso} sucesso, ${erro} erro(s)`, 'warning');
  } else {
    showToast(`✗ Falha: ${erro} erro(s)`, 'error');
  }
}

async function loadTipoInteracao() {
  if (isEdit.value) {
    const id = Number(route.params.id);
    const tipoInteracao = await tipoInteracaoStore.getTipoInteracaoById(id);
    
    if (tipoInteracao) {
      form.value = {
        tenant_id: tipoInteracao.tenant_id,
        nome: tipoInteracao.nome,
        conector_id: tipoInteracao.conector_id,
        porta: tipoInteracao.porta,
        rota: tipoInteracao.rota || '',
        status: tipoInteracao.status,
        observacoes: tipoInteracao.observacoes || ''
      };
    }
  }
}

onMounted(async () => {
  // Carregar conectores
  await conectorStore.fetchConectores();
  
  // Definir tenant_id baseado no usuário logado
  const user = authStore.getUser;
  if (user) {
    form.value.tenant_id = user.tenant_id || 1;
  }
  
  await loadTipoInteracao();
});
</script>

<style scoped>
/* Estilos específicos da view de Tipos de Interação (se necessário) */
/* Os estilos globais estão em src/styles/pages/_management-views.scss */
</style>
