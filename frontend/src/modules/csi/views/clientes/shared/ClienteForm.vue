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
            
            <!-- Botão Salvar no canto superior direito (apenas quando pode editar) -->
            <v-btn
              v-if="canEdit"
              color="primary"
              @click="saveCliente"
              :loading="clienteStore.loading"
            >
              <v-icon left>mdi-content-save</v-icon>
              Salvar
            </v-btn>
          </v-card-title>
          
          <v-card-text class="management-form-container">
            <v-form ref="formRef" @submit.prevent="saveCliente" class="management-form">
              <!-- Dados Básicos -->
              <v-row>
                <v-col cols="12">
                  <h3 class="section-title">Dados Básicos</h3>
                </v-col>

                <v-col cols="12" class="tenant-selection" v-show="showTenantField">
                  <v-autocomplete
                    class="management-form-field"
                    v-model="form.tenant_id"
                    :items="tenantStore.tenants"
                    item-title="nome"
                    item-value="id"
                    label="Tenant *"
                    required
                    :rules="canEdit ? [rules.required] : []"
                    :loading="tenantStore.loading"
                    :readonly="isEdit || !canEdit"
                    :disabled="isEdit || !canEdit"
                    prepend-icon="mdi-domain"
                    :hint="isEdit ? 'O tenant não pode ser alterado durante a edição' : 'Selecione o tenant ao qual este cliente pertence'"
                    persistent-hint
                  >
                    <template v-slot:item="{ props, item }">
                      <v-list-item v-bind="props" :title="item.raw.nome" :subtitle="item.raw.email">
                        <template v-slot:prepend>
                          <v-avatar color="primary" size="small">
                            <span class="text-white">{{ item.raw.nome.charAt(0).toUpperCase() }}</span>
                          </v-avatar>
                        </template>
                      </v-list-item>
                    </template>
                  </v-autocomplete>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.razao_social" 
                    label="Razão Social *" 
                    required
                    :rules="canEdit ? [rules.required] : []"
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.nome_fantasia" 
                    label="Nome Fantasia" 
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.cnpj_cpf" 
                    label="CNPJ/CPF *" 
                    required
                    :rules="canEdit ? [rules.required] : []"
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.codigo_erp" 
                    label="Código ERP" 
                    :readonly="isEdit"
                    hint="Campo preenchido automaticamente pelo sistema"
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.codigo_senior" 
                    label="Código Senior" 
                    :readonly="isEdit"
                    hint="Campo preenchido automaticamente pelo sistema"
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.codigo_ramo" 
                    label="Código Ramo" 
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <StatusSwitch
                    v-model="activeStatus"
                    label="Status do Cliente"
                    :disabled="!canEdit"
                  />
                </v-col>
              </v-row>

              <!-- Dados de Contato -->
              <v-row>
                <v-col cols="12">
                  <h3 class="section-title">Dados de Contato</h3>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.email" 
                    label="Email" 
                    type="email"
                    :rules="[rules.email]"
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.telefone" 
                    label="Telefone" 
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.celular" 
                    label="Celular" 
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
              </v-row>

              <!-- Endereço -->
              <v-row>
                <v-col cols="12">
                  <h3 class="section-title">Endereço</h3>
                </v-col>
                <v-col cols="12" md="8">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.endereco" 
                    label="Endereço" 
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="2">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.numero" 
                    label="Número" 
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="2">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.cep" 
                    label="CEP" 
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.complemento" 
                    label="Complemento" 
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.bairro" 
                    label="Bairro" 
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="8">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.cidade" 
                    label="Cidade" 
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.estado" 
                    label="UF" 
                    :rules="[rules.uf]"
                    maxlength="2"
                    :disabled="!canEdit"
                  ></v-text-field>
                </v-col>
              </v-row>

              <!-- Informações Adicionais -->
              <v-row>
                <v-col cols="12">
                  <h3 class="section-title">Informações Adicionais</h3>
                </v-col>

                <v-col cols="12" md="4">
                  <v-switch
                    class="management-form-field"
                    v-model="form.cliente_referencia"
                    :color="form.cliente_referencia ? 'primary' : undefined"
                    :class="{ 'is-switch-active': form.cliente_referencia }"
                    label="Cliente Referência"
                    hide-details
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <v-select
                    class="management-form-field"
                    v-model="form.classificacao"
                    :items="classificacaoOptions"
                    item-title="text"
                    item-value="value"
                    label="Classificação"
                    variant="outlined"
                    clearable
                    :disabled="!canEdit"
                  ></v-select>
                </v-col>
                <v-col cols="12" md="4">
                  <v-select
                    class="management-form-field"
                    v-model="form.tipo_perfil"
                    :items="tipoPerfilOptions"
                    item-title="text"
                    item-value="value"
                    label="Tipo do Perfil"
                    variant="outlined"
                    clearable
                  ></v-select>
                </v-col>
              </v-row>

              <!-- Contatos -->
              <v-row>
                <v-col cols="12">
                  <h3 class="section-title">Contatos</h3>
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="12" md="6">
                  <v-btn 
                    class="management-form-field"
                    @click="openContatosModal"
                    :disabled="!form.id || !authStore.canAccessContatos()"
                  >
                    <v-icon left>mdi-account-multiple</v-icon>
                    Gerenciar Contatos
                  </v-btn>
                  <div v-if="!form.id" class="text-caption text-grey mt-1">
                    Salve o cliente primeiro para gerenciar contatos
                  </div>
                  <div v-if="form.id && !authStore.canAccessContatos()" class="text-caption text-grey mt-1">
                    Você não tem permissão para gerenciar contatos
                  </div>
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
                  ></v-textarea>
                </v-col>
              </v-row>
              
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>

  <!-- Modal de Contatos -->
  <ContatosModal
    v-model="showContatosModal"
    :cliente-id="form.id || 0"
    :tenant-id="form.tenant_id"
  />
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useClienteStore } from '@/modules/csi/stores/cliente';
import { useTenantStore } from '@/modules/plataforma/stores/tenant';
import { useAuthStore } from '@/modules/plataforma/stores/auth';
import { usePermissions } from '@/composables/usePermissions';
import StatusSwitch from '@/components/common/StatusSwitch.vue';
import ContatosModal from '@/components/modals/ContatosModal.vue';
import type { Cliente } from '@/types/cliente';

const route = useRoute();
const router = useRouter();
const clienteStore = useClienteStore();
const tenantStore = useTenantStore();
const authStore = useAuthStore();
const { clientePermissions } = usePermissions();
const formRef = ref();
const showContatosModal = ref(false);

const form = ref<Cliente>({
  id: undefined,
  tenant_id: undefined,
  razao_social: '',
  nome_fantasia: '',
  codigo_ramo: '',
  cidade: '',
  estado: '',
  cnpj_cpf: '',
  codigo_senior: '',
  status: true,
  cliente_referencia: false,
  tipo_perfil: undefined,
  classificacao: undefined,
  email: '',
  telefone: '',
  celular: '',
  endereco: '',
  numero: '',
  complemento: '',
  bairro: '',
  cep: '',
  observacoes: ''
});

const isEdit = computed(() => !!route.params.id);
const isViewMode = computed(() => route.name === 'cliente-visualizar');
const canEdit = computed(() => {
  if (isViewMode.value) {
    return false; // Em modo visualização, nunca pode editar
  }
  
  if (!isEdit.value) {
    // Criando novo cliente - verificar permissão de criar
    return clientePermissions.value.canCreate;
  }
  
  // Editando cliente existente - verificar permissão de editar
  return clientePermissions.value.canEdit;
});
const formTitle = computed(() => {
  if (isViewMode.value) return 'Visualizar Cliente';
  return isEdit.value ? 'Editar Cliente' : 'Novo Cliente';
});

// Verifica se o usuário atual tem perfil HUB
const isHubUser = computed(() => {
  const user = authStore.getUser;
  if (!user) return false;
  
  // Verifica se o usuário tem a role 'HUB'
  return user.roles && user.roles.includes('HUB');
});

// Controla a exibição do campo Tenant
const showTenantField = computed(() => isHubUser.value);

// Computed para garantir que active sempre seja boolean
const activeStatus = computed({
  get: () => form.value.status ?? true,
  set: (value: boolean) => {
    form.value.status = value;
  }
});

const rules = {
  required: (value: string | number) => (!!value || value === 0) || 'Campo obrigatório',
  email: (value: string) => !value || /.+@.+\..+/.test(value) || 'Email deve ser válido',
  uf: (value: string) => !value || value.length === 2 || 'UF deve ter 2 caracteres'
};

// Opções para tipo de perfil
const tipoPerfilOptions = [
  { text: 'Relacional', value: 'Relacional' },
  { text: 'Transacional', value: 'Transacional' }
];

// Opções para classificação
const classificacaoOptions = [
  { text: 'Promotor', value: 'Promotor' },
  { text: 'Neutro', value: 'Neutro' },
  { text: 'Detrator', value: 'Detrator' }
];

async function loadCliente() {
  if (isEdit.value && route.params.id) {
    const clienteId = Number(route.params.id);
    try {
      await clienteStore.getClienteById(clienteId);
      if (clienteStore.selectedCliente) {
        const cliente = clienteStore.selectedCliente;
        form.value = {
          ...form.value,
          ...cliente,
          id: cliente.id,
          status: cliente.status ?? true, // Garante que sempre seja boolean
        };
      }
    } catch (error) {
      router.push('/csi/clientes');
    }
  }
}

