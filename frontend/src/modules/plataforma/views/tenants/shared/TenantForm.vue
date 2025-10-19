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
              @click="saveTenant"
              :loading="tenantStore.loading"
            >
              <v-icon left>mdi-content-save</v-icon>
              Salvar
            </v-btn>
          </v-card-title>
          
          <v-card-text class="management-form-container">
            <!-- Exibir mensagem de erro se houver -->
            <v-alert
              v-if="tenantStore.error"
              type="error"
              dismissible
              @click:close="tenantStore.error = null"
              class="mb-4"
            >
              {{ tenantStore.error }}
            </v-alert>
            
            <v-form ref="formRef" @submit.prevent="saveTenant" class="management-form">
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
                    :rules="canEdit ? [rules.required] : []"
                    :disabled="!canEdit"
                    prepend-icon="mdi-domain"
                    @input="clearError"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.dominio" 
                    label="Dominio *" 
                    required
                    :rules="!isEdit && canEdit ? [rules.dominio] : []"
                    :disabled="isEdit || !canEdit"
                    prepend-icon="mdi-domain"
                    :hint="isEdit ? 'O domínio não pode ser alterado após a criação' : 'Digite apenas letras minúsculas'"
                    persistent-hint
                    @input="clearError"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-textarea 
                    class="management-form-field" 
                    v-model="form.descricao" 
                    label="Descrição" 
                    rows="3"                    
                    :disabled="!canEdit"
                  ></v-textarea>
                </v-col>
                <!-- Mostra status só quando está editando -->
                <v-col cols="12" md="6">
                  <StatusSwitch
                    v-model="form.status"
                    label="Status do Tenant"
                    v-if="isEdit"
                    :disabled="!canEdit"
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
import { useTenantStore } from '@/modules/plataforma/stores/tenant';
import { usePermissions } from '@/composables/usePermissions';
import StatusSwitch from '@/components/common/StatusSwitch.vue';
import type { Tenant } from '@/types/tenant';

const route = useRoute();
const router = useRouter();
const tenantStore = useTenantStore();
const { tenantPermissions } = usePermissions();

const formRef = ref();
const form = ref<Tenant>({
  id: undefined,
  nome: '',
  email: '',
  status: true,
  dominio: '',
  descricao: ''
});

const isEdit = computed(() => !!route.params.id);
const isViewMode = computed(() => route.name === 'tenant-visualizar');
const canEdit = computed(() => {
  if (isViewMode.value) {
    return false; // Em modo visualização, nunca pode editar
  }
  
  if (!isEdit.value) {
    // Criando novo tenant - verificar permissão de criar
    return tenantPermissions.value.canCreate;
  }
  
  // Editando tenant existente - verificar permissão de editar
  return tenantPermissions.value.canEdit;
});
const formTitle = computed(() => {
  if (isViewMode.value) return 'Visualizar Tenant';
  return isEdit.value ? 'Editar Tenant' : 'Novo Tenant';
});

const rules = {
  required: (value: string | number) => (!!value || value === 0) || 'Campo obrigatório',
  email: (value: string) => !value || /.+@.+\..+/.test(value) || 'Email deve ser válido',
  dominio: (value: string) => !value || /^[a-z]+$/.test(value) || 'Domínio deve conter apenas letras minúsculas' 
};

async function loadTenant() {
  if (isEdit.value && route.params.id) {
    const tenantId = Number(route.params.id);
    try {
      await tenantStore.getTenantById(tenantId);
      if (tenantStore.selectedTenant) {
        const tenant = tenantStore.selectedTenant;
        form.value = {
          ...form.value,
          ...tenant,
          id: tenant.id
        };
      }
    } catch (error) {
      console.error('Erro ao carregar tenant:', error);
      router.push('/plataforma/tenants');
    }
  }
}

async function saveTenant() {
  const { valid } = await formRef.value.validate();
  
  if (!valid) {
    return;
  }

  try {
    // Remove campos vazios para não enviar nulls desnecessários
    const dataToSend = Object.fromEntries(
      Object.entries(form.value).filter(([_, value]) => value !== '' && value !== null && value !== undefined)
    );

    // Garante que o status seja enviado corretamente
    dataToSend.status = form.value.status;

    if (isEdit.value && form.value.id) {
      await tenantStore.updateTenant(form.value.id, dataToSend);
    } else {
      await tenantStore.createTenant(dataToSend);
    }
    
    if (!tenantStore.error) {
      router.push('/plataforma/tenants');
    }
  } catch (error) {
    console.error('Erro ao salvar tenant:', error);
  }
}

function goBack() {
  router.push('/plataforma/tenants');
}

function clearError() {
  if (tenantStore.error) {
    tenantStore.error = null;
  }
}

onMounted(async () => {
  // Carrega o tenant se estiver editando
  await loadTenant();
});
</script>

<style scoped>
/* Estilos específicos do formulário de tenant */
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
</style>
