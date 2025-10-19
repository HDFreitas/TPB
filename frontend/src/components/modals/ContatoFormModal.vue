<template>
  <v-dialog v-model="isOpen" max-width="800px" persistent>
    <v-card class="management-card">
      <v-card-title class="management-title">
        {{ isEdit ? 'Editar Contato' : 'Novo Contato' }}
        <v-spacer></v-spacer>
        <v-btn 
          icon="mdi-close" 
          variant="text" 
          @click="closeModal"
        ></v-btn>
      </v-card-title>

      <v-card-text class="management-form-container">
        <v-form ref="formRef" @submit.prevent="saveContato" class="management-form">
          <!-- Dados Básicos -->
          <v-row>
            <v-col cols="12">
              <h3 class="section-title">Dados Básicos</h3>
            </v-col>

            <!-- Campo Tenant (apenas para usuários HUB) -->
            <v-col cols="12" v-if="showTenantField">
              <v-autocomplete
                class="management-form-field"
                v-model="form.tenant_id"
                :items="tenantStore.tenants"
                item-title="nome"
                item-value="id"
                label="Tenant *"
                required
                :rules="[rules.required]"
                :loading="tenantStore.loading"
                :readonly="isEdit"
                :disabled="isEdit"
                prepend-icon="mdi-domain"
                :hint="isEdit ? 'O tenant não pode ser alterado durante a edição' : 'Selecione o tenant ao qual este contato pertence'"
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

            <!-- Campo Cliente (apenas para usuários HUB) -->
            <v-col cols="12" v-if="showClienteField">
              <v-autocomplete
                class="management-form-field"
                v-model="form.cliente_id"
                :items="clientes"
                item-title="razao_social"
                item-value="id"
                label="Cliente *"
                required
                :rules="[rules.required]"
                :loading="loadingClientes"
                :readonly="isEdit"
                :disabled="isEdit"
                prepend-icon="mdi-account"
                :hint="isEdit ? 'O cliente não pode ser alterado durante a edição' : 'Selecione o cliente ao qual este contato pertence'"
                persistent-hint
              >
                <template v-slot:item="{ props, item }">
                  <v-list-item v-bind="props" :title="(item.raw as any).razao_social" :subtitle="(item.raw as any).nome_fantasia">
                    <template v-slot:prepend>
                      <v-avatar color="secondary" size="small">
                        <span class="text-white">{{ (item.raw as any).razao_social.charAt(0).toUpperCase() }}</span>
                      </v-avatar>
                    </template>
                  </v-list-item>
                </template>
              </v-autocomplete>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field 
                class="management-form-field" 
                v-model="form.nome" 
                label="Nome *" 
                required
                :rules="[rules.required]"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field 
                class="management-form-field" 
                v-model="form.codigo" 
                label="Código"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field 
                class="management-form-field" 
                v-model="form.email" 
                label="Email"
                type="email"
                :rules="[rules.email]"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field 
                class="management-form-field" 
                v-model="form.cargo" 
                label="Cargo"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field 
                class="management-form-field" 
                v-model="form.telefone" 
                label="Telefone"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
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

            <v-col cols="12" md="6">
              <v-switch
                class="management-form-field"
                v-model="form.promotor"
                :color="form.promotor ? 'primary' : undefined"
                :class="{ 'is-switch-active': form.promotor }"
                label="Promotor"
                hide-details
              />
            </v-col>
          </v-row>
          
          <v-row class="mt-6">
            <v-col cols="12" class="d-flex justify-space-between">
              <v-btn 
                variant="outlined"
                color="grey"
                @click="closeModal"
              >
                <v-icon left>mdi-arrow-left</v-icon>
                Cancelar
              </v-btn>                
              <v-btn 
                color="primary"
                type="submit"
                :loading="contatoStore.loading"
              >
                <v-icon left>mdi-content-save</v-icon>
                Salvar
              </v-btn>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useContatoStore } from '@/modules/csi/stores/contato';
import { useTenantStore } from '@/modules/plataforma/stores/tenant';
import { useAuthStore } from '@/modules/plataforma/stores/auth';
import { useClienteStore } from '@/modules/csi/stores/cliente';
import { Contato } from '@/types/contato';