async function saveCliente() {
  const { valid } = await formRef.value.validate();
  
  if (!valid) {
    return;
  }

  // Validação adicional para tenant_id (se necessário)
  // if (!form.value.tenant_id) {
  //   return;
  // }

  try {
    // Remove campos vazios para não enviar nulls desnecessários
    const dataToSend = Object.fromEntries(
      Object.entries(form.value).filter(([_, value]) => value !== '' && value !== null && value !== undefined)
    );

    // Se não é usuário HUB, remove o tenant_id para deixar o backend decidir automaticamente
    if (!isHubUser.value) {
      delete dataToSend.tenant_id;
    }

    if (isEdit.value && form.value.id) {
      await clienteStore.updateCliente(form.value.id, dataToSend);
    } else {
      await clienteStore.createCliente(dataToSend);
    }
    
    if (!clienteStore.error) {
      // Recarrega a lista de clientes para mostrar dados atualizados
      await clienteStore.fetchClientes();
      router.push('/csi/clientes');
    }
  } catch (error) {
    console.error('Erro ao salvar cliente:', error);
  }
}

function goBack() {
  router.push('/csi/clientes');
}

function openContatosModal() {
  if (form.value.id) {
    // Verifica se o usuário tem permissão para acessar contatos
    if (!authStore.canAccessContatos()) {
      console.warn('Usuário não tem permissão para acessar contatos');
      return;
    }
    showContatosModal.value = true;
  }
}

// Watch para reagir quando o usuário for carregado
watch(() => authStore.getUser, (newUser) => {
  if (newUser && newUser.roles && newUser.permissions) {
    // Usuário carregado com roles e permissions
  }
}, { immediate: true });

onMounted(async () => {
  // Se não há usuário mas está autenticado, forçar verificação
  if (authStore.isAuthenticated && !authStore.getUser) {
    await authStore.checkAuth();
  }
  
  // Carrega a lista de tenants
  await tenantStore.fetchTenants();
  
  // Se não é usuário HUB e não é edição, define o tenant_id automaticamente
  if (!isHubUser.value && !isEdit.value) {
    const user = authStore.getUser;
    if (user && (user as any).tenant_id) {
      form.value.tenant_id = (user as any).tenant_id;
    }
  }
  
  // Carrega o cliente se estiver editando
  await loadCliente();
});

</script>

<style scoped>
/* Estilos específicos do formulário de cliente */
.management-form-container {
  padding: 32px !important;
  background: #fafafa;
}

.management-form {
  background: white;
  padding: 32px;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.section-title {
  color: #333;
  font-weight: 600;
  margin-bottom: 16px;
  padding-bottom: 8px;
  border-bottom: 2px solid #e0e0e0;
}

.tenant-selection {
  background: #f8f9fa;
  border: 1px solid #e3f2fd;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 16px;
}

.tenant-selection .v-field {
  background: white;
}

.tenant-selection .v-field--disabled {
  background: #f5f5f5 !important;
  opacity: 0.7;
}

.tenant-selection .v-field--disabled .v-field__input {
  color: #666 !important;
}


/* Switch: track e thumb — ativo (azul) / inativo (preto) */
/* Coloque isso dentro do <style scoped> do componente */

::v-deep .management-form-field .v-switch__track {
  /* estado padrão - inativo: preto suave (ajuste a opacidade se quiser menos contraste) */
  background-color: rgba(0, 0, 0, 0.08) !important; /* track inativa (sutil) */
  border-color: rgba(0, 0, 0, 0.12) !important;
  transition: background-color 200ms ease, border-color 200ms ease;
}

::v-deep .management-form-field .v-switch__thumb {
  background-color: #fff !important;
  transition: box-shadow 200ms ease, transform 150ms ease;
}

::v-deep .management-form-field.is-switch-active .v-switch__track {
  background-color: rgba(var(--v-theme-primary), 0.90) !important; /* quase sólido */
  border-color: rgba(var(--v-theme-primary), 1) !important;
}

::v-deep .management-form-field.is-switch-active .v-switch__thumb {
  box-shadow: 0 0 0 6px rgba(var(--v-theme-primary), 0.12) !important;
}

::v-deep .management-form-field:not(.is-switch-active) .v-switch__track {
  background-color: #000 !important;
  border-color: #000 !important;
}

::v-deep .management-form-field:not(.is-switch-active) .v-switch__track {
  background-color: rgba(0,0,0,0.24) !important;
  border-color: rgba(0,0,0,0.32) !important;
}

::v-deep .management-form-field:not(.is-switch-active) .v-field__label,
::v-deep .management-form-field:not(.is-switch-active) .v-label,
::v-deep .management-form-field:not(.is-switch-active) .v-switch__label {
  color: #000 !important; /* use #000 ou outro hex como #111 */
}

::v-deep .management-form-field.is-switch-active .v-field__label,
::v-deep .management-form-field.is-switch-active .v-label,
::v-deep .management-form-field.is-switch-active .v-switch__label {
  color: rgb(var(--v-theme-primary)) !important;
}

</style>
