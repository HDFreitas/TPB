<template>
    <BaseFilters
        :filters="filters"
        :expanded="filtersExpanded"
        @update:filters="updateFilters"
        @apply="applyFilters"
        @clear="clearFilters"
    >
        <template #filters="{ localFilters }">
            <v-col cols="12" md="3">
                <v-select
                    v-model="localFilters.action"
                    :items="actionOptions"
                    item-title="text"
                    item-value="value"
                    label="Operação"
                    variant="outlined"
                    density="compact"
                    clearable
                    :loading="loadingActions"
                    prepend-icon="mdi-lightning-bolt"
                />
            </v-col>

            <v-col cols="12" md="3">
                <v-select
                    v-model="localFilters.log_type"
                    :items="logTypeOptions"
                    item-title="text"
                    item-value="value"
                    label="Tipo de Log"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-format-list-bulleted-type"
                />
            </v-col>

            <v-col cols="12" md="3">
                <v-text-field
                    v-model="localFilters.user_id"
                    label="ID do Usuário"
                    variant="outlined"
                    density="compact"
                    type="number"
                    clearable
                    prepend-icon="mdi-account"
                />
            </v-col>

            <v-col cols="12" md="3">
                <v-text-field
                    v-model="localFilters.ip_address"
                    label="Endereço IP"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-ip"
                />
            </v-col>

            <v-col cols="12" md="3">
                <v-text-field
                    v-model="localFilters.content"
                    label="Conteúdo"
                    variant="outlined"
                    density="compact"
                    clearable
                    prepend-icon="mdi-text-search"
                />
            </v-col>

            <v-col cols="12" md="3">
                <v-menu
                    v-model="fromMenu"
                    :close-on-content-click="false"
                    transition="scale-transition"
                    offset-y
                    min-width="auto"
                >
                    <template #activator="{ props }">
                        <v-text-field
                            v-model="localFilters.created_at_from"
                            label="Data Inicial"
                            variant="outlined"
                            density="compact"
                            readonly
                            clearable
                            prepend-icon="mdi-calendar-start"
                            v-bind="props"
                        />
                    </template>
                    <v-date-picker 
                        v-model="localFilters.created_at_from" 
                        @update:modelValue="fromMenu = false"
                    />
                </v-menu>
            </v-col>

            <v-col cols="12" md="3">
                <v-menu
                    v-model="toMenu"
                    :close-on-content-click="false"
                    transition="scale-transition"
                    offset-y
                    min-width="auto"
                >
                    <template #activator="{ props }">
                        <v-text-field
                            v-model="localFilters.created_at_to"
                            label="Data Final"
                            variant="outlined"
                            density="compact"
                            readonly
                            clearable
                            prepend-icon="mdi-calendar-end"
                            v-bind="props"
                        />
                    </template>
                    <v-date-picker 
                        v-model="localFilters.created_at_to" 
                        @update:modelValue="toMenu = false"
                    />
                </v-menu>
            </v-col>
        </template>
    </BaseFilters>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useLogStore } from '@/modules/plataforma/stores/log';
import logService from '@/services/utils/log';
import BaseFilters from './BaseFilters.vue';

const logStore = useLogStore();

const fromMenu = ref(false);
const toMenu = ref(false);
const loadingActions = ref(false);
const availableActions = ref<string[]>([]);

const logTypeOptions = [
    { text: 'Todos', value: null },
    { text: 'Informativo', value: 'info' },
    { text: 'Erro', value: 'error' },
    { text: 'Aviso', value: 'warning' },
    { text: 'Debug', value: 'debug' },
];

// Computed para as opções de operação
const actionOptions = computed(() => {
    return [
        { text: 'Todas as operações', value: null },
        ...availableActions.value.map(operation => ({
            text: formatOperationName(operation),
            value: operation
        }))
    ];
});

// Computed properties para o BaseFilters
const filters = computed(() => logStore.filters);
const filtersExpanded = computed(() => logStore.filtersExpanded);

// Formata o nome da operação para exibição
function formatOperationName(operation: string): string {
    // Mapeamento de operações para nomes em português
    const operationMap: { [key: string]: string } = {
        'CREATE': 'Criar',
        'UPDATE': 'Atualizar',
        'DELETE': 'Excluir',
        'LOGIN': 'Login',
        'LOGOUT': 'Logout',
        'READ': 'Visualizar',
        'SEARCH': 'Buscar',
        'EXPORT': 'Exportar',
        'IMPORT': 'Importar'
    };
    
    return operationMap[operation.toUpperCase()] || operation.charAt(0).toUpperCase() + operation.slice(1).toLowerCase();
}

// Carrega as operações disponíveis
async function loadActions() {
    loadingActions.value = true;
    try {
        const response = await logService.getActions();
        availableActions.value = response.data || [];
    } catch (error) {
        console.error('Erro ao carregar operações:', error);
        availableActions.value = [];
    } finally {
        loadingActions.value = false;
    }
}

function updateFilters(newFilters: any) {
    // Atualiza o store com os filtros
    Object.keys(newFilters).forEach(key => {
        logStore.setFilter(key, newFilters[key]);
    });
}

function applyFilters() {
    logStore.applyFilters();
}

function clearFilters() {
    logStore.clearFilters();
}

// Carrega as operações ao montar o componente
onMounted(() => {
    loadActions();
});
</script>