interface Props {
  modelValue: boolean;
  contato?: Contato | null;
  clienteId?: number;
  tenantId?: number;
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void;
  (e: 'saved'): void;
  (e: 'closed'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const contatoStore = useContatoStore();
const tenantStore = useTenantStore();
const authStore = useAuthStore();
const clienteStore = useClienteStore();
const formRef = ref();

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const isEdit = computed(() => !!props.contato?.id);

const form = ref<Partial<Contato>>({
  id: undefined,
  tenant_id: undefined,
  cliente_id: props.clienteId || undefined,
  codigo: '',
  nome: '',
  email: '',
  cargo: '',
  telefone: '',
  tipo_perfil: undefined,
  promotor: false
});

const clientes = ref<any[]>([]);
const loadingClientes = ref(false);

// Verifica se o usuário atual tem perfil HUB
const isHubUser = computed(() => {
  return authStore.hasRole('HUB');
});

// Controla a exibição dos campos
const showTenantField = computed(() => isHubUser.value);
const showClienteField = computed(() => isHubUser.value);

const rules = {
  required: (value: string | number) => (!!value || value === 0) || 'Campo obrigatório',
  email: (value: string) => !value || /.+@.+\..+/.test(value) || 'Email deve ser válido'
};

// Opções para tipo de perfil
const tipoPerfilOptions = [
  { text: 'Relacional', value: 'Relacional' },
  { text: 'Transacional', value: 'Transacional' }
];

async function loadClientes() {
  if (!isHubUser.value) return;
  
  loadingClientes.value = true;
  try {
    await clienteStore.fetchClientes();
    clientes.value = clienteStore.clientes;
  } catch (error) {
    console.error('Erro ao carregar clientes:', error);
  } finally {
    loadingClientes.value = false;
  }
}

function loadContato() {
  if (props.contato) {
    form.value = {
      ...form.value,
      ...props.contato,
      id: props.contato.id,
    };
  } else {
    // Reset form for new contato
    form.value = {
      id: undefined,
      tenant_id: props.tenantId || undefined,
      cliente_id: props.clienteId || 0,
      codigo: '',
      nome: '',
      email: '',
      cargo: '',
      telefone: '',
      tipo_perfil: undefined,
      promotor: false
    };
  }
}

async function saveContato() {
  const { valid } = await formRef.value.validate();
  
  if (!valid) {
    return;
  }

  try {
    // Remove campos vazios para não enviar nulls desnecessários
    const dataToSend = Object.fromEntries(
      Object.entries(form.value).filter(([_, value]) => value !== '' && value !== null && value !== undefined)
    );

    // Se não é usuário HUB, remove o tenant_id para deixar o backend decidir automaticamente
    if (!isHubUser.value) {
      delete dataToSend.tenant_id;
      delete dataToSend.cliente_id;
    }

    if (isEdit.value && form.value.id) {
      await contatoStore.updateContato(form.value.id, dataToSend);
    } else {
      await contatoStore.createContato(dataToSend);
    }
    
    if (!contatoStore.error) {
      emit('saved');
      closeModal();
    }
  } catch (error) {
    console.error('Erro ao salvar contato:', error);
  }
}

function closeModal() {
  emit('closed');
  isOpen.value = false;
}

// Carrega dados quando o modal abre
watch(isOpen, (newValue) => {
  if (newValue) {
    loadContato();
    if (isHubUser.value) {
      loadClientes();
    }
  }
});

onMounted(async () => {
  // Se não há usuário mas está autenticado, forçar verificação
  if (authStore.isAuthenticated && !authStore.getUser) {
    await authStore.checkAuth();
  }
  
  // Carrega a lista de tenants
  await tenantStore.fetchTenants();
});
</script>

<style scoped>
/* Estilos específicos do formulário de contato */
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

/* Switch: track e thumb — ativo (azul) / inativo (preto) */
::v-deep .management-form-field .v-switch__track {
  background-color: rgba(0, 0, 0, 0.08) !important;
  border-color: rgba(0, 0, 0, 0.12) !important;
  transition: background-color 200ms ease, border-color 200ms ease;
}

::v-deep .management-form-field .v-switch__thumb {
  background-color: #fff !important;
  transition: box-shadow 200ms ease, transform 150ms ease;
}

::v-deep .management-form-field.is-switch-active .v-switch__track {
  background-color: rgba(var(--v-theme-primary), 0.90) !important;
  border-color: rgba(var(--v-theme-primary), 1) !important;
}

::v-deep .management-form-field.is-switch-active .v-switch__thumb {
  box-shadow: 0 0 0 6px rgba(var(--v-theme-primary), 0.12) !important;
}

::v-deep .management-form-field:not(.is-switch-active) .v-switch__track {
  background-color: rgba(0,0,0,0.24) !important;
  border-color: rgba(0,0,0,0.32) !important;
}

::v-deep .management-form-field:not(.is-switch-active) .v-field__label,
::v-deep .management-form-field:not(.is-switch-active) .v-label,
::v-deep .management-form-field:not(.is-switch-active) .v-switch__label {
  color: #000 !important;
}

::v-deep .management-form-field.is-switch-active .v-field__label,
::v-deep .management-form-field.is-switch-active .v-label,
::v-deep .management-form-field.is-switch-active .v-switch__label {
  color: rgb(var(--v-theme-primary)) !important;
}
</style>
