<template>
  <v-card class="perfil-permissions-manager">
    <v-card-title class="d-flex align-center">
      <v-icon left>mdi-shield-account</v-icon>
      Permissões do Perfil
      <v-spacer></v-spacer>
      <v-chip
        v-if="hasChanges"
        color="warning"
        size="small"
      >
        <v-icon left>mdi-alert</v-icon>
        Alterações não salvas
      </v-chip>
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

    <!-- Aviso para perfis especiais -->
    <v-alert
      v-if="isSpecialProfile"
      type="warning"
      variant="tonal"
      density="compact"
      class="ma-4"
    >
      <v-icon left>mdi-alert</v-icon>
      Este é um perfil especial ({{ profileName }}). As permissões são fixas e não podem ser alteradas.
    </v-alert>

    <v-card-text>
      <!-- Filtro de busca -->
      <v-text-field
        v-model="searchPermissions"
        label="Buscar permissões"
        prepend-inner-icon="mdi-magnify"
        variant="outlined"
        density="compact"
        clearable
        class="mb-4"
        :disabled="isSpecialProfile"
      />

      <!-- Ações em lote -->
      <div class="mb-4" v-if="!isSpecialProfile">
        <v-btn
          variant="outlined"
          size="small"
          @click="selectAllVisible"
          class="mr-2"
        >
          <v-icon left>mdi-checkbox-multiple-marked</v-icon>
          Selecionar Todas Visíveis
        </v-btn>
        <v-btn
          variant="outlined"
          size="small"
          @click="deselectAllVisible"
        >
          <v-icon left>mdi-checkbox-multiple-blank-outline</v-icon>
          Desmarcar Todas Visíveis
        </v-btn>
      </div>

      <!-- Lista de permissões agrupadas por módulo -->
      <div v-for="(permissions, module) in groupedPermissions" :key="module" class="mb-4">
        <v-card variant="outlined">
          <v-card-title class="text-h6 bg-light-lighten-4">
            <v-icon left>{{ getModuleIcon(String(module)) }}</v-icon>
            {{ getModuleName(String(module)) }}
            <v-spacer></v-spacer>
            <v-chip
              size="small"
              :color="getModulePermissionCount(permissions) > 0 ? 'primary' : 'default'"
            >
              {{ getModulePermissionCount(permissions) }}/{{ permissions.length }}
            </v-chip>
          </v-card-title>
          
          <v-card-text>
            <v-row>
              <v-col
                v-for="permission in permissions"
                :key="permission.id"
                cols="12"
                sm="6"
                md="4"
              >
                <v-checkbox
                  v-model="selectedPermissions"
                  :value="permission.id"
                  :label="permission.display_name"
                  density="compact"
                  :disabled="isSpecialProfile"
                  hide-details
                />
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </div>

      <!-- Mensagem se não houver permissões -->
      <v-alert
        v-if="Object.keys(groupedPermissions).length === 0"
        type="info"
        variant="tonal"
        density="compact"
      >
        Nenhuma permissão encontrada.
      </v-alert>
    </v-card-text>

    <!-- Loading overlay -->
    <v-overlay
      v-if="perfilStore.permissionsLoading"
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
  profileName?: string;
}

const props = defineProps<Props>();
const perfilStore = usePerfilStore();

// Expor métodos para o componente pai
const emit = defineEmits<{
  permissionsChanged: [hasChanges: boolean];
}>();

// Estados locais
const searchPermissions = ref('');
const selectedPermissions = ref<number[]>([]);
const initialPermissions = ref<number[]>([]);

// Computed
const isSpecialProfile = computed(() => {
  return Boolean(props.profileName && ['HUB', 'ADMINISTRADOR'].includes(props.profileName.toUpperCase()));
});

const hasChanges = computed(() => {
  if (isSpecialProfile.value) return false;
  
  const current = [...selectedPermissions.value].sort();
  const initial = [...initialPermissions.value].sort();
  
  return JSON.stringify(current) !== JSON.stringify(initial);
});

