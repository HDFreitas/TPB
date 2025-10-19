<template>
  <v-card class="perfil-users-manager">
    <v-card-title class="d-flex align-center">
      <v-icon left>mdi-account-group</v-icon>
      Usuários do Perfil
      <v-spacer></v-spacer>
      <v-btn
        v-if="selectedAvailableUsers.length > 0"
        color="primary"
        @click="associateSelectedUsers"
        :loading="perfilStore.usersLoading"
        size="small"
      >
        <v-icon left>mdi-account-plus</v-icon>
        Associar Selecionados
      </v-btn>
    </v-card-title>

    <!-- Exibir erro se houver -->
    <v-alert
      v-if="perfilStore.error"
      type="error"
      variant="tonal"
      density="compact"
      class="ma-4"
      closable
      @click:close="clearError"
    >
      {{ perfilStore.error }}
    </v-alert>

    <v-card-text>
      <v-row>
        <!-- Usuários Associados -->
        <v-col cols="12" md="6">
          <v-card variant="outlined">
            <v-card-title class="text-h6">
              Usuários Associados ({{ perfilStore.perfilUsers.associated.length }})
            </v-card-title>
            <v-card-text>
              <v-list v-if="perfilStore.perfilUsers.associated.length > 0" density="compact">
                <v-list-item
                  v-for="user in perfilStore.perfilUsers.associated"
                  :key="user.id"
                  class="user-item"
                >
                  <template #prepend>
                    <v-avatar size="32" color="primary">
                      <v-icon>mdi-account</v-icon>
                    </v-avatar>
                  </template>
                  
                  <v-list-item-title>{{ user.name }}</v-list-item-title>
                  <v-list-item-subtitle>{{ user.email }}</v-list-item-subtitle>
                  
                  <template #append>
                    <v-btn
                      icon
                      size="small"
                      color="error"
                      variant="text"
                      @click="removeUser(user.id)"
                      :loading="perfilStore.usersLoading"
                    >
                      <v-icon>mdi-account-minus</v-icon>
                    </v-btn>
                  </template>
                </v-list-item>
              </v-list>
              
              <v-alert
                v-else
                type="info"
                variant="tonal"
                density="compact"
              >
                Nenhum usuário associado a este perfil.
              </v-alert>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Usuários Disponíveis -->
        <v-col cols="12" md="6">
          <v-card variant="outlined">
            <v-card-title class="text-h6">
              Usuários Disponíveis ({{ perfilStore.perfilUsers.available.length }})
            </v-card-title>
            <v-card-text>
              <!-- Filtro de busca -->
              <v-text-field
                v-model="searchAvailable"
                label="Buscar usuários disponíveis"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="compact"
                clearable
                class="mb-3"
              />
              
              <v-list v-if="filteredAvailableUsers.length > 0" density="compact">
                <v-list-item
                  v-for="user in filteredAvailableUsers"
                  :key="user.id"
                  class="user-item"
                >
                  <template #prepend>
                    <v-checkbox
                      v-model="selectedAvailableUsers"
                      :value="user.id"
                      density="compact"
                    />
                    <v-avatar size="32" color="secondary" class="ml-2">
                      <v-icon>mdi-account-outline</v-icon>
                    </v-avatar>
                  </template>
                  
                  <v-list-item-title>{{ user.name }}</v-list-item-title>
                  <v-list-item-subtitle>{{ user.email }}</v-list-item-subtitle>
                </v-list-item>
              </v-list>
              
              <v-alert
                v-else-if="perfilStore.perfilUsers.available.length === 0"
                type="info"
                variant="tonal"
                density="compact"
              >
                Todos os usuários já estão associados a este perfil.
              </v-alert>
              
              <v-alert
                v-else
                type="info"
                variant="tonal"
                density="compact"
              >
                Nenhum usuário encontrado com o filtro aplicado.
              </v-alert>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-card-text>

    <!-- Loading overlay -->
    <v-overlay
      v-if="perfilStore.usersLoading"
      contained
      class="d-flex align-center justify-center"
    >
      <v-progress-circular indeterminate size="64" />
    </v-overlay>
  </v-card>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { usePerfilStore } from '@/modules/plataforma/stores/perfil';

interface Props {
  perfilId: number;
}

const props = defineProps<Props>();
const perfilStore = usePerfilStore();

// Estados locais
const searchAvailable = ref('');
const selectedAvailableUsers = ref<number[]>([]);

// Computed
const filteredAvailableUsers = computed(() => {
  if (!searchAvailable.value) {
    return perfilStore.perfilUsers.available;
  }
  
  const search = searchAvailable.value.toLowerCase();
  return perfilStore.perfilUsers.available.filter((user: any) =>
    user.name.toLowerCase().includes(search) ||
    user.email.toLowerCase().includes(search) ||
    user.usuario?.toLowerCase().includes(search)
  );
});

// Métodos
function clearError() {
  perfilStore.error = null;
}

async function associateSelectedUsers() {
  if (selectedAvailableUsers.value.length === 0) return;
  
  await perfilStore.associateUsers(props.perfilId, selectedAvailableUsers.value);
  selectedAvailableUsers.value = [];
}

async function removeUser(userId: number) {
  if (confirm('Deseja realmente remover este usuário do perfil?')) {
    await perfilStore.removeUser(props.perfilId, userId);
  }
}

// Watchers
watch(() => props.perfilId, async (newId) => {
  if (newId) {
    await perfilStore.fetchPerfilUsers(newId);
  }
}, { immediate: true });

onMounted(async () => {
  if (props.perfilId) {
    await perfilStore.fetchPerfilUsers(props.perfilId);
  }
});
</script>

<style scoped>
.perfil-users-manager {
  position: relative;
}

.user-item {
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.user-item:last-child {
  border-bottom: none;
}
</style>
