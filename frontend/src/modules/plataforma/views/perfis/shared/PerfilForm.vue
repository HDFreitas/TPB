<template>
  <v-container fluid class="perfil-form-container">
    <v-row>
      <v-col cols="12">
        <!-- Header com navegação -->
        <v-card class="mb-4">
          <v-card-title class="d-flex align-center">
            <v-btn
              icon
              @click="goBack"
              class="mr-3"
            >
              <v-icon>mdi-arrow-left</v-icon>
            </v-btn>
            <div>
              <h2 class="text-h5">{{ formTitle }}</h2>
              <p class="text-body-2 text-medium-emphasis mb-0" v-if="isEditing && selectedPerfil">
              </p>
            </div>
            <v-spacer></v-spacer>
            
            <!-- Botão Salvar no canto superior direito (apenas quando pode editar) -->
            <v-btn
              v-if="canEdit"
              color="primary"
              @click="savePerfil"
              :loading="perfilStore.loading"
              :disabled="!isFormValid"
            >
              <v-icon left>mdi-content-save</v-icon>
              {{ getSaveButtonText }}
            </v-btn>
          </v-card-title>
        </v-card>

        <!-- Exibir erro global se houver -->
        <v-alert
          v-if="perfilStore.error"
          type="error"
          variant="tonal"
          density="compact"
          class="mb-4"
          closable
          @click:close="clearError"
        >
          {{ perfilStore.error }}
        </v-alert>

        <!-- Tabs para organizar o conteúdo -->
        <v-tabs v-model="activeTab" class="mb-4">
          <v-tab value="basic">
            <v-icon left>mdi-information</v-icon>
            Informações Básicas
          </v-tab>
          <v-tab value="users" :disabled="!isEditing || !canEdit">
            <v-icon left>mdi-account-group</v-icon>
            Usuários
          </v-tab>
          <v-tab value="permissions" :disabled="!isEditing || !canEdit">
            <v-icon left>mdi-shield-account</v-icon>
            Permissões
          </v-tab>
        </v-tabs>

        <!-- Conteúdo das tabs -->
        <v-window v-model="activeTab">
          <!-- Tab: Informações Básicas -->
          <v-window-item value="basic">
            <v-card>
              <v-card-title>
                <v-icon left>mdi-information</v-icon>
                Dados do Perfil
              </v-card-title>
              <v-card-text>
                <v-form ref="formRef" v-model="isFormValid">
                  <v-row>
                    <v-col cols="12" md="6">
                      <v-text-field
                        v-model="formData.name"
                        label="Nome do Perfil *"
                        variant="outlined"
                        :rules="canEdit ? nameRules : []"
                        :disabled="isSpecialProfile || !canEdit"
                        required
                      />
                    </v-col>
                    <v-col cols="12">
                      <v-textarea
                        v-model="formData.description"
                        label="Descrição"
                        variant="outlined"
                        rows="3"
                        :disabled="isSpecialProfile || !canEdit"
                      />
                    </v-col>
                    <!-- Mostra status quando está editando (exceto para perfil HUB) -->
                    <v-col cols="12" md="6" v-if="isEditing && !isHubProfile">
                      <StatusSwitch
                        v-model="formData.status"
                        label="Status do Perfil"
                        :disabled="!canEdit"
                      />
                    </v-col>
                  </v-row>

                  <!-- Aviso para perfis especiais -->
                  <v-alert
                    v-if="isSpecialProfile"
                    type="warning"
                    variant="tonal"
                    density="compact"
                    class="mt-4"
                  >
                    <v-icon left>mdi-alert</v-icon>
                    <span v-if="isHubProfile">
                      Este é o perfil HUB. Nome, descrição e status não podem ser alterados.
                      Apenas a vinculação de usuários é permitida.
                    </span>
                    <span v-else>
                      Este é um perfil especial ({{ formData.name }}). Nome e descrição não podem ser alterados.
                      Apenas o status e a vinculação de usuários são permitidos.
                    </span>
                  </v-alert>
                </v-form>
              </v-card-text>
            </v-card>
          </v-window-item>

          <!-- Tab: Usuários -->
          <v-window-item value="users">
            <PerfilUsersManager
              v-if="isEditing && selectedPerfil && canEdit"
              :perfil-id="selectedPerfil.id"
            />
            <v-card v-else-if="isEditing && selectedPerfil && !canEdit">
              <v-card-text class="text-center pa-8">
                <v-icon size="64" color="grey-lighten-1">mdi-lock</v-icon>
                <h3 class="text-h6 mt-4 mb-2">Acesso Restrito</h3>
                <p class="text-body-2 text-medium-emphasis">
                  Você precisa de permissão de editar perfis para gerenciar usuários.
                </p>
              </v-card-text>
            </v-card>
          </v-window-item>

          <!-- Tab: Permissões -->
          <v-window-item value="permissions">
            <PerfilPermissionsManager
              v-if="isEditing && selectedPerfil && canEdit"
              ref="permissionsManagerRef"
              :perfil-id="selectedPerfil.id"
              :profile-name="selectedPerfil.name"
              @permissions-changed="onPermissionsChanged"
            />
            <v-card v-else-if="isEditing && selectedPerfil && !canEdit">
              <v-card-text class="text-center pa-8">
                <v-icon size="64" color="grey-lighten-1">mdi-lock</v-icon>
                <h3 class="text-h6 mt-4 mb-2">Acesso Restrito</h3>
                <p class="text-body-2 text-medium-emphasis">
                  Você precisa de permissão de editar perfis para gerenciar permissões.
                </p>
              </v-card-text>
            </v-card>
          </v-window-item>
        </v-window>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { usePerfilStore } from '@/modules/plataforma/stores/perfil';
