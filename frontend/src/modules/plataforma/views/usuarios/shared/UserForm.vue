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
            
            <!-- Botão Salvar (apenas quando pode editar) -->
            <v-btn
              v-if="canEdit"
              color="primary"
              @click="saveUser"
              :loading="userStore.loading"
            >
              <v-icon left>mdi-content-save</v-icon>
              Salvar
            </v-btn>
          </v-card-title>
          
          <v-card-text class="management-form-container">
            <v-form ref="formRef" @submit.prevent="saveUser" class="management-form">
              <!-- Dados Básicos -->
              <v-row>
                <v-col cols="12">
                  <h3 class="section-title">Dados Básicos</h3>
                </v-col>
                <!-- Tenant será definido automaticamente pelo usuário logado -->
                <v-col cols="12" v-if="false">
                  <v-alert type="info" variant="tonal" class="mb-4">
                    <v-icon>mdi-information</v-icon>
                    O usuário será criado no mesmo tenant do usuário logado
                  </v-alert>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.name" 
                    label="Nome *" 
                    required
                    :rules="canEdit ? [rules.required] : []"
                    :disabled="!canEdit"
                    prepend-icon="mdi-account"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.usuario" 
                    label="Usuário *" 
                    required
                    :rules="!isEdit && canEdit ? [rules.required, rules.usuario] : []"
                    :disabled="isEdit || !canEdit"
                    prepend-icon="mdi-account"
                    :hint="isEdit ? 'O usuário não pode ser alterado após a criação' : 'Digite apenas letras minúsculas e pontos'"
                    persistent-hint
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.email" 
                    label="Email *" 
                    type="email"
                    required
                    :rules="canEdit ? [rules.required, rules.email] : []"
                    :disabled="!canEdit"
                    prepend-icon="mdi-email"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.dominio" 
                    label="Domínio" 
                    disabled
                    prepend-icon="mdi-domain"
                    hint="Preenchido automaticamente com o domínio do seu tenant"
                    persistent-hint
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6" v-if="!isEdit">
                  <v-text-field 
                    class="management-form-field" 
                    v-model="form.password" 
                    label="Senha *" 
                    type="password"
                    required
                    :rules="canEdit ? [rules.required, rules.password] : []"
                    :disabled="!canEdit"
                    prepend-icon="mdi-lock"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <StatusSwitch
                    v-model="(form as any).status"
                    label="Status do Usuário"
                    :disabled="!canEdit"
                  />
                </v-col>
              </v-row>
              
              <!-- Botão Gestão de Perfis (só na edição) -->
              <v-row v-if="isEdit" class="mt-4">
                <v-col cols="12">
                  <v-divider class="mb-4"></v-divider>
                  <h3 class="section-title">Gestão de Perfis</h3>
                  <v-btn 
                    color="primary"
                    variant="outlined"
                    @click="openProfileManagement"
                    :disabled="userStore.loading"
                  >
                    <v-icon left>mdi-account-group</v-icon>
                    {{ isViewMode ? 'Visualizar Perfis do Usuário' : 'Gerenciar Perfis do Usuário' }}
                  </v-btn>
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
import { useUserStore } from '@/modules/plataforma/stores/user';
import { useAuthStore } from '@/modules/plataforma/stores/auth';
import { usePermissions } from '@/composables/usePermissions';
import StatusSwitch from '@/components/common/StatusSwitch.vue';
import type { User } from '@/types/tenant';

const route = useRoute();
const router = useRouter();
const userStore = useUserStore();
const authStore = useAuthStore();
const { userPermissions } = usePermissions();

const formRef = ref();
const form = ref<Partial<User> & { status: boolean }>({
  id: undefined,
  name: '',
  email: '',
  password: '',
  usuario: '',
  dominio: '', // Será preenchido automaticamente
  status: true
});

const isEdit = computed(() => !!route.params.id);
const isViewMode = computed(() => route.name === 'usuario-visualizar');
const canEdit = computed(() => {
  if (isViewMode.value) {
    return false; // Em modo visualização, nunca pode editar
  }
  
  if (!isEdit.value) {
    // Criando novo usuário - verificar permissão de criar
    return userPermissions.value.canCreate;
  }
  
  // Verificar se é usuário administrador ou HUB (fallback)
  const userRoles = authStore.user?.roles || [];
  const isAdmin = userRoles.includes('Administrador') || userRoles.includes('HUB');
  
  // Editando usuário existente - se tem permissão de editar, campos ficam habilitados
  // Se só tem permissão de visualizar, campos ficam desabilitados
  const hasEditPermission = userPermissions.value.canEdit;
  const result = hasEditPermission || isAdmin; // Fallback para admins
  
  
  return result;
});
const formTitle = computed(() => {
  if (isViewMode.value) return 'Visualizar Usuário';
  return isEdit.value ? 'Editar Usuário' : 'Novo Usuário';
});

const rules = {
  required: (value: string | number) => (!!value || value === 0) || 'Campo obrigatório',
  email: (value: string) => !value || /.+@.+\..+/.test(value) || 'Email deve ser válido',
  password: (value: string) => !value || value.length >= 6 || 'Senha deve ter pelo menos 6 caracteres',
  usuario: (value: string) => !value || /^[a-z.]+$/.test(value) || 'Usuário deve conter apenas letras minúsculas e pontos'
};

async function loadUser() {
  if (isEdit.value && route.params.id) {
    const userId = Number(route.params.id);
    
    try {
      await userStore.getUserById(userId);
      
      if (userStore.selectedUser) {
        const user = userStore.selectedUser;
        
        // Preencher o formulário com os dados do usuário
        const currentUser = authStore.getUser;
        form.value = {
          id: user.id,
          name: user.name || '',
          email: user.email || '',
          usuario: user.usuario || '',
          dominio: (currentUser as any)?.dominio || '', // Sempre usar domínio do usuário logado
          status: (user as any).status !== undefined ? (user as any).status : true,
          password: '' // Senha sempre vazia na edição
        };
        
      } else {
        console.error('Usuário não encontrado');
        router.push('/plataforma/usuarios');
      }
    } catch (error) {
      console.error('Erro ao carregar usuário:', error);
      if (userStore.error) {
        // Se há erro na store, mostrar para o usuário
        alert(userStore.error);
      }
      router.push('/plataforma/usuarios');
    }
  }
}

async function saveUser() {
  const { valid } = await formRef.value.validate();
  
  if (!valid) {
    return;
  }

  // Tenant será definido automaticamente pelo backend

  try {
    // Remove campos vazios para não enviar nulls desnecessários
    const dataToSend = Object.fromEntries(
      Object.entries(form.value).filter(([_, value]) => value !== '' && value !== null && value !== undefined)
    );

    if (isEdit.value && form.value.id) {
      // Na edição, não envia a senha se estiver vazia
      if (!dataToSend.password) {
        delete dataToSend.password;
      }
      await userStore.updateUser(form.value.id, dataToSend);
    } else {
      await userStore.createUser(dataToSend);
    }
    
    if (!userStore.error) {
      router.push('/plataforma/usuarios');
    }
  } catch (error) {
    console.error('Erro ao salvar usuário:', error);
  }
}

function goBack() {
  router.push('/plataforma/usuarios');
}

function openProfileManagement() {
  if (form.value.id) {
    // Navegar para página de gestão de perfis do usuário
    router.push(`/plataforma/usuarios/${form.value.id}/perfis`);
  } else {
    console.error('ID do usuário não encontrado para gestão de perfis');
  }
}

// Função para preencher domínio automaticamente
function setDomainFromAuthUser() {
  const currentUser = authStore.getUser;
  if (currentUser && (currentUser as any).dominio) {
    form.value.dominio = (currentUser as any).dominio;
  }
}

onMounted(async () => {
  // Preencher domínio automaticamente com o do usuário logado
  setDomainFromAuthUser();
  
  // Carrega o usuário se estiver editando
  await loadUser();
});
</script>

<style scoped>
/* Estilos específicos do formulário de usuário */
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
</style>
