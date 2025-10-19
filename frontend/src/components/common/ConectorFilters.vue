<template>
    <BaseFilters :filters="localFilters" :expanded="filtersExpanded" @update:filters="updateFilters" @apply="applyFilters" @clear="clearFilters">
        <template #filters="{ localFilters }">
            <v-col v-if="isHubUser" cols="12" md="3">
                <v-select
                    v-model="localFilters.tenant_id"
                    :items="tenantOptions"
                    item-title="nome"
                    item-value="id"
                    label="Tenant"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-domain"
                />
            </v-col>
            <v-col cols="12" md="3">
                <v-text-field
                    v-model="localFilters.nome"
                    label="Nome"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-connection"
                />
            </v-col>
            <v-col cols="12" md="3">
                <v-select
                    v-model="localFilters.status"
                    :items="statusOptions"
                    item-title="text"
                    item-value="value"
                    label="Status"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-check-circle"
                />
            </v-col>
        </template>
    </BaseFilters>
</template>

<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue';
import { useConectorStore } from '@/modules/csi/stores/conector';
import { useTenantStore } from '@/modules/plataforma/stores/tenant';
import { useAuthStore } from '@/modules/plataforma/stores/auth';
import BaseFilters from '@/components/common/BaseFilters.vue';

const conectorStore = useConectorStore();
const tenantStore = useTenantStore();
const authStore = useAuthStore();

// Verificar se o usuário é HUB
const isHubUser = computed(() => {
    const user = authStore.getUser;
    return user?.roles?.includes('HUB') || false;
});

// Helper function para aplicar filtros de forma dinâmica e type-safe
function applyFiltersDynamically(filters: Record<string, any>) {
    Object.keys(filters).forEach(key => {
        conectorStore.setFilter(key, filters[key]);
    });
}

// Não altera o store a cada modificação - explicitly typed
const localFilters = ref<Record<string, any>>({
    tenant_id: conectorStore.filters.tenant_id,
    nome: conectorStore.filters.nome,
    status: conectorStore.filters.status,
});

const statusOptions = [
    { text: 'Todos', value: null },
    { text: 'Ativo', value: true },
    { text: 'Inativo', value: false },
];

// Opções de tenant para usuários HUB
const tenantOptions = computed(() => {
    if (!isHubUser.value) return [];
    
    return [
        { id: null, nome: 'Todos os Tenants' },
        ...tenantStore.tenants.map(tenant => ({
            id: tenant.id,
            nome: tenant.nome
        }))
    ];
});


// acessa o estado da store
const filtersExpanded = computed(() => conectorStore.filtersExpanded);


function updateFilters(filters: Record<string, any>) {
    localFilters.value = { ...filters };
}

function applyFilters() {
    applyFiltersDynamically(localFilters.value);
    conectorStore.applyFilters();
}

function clearFilters() {
    conectorStore.clearFilters();
    localFilters.value = { ...conectorStore.filters };
}

watch(() => conectorStore.filters, (newFilters) => {
    localFilters.value = { ...newFilters };
}, { deep: true });

// Carregar tenants se o usuário for HUB
onMounted(async () => {
    if (isHubUser.value) {
        await tenantStore.fetchTenants();
    }
});
</script>