const allPermissions = computed(() => {
  return [
    ...perfilStore.perfilPermissions.assigned,
    ...perfilStore.perfilPermissions.available
  ];
});

const filteredPermissions = computed(() => {
  // Filtrar permissões de tenants (apenas perfil HUB deve ter essas permissões)
  // Permissões como: tenants.visualizar, tenants.criar, tenants.editar, tenants.excluir
  let permissions = allPermissions.value.filter((permission: any) => 
    !permission.name.startsWith('tenants.')
  );
  
  if (!searchPermissions.value) {
    return permissions;
  }
  
  const search = searchPermissions.value.toLowerCase();
  return permissions.filter((permission: any) =>
    permission.display_name.toLowerCase().includes(search) ||
    permission.name.toLowerCase().includes(search)
  );
});

const groupedPermissions = computed(() => {
  const groups: { [key: string]: any[] } = {};
  
  filteredPermissions.value.forEach((permission: any) => {
    const module = permission.name.split('.')[0];
    if (!groups[module]) {
      groups[module] = [];
    }
    groups[module].push(permission);
  });
  
  // Ordenar permissões dentro de cada módulo
  Object.keys(groups).forEach(module => {
    groups[module].sort((a, b) => a.display_name.localeCompare(b.display_name));
  });
  
  return groups;
});

// Métodos
function clearError() {
  perfilStore.error = null;
}

function getModuleIcon(module: string): string {
  const icons: { [key: string]: string } = {
    tenants: 'mdi-domain',
    usuarios: 'mdi-account-group',
    perfis: 'mdi-shield-account',
    permissoes: 'mdi-key',
    clientes: 'mdi-account-tie',
  };
  return icons[module] || 'mdi-cog';
}

function getModuleName(module: string): string {
  const names: { [key: string]: string } = {
    tenants: 'Tenants',
    usuarios: 'Usuários',
    perfis: 'Perfis',
    permissoes: 'Permissões',
    clientes: 'Clientes',
    tipos_interacao: 'Tipos de Interação',
    interacoes: 'Interações',
  };
  return names[module] || module.charAt(0).toUpperCase() + module.slice(1);
}

function getModulePermissionCount(permissions: any[]): number {
  return permissions.filter(p => selectedPermissions.value.includes(p.id)).length;
}

function selectAllVisible() {
  const visibleIds = filteredPermissions.value.map((p: any) => p.id);
  const newSelected = [...new Set([...selectedPermissions.value, ...visibleIds])];
  selectedPermissions.value = newSelected;
}

function deselectAllVisible() {
  const visibleIds = new Set(filteredPermissions.value.map((p: any) => p.id));
  selectedPermissions.value = selectedPermissions.value.filter(id => !visibleIds.has(id));
}

async function savePermissions() {
  await perfilStore.syncPermissions(props.perfilId, selectedPermissions.value);
  initialPermissions.value = [...selectedPermissions.value];
}

// Expor método para o componente pai
defineExpose({
  savePermissions,
  hasChanges
});

// Watchers
watch(() => props.perfilId, async (newId) => {
  if (newId) {
    await loadPermissions();
  }
}, { immediate: true });

watch(() => perfilStore.perfilPermissions.assigned, (assigned) => {
  selectedPermissions.value = assigned.map((p: any) => p.id);
  initialPermissions.value = [...selectedPermissions.value];
}, { deep: true });

// Notificar o componente pai sobre mudanças
watch(hasChanges, (newValue) => {
  emit('permissionsChanged', newValue);
}, { immediate: true });

async function loadPermissions() {
  if (props.perfilId) {
    await perfilStore.fetchPerfilPermissions(props.perfilId);
  }
}

onMounted(async () => {
  await loadPermissions();
});
</script>

<style scoped>
.perfil-permissions-manager {
  position: relative;
}

.v-card-title.bg-grey-lighten-4 {
  background-color: rgb(var(--v-theme-surface-variant)) !important;
}
</style>