import { usePermissions } from '@/composables/usePermissions';
import PerfilUsersManager from './PerfilUsersManager.vue';
import PerfilPermissionsManager from './PerfilPermissionsManager.vue';
import StatusSwitch from '@/components/common/StatusSwitch.vue';

const router = useRouter();
const route = useRoute();
const perfilStore = usePerfilStore();
const { perfilPermissions } = usePermissions();

// Estados locais
const activeTab = ref('basic');
const isFormValid = ref(false);
const formRef = ref();
const permissionsManagerRef = ref();
const hasPermissionChanges = ref(false);

// Dados do formulário
const formData = ref({
  id: null as number | null,
  name: '',
  description: '',
  status: true,
});

// Computed
const isEditing = computed(() => !!route.params.id);
const isViewMode = computed(() => route.name === 'perfil-visualizar');
const canEdit = computed(() => {
  if (isViewMode.value) {
    return false; // Em modo visualização, nunca pode editar
  }
  
  if (!isEditing.value) {
    // Criando novo perfil - verificar permissão de criar
    return perfilPermissions.value.canCreate;
  }
  
  // Editando perfil existente - verificar permissão de editar
  return perfilPermissions.value.canEdit;
});
const selectedPerfil = computed(() => perfilStore.selectedPerfil);
const formTitle = computed(() => {
  if (isViewMode.value) return 'Visualizar Perfil';
  return isEditing.value ? 'Editar Perfil' : 'Novo Perfil';
});

const isSpecialProfile = computed(() => {
  // Só considera perfil especial se estiver editando E o perfil for HUB/ADMINISTRADOR
  return isEditing.value && 
         selectedPerfil.value && 
         selectedPerfil.value.name && 
         ['HUB', 'ADMINISTRADOR'].includes(selectedPerfil.value.name.toUpperCase());
});

const isHubProfile = computed(() => {
  // Verifica se é especificamente o perfil HUB
  return isEditing.value && 
         selectedPerfil.value && 
         selectedPerfil.value.name && 
         selectedPerfil.value.name.toUpperCase() === 'HUB';
});

const getSaveButtonText = computed(() => {
  if (!isEditing.value) {
    return 'Criar Perfil';
  }
  
  if (hasPermissionChanges.value) {
    return 'Salvar Alterações e Permissões';
  }
  
  return 'Salvar Alterações';
});

// Regras de validação
const nameRules = [
  (v: string) => !!v || 'Nome é obrigatório',
  (v: string) => (v && v.length >= 2) || 'Nome deve ter pelo menos 2 caracteres',
  (v: string) => (v && v.length <= 255) || 'Nome deve ter no máximo 255 caracteres',
  (v: string) => {
    // Verificar se já existe um perfil com este nome (apenas para novos perfis ou quando o nome foi alterado)
    if (!isEditing.value || (selectedPerfil.value && selectedPerfil.value.name !== v)) {
      const existingPerfil = perfilStore.perfis.find(p => 
        p.name.toLowerCase() === v.toLowerCase() && 
        (!isEditing.value || p.id !== selectedPerfil.value?.id)
      );
      if (existingPerfil) {
        return `Já existe um perfil com o nome "${v}". Por favor, escolha outro nome.`;
      }
    }
    return true;
  }
];

// Métodos
function clearError() {
  perfilStore.error = null;
}

function goBack() {
  router.push('/plataforma/perfis');
}

function onPermissionsChanged(hasChanges: boolean) {
  hasPermissionChanges.value = hasChanges;
}

async function savePerfil() {
  if (!isFormValid.value) {
    return;
  }

  let payload: any;

  // Para perfil HUB, não enviar nenhum campo (apenas vinculação de usuários)
  if (isHubProfile.value) {
    // HUB não permite alteração de nenhum campo básico
    payload = {};
  } else if (isSpecialProfile.value) {
    // Para perfil Administrador, enviar apenas o status
    payload = {
      status: formData.value.status,
    };
  } else {
    // Para perfis normais, enviar todos os campos
    payload = {
      name: formData.value.name,
      description: formData.value.description || null,
      status: formData.value.status,
    };
  }

  try {
    if (isEditing.value) {
      await perfilStore.updatePerfil(Number(route.params.id), payload);
      
      // Se há mudanças nas permissões e não houve erro, salvar permissões também
      if (!perfilStore.error && hasPermissionChanges.value && permissionsManagerRef.value) {
        await permissionsManagerRef.value.savePermissions();
      }
    } else {
      await perfilStore.createPerfil(payload);
    }
    
    // Se não houve erro, voltar para a lista
    if (!perfilStore.error) {
      router.push('/plataforma/perfis');
    }
  } catch (error) {
    // Erro já tratado no store
  }
}

// Watchers
watch(selectedPerfil, (perfil) => {
  if (perfil) {
    formData.value = {
      id: perfil.id,
      name: perfil.name || '',
      description: perfil.description || '',
      status: perfil.status !== undefined ? perfil.status : true,
    };
  }
}, { immediate: true });

// Lifecycle
onMounted(async () => {
  // Limpar dados anteriores
  perfilStore.clearSelectedPerfil();
  perfilStore.clearPerfilData();
  
  if (isEditing.value) {
    const perfilId = Number(route.params.id);
    if (perfilId) {
      await perfilStore.getPerfilById(perfilId);
    }
  } else {
    // Novo perfil - limpar formulário
    formData.value = {
      id: null,
      name: '',
      description: '',
      status: true,
    };
  }
});
</script>

<style scoped>
.perfil-form-container {
  max-width: 1200px;
  margin: 0 auto;
}

.v-window-item {
  padding: 0;
}
</style>