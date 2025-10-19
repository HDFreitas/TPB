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
              <h2 class="text-h5">{{ isEdit ? 'Editar Conector' : 'Novo Conector' }}</h2>
            </div>
            <v-spacer></v-spacer>
            
            <!-- Botão Salvar no canto superior direito -->
            <v-btn
              color="primary"
              @click="saveConector"
              :loading="conectorStore.loading"
            >
              <v-icon left>mdi-content-save</v-icon>
              Salvar
            </v-btn>
          </v-card-title>
          
          <v-card-text class="management-form-container">
            <!-- Exibir mensagem de erro se houver -->
            <v-alert
              v-if="conectorStore.error"
              type="error"
              dismissible
              @click:close="conectorStore.error = null"
              class="mb-4"
            >
              {{ conectorStore.error }}
            </v-alert>
            
            <v-form ref="formRef" @submit.prevent="saveConector" class="management-form">
              <!-- Dados Básicos -->
              <v-row>
                <v-col cols="12">
                  <h3 class="section-title">Dados Básicos</h3>
                </v-col>
                
                <!-- Campo Tenant - apenas para usuários HUB -->
                <v-col v-if="isHubUser" cols="12" md="6">
                  <v-select
                    class="management-form-field"
                    v-model="form.tenant_id"
                    label="Tenant *"
                    :items="tenantOptions"
                    item-title="nome"
                    item-value="id"
                    required
                    :rules="[rules.required]"
                    prepend-icon="mdi-domain"
                    @input="clearError"
                  ></v-select>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-select
                    class="management-form-field"
                    v-model="form.codigo"
                    label="Tipo de Conector *"
                    :items="conectorTypes"
                    item-title="nome"
                    item-value="codigo"
                    required
                    :rules="[rules.required]"
                    prepend-icon="mdi-connection"
                    @update:model-value="onCodigoChange"
                    @input="clearError"
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.nome" 
                    label="Nome *" 
                    required
                    :rules="[rules.required]"
                    prepend-icon="mdi-tag"
                    @input="clearError"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.url" 
                    label="URL" 
                    :rules="[rules.url]"
                    prepend-icon="mdi-link"
                    @input="clearError"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <StatusSwitch
                    v-model="form.status"
                    label="Status do Conector"
                  />
                </v-col>
              </v-row>

              <!-- Configurações Específicas -->
              <v-row v-if="showSpecificFields">
                <v-col cols="12">
                  <h3 class="section-title">Configurações Específicas</h3>
                </v-col>
                
                <!-- Campos para ERP -->
                <template v-if="form.codigo === '1-ERP'">
                  <v-col cols="12" md="6">
                    <v-text-field 
                      class="management-form-field" 
                      v-model="form.usuario" 
                      label="Usuário" 
                      prepend-icon="mdi-account"
                      @input="clearError"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field 
                      class="management-form-field" 
                      v-model="form.senha" 
                      label="Senha" 
                      type="password"
                      prepend-icon="mdi-lock"
                      @input="clearError"
                    ></v-text-field>
                  </v-col>
                </template>

                <!-- Campos para Movidesk e CRM Eleve -->
                <template v-if="form.codigo === '2-Movidesk' || form.codigo === '3-CRM Eleve'">
                  <v-col cols="12">
                    <v-textarea 
                      class="management-form-field" 
                      v-model="form.token" 
                      label="Token" 
                      rows="3"
                      prepend-icon="mdi-key"
                      @input="clearError"
                    ></v-textarea>
                  </v-col>
                </template>
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
                    prepend-icon="mdi-note-text"
                  ></v-textarea>
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
import { useConectorStore } from '@/modules/csi/stores/conector';
import { useTenantStore } from '@/modules/plataforma/stores/tenant';
import { useAuthStore } from '@/modules/plataforma/stores/auth';
import StatusSwitch from '@/components/common/StatusSwitch.vue';
import type { ConectorFormData, ConectorTypeConfig } from '@/types/conector';

const route = useRoute();
const router = useRouter();
const conectorStore = useConectorStore();
const tenantStore = useTenantStore();
const authStore = useAuthStore();

const formRef = ref();
const isEdit = computed(() => !!route.params.id);

// Verificar se o usuário é HUB
const isHubUser = computed(() => {
    const user = authStore.getUser;
    return user?.roles?.includes('HUB') || false;
});

// Opções de tenant para usuários HUB
const tenantOptions = computed(() => {
    if (!isHubUser.value) return [];
    
    return tenantStore.tenants.map(tenant => ({
        id: tenant.id,
        nome: tenant.nome
    }));
});


const conectorTypes: ConectorTypeConfig[] = [
  {
    codigo: '1-ERP',
    nome: 'ERP Senior',
    campos: ['usuario', 'senha'],
    descricao: 'Conector para integração com ERP Senior via SOAP'
  },
  {
    codigo: '2-Movidesk',
    nome: 'Movidesk API',
    campos: ['token'],
    descricao: 'Conector para integração com Movidesk via REST API'
  },
  {
    codigo: '3-CRM Eleve',
    nome: 'CRM Eleve',
    campos: ['token'],
    descricao: 'Conector para integração com CRM Eleve'
  }
];

const form = ref<ConectorFormData>({
  tenant_id: 1, // Será definido baseado no perfil do usuário
  codigo: '',
  nome: '',
  url: '',
  status: true,
  usuario: '',
  senha: '',
  token: '',
  observacoes: ''
});

const showSpecificFields = computed(() => {
  return form.value.codigo && form.value.codigo !== '';
});

const rules = {
  required: (value: any) => !!value || 'Campo obrigatório',
  url: (value: string) => {
    if (!value) return true;
    const urlPattern = /^https?:\/\/.+/;
    return urlPattern.test(value) || 'URL inválida';
  }
};

function onCodigoChange(codigo: string) {
  // Limpar campos específicos quando mudar o tipo
  form.value.usuario = '';
  form.value.senha = '';
  form.value.token = '';
  
  // Definir nome padrão baseado no código
  const tipo = conectorTypes.find(t => t.codigo === codigo);
  if (tipo) {
    form.value.nome = tipo.nome;
  }
}

async function saveConector() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  try {
    if (isEdit.value) {
      await conectorStore.updateConector(Number(route.params.id), form.value);
      
      // Aplicar filtros se estiverem preenchidos
      const hasFilters = Object.values(conectorStore.filters).some(value => 
        value !== null && value !== undefined && value !== ''
      );
      
      if (hasFilters) {
        // Chama diretamente searchConectores com os filtros atuais
        const cleanFilters = Object.fromEntries(
          Object.entries(conectorStore.filters).filter(([_, value]) =>
            value !== null && value !== undefined && value !== ''
          )
        );
        await conectorStore.searchConectores(cleanFilters);
      } else {
        await conectorStore.fetchConectores();
      }
    }
    
    if (!conectorStore.error) {
      router.push('/csi/conectores');
    }
  } catch (error) {
    console.error('Erro ao salvar conector:', error);
  }
}

function goBack() {
  router.push('/csi/conectores');
}

function clearError() {
  conectorStore.error = null;
}

async function loadConector() {
  if (isEdit.value) {
    const id = Number(route.params.id);
    await conectorStore.getConectorById(id);
    
    if (conectorStore.selectedConector) {
      form.value = {
        tenant_id: conectorStore.selectedConector.tenant_id,
        codigo: conectorStore.selectedConector.codigo,
        nome: conectorStore.selectedConector.nome,
        url: conectorStore.selectedConector.url || '',
        status: conectorStore.selectedConector.status,
        usuario: conectorStore.selectedConector.usuario || '',
        senha: conectorStore.selectedConector.senha || '',
        token: conectorStore.selectedConector.token || '',
        observacoes: conectorStore.selectedConector.observacoes || ''
      };
    }
  }
}

onMounted(async () => {
  // Carregar tenants se o usuário for HUB
  if (isHubUser.value) {
    await tenantStore.fetchTenants();
  }
  
  // Definir tenant_id baseado no perfil do usuário
  const user = authStore.getUser;
  if (user && !isHubUser.value) {
    // Para usuários não-HUB, usar o tenant_id do usuário logado
    // Como o user não tem tenant_id diretamente, vamos usar um valor padrão
    // TODO: Implementar lógica para obter tenant_id do usuário logado
    form.value.tenant_id = 1; // Valor temporário
  }
  
  loadConector();
});
</script>

<style scoped>
/* Estilos específicos da view de Conectores (se necessário) */
/* Os estilos globais estão em src/styles/pages/_management-views.scss */
</style>
