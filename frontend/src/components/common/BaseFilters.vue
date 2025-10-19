<template>
    <v-card class="mb-4">
        <v-card-title 
            class="d-flex align-center cursor-pointer" 
            @click="toggleExpanded"
        >
            <v-icon left>mdi-filter</v-icon>
            Filtros
            <v-spacer></v-spacer>
            <v-icon>{{ expanded ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
        </v-card-title>

        <v-expand-transition>
            <v-card-text v-show="expanded">
                <v-row>
                    <slot name="filters" :localFilters="localFilters" :applyFilters="applyFilters" :clearFilters="clearFilters">
                        <!-- Filtros padrão serão inseridos aqui via slot -->
                    </slot>
                    
                    <!-- Botões de ação padrão -->
                    <v-col cols="12" class="d-flex gap-2">
                        <v-btn color="primary" @click="applyFilters" size="default">
                            <v-icon left>mdi-magnify</v-icon>
                            Filtrar
                        </v-btn>
                        <v-btn color="secondary" class="ml-3" @click="clearFilters" size="default">
                            <v-icon left>mdi-close</v-icon>
                            Limpar
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-expand-transition>
    </v-card>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

interface Props {
    filters: Record<string, any>;
    expanded?: boolean;
}

interface Emits {
    (e: 'update:filters', filters: Record<string, any>): void;
    (e: 'apply'): void;
    (e: 'clear'): void;
}

const props = withDefaults(defineProps<Props>(), {
    expanded: false
});

const emit = defineEmits<Emits>();

const localFilters = ref<Record<string, any>>({ ...props.filters });

const expanded = ref(props.expanded);

function toggleExpanded() {
    expanded.value = !expanded.value;
}

function applyFilters() {
    emit('update:filters', { ...localFilters.value });
    emit('apply');
}

function clearFilters() {
    const clearedFilters = Object.fromEntries(
        Object.keys(localFilters.value).map(key => [key, null])
    );
    localFilters.value = { ...clearedFilters };
    emit('update:filters', clearedFilters);
    emit('clear');
}

// Sincroniza o estado local quando os filtros são limpos externamente
watch(() => props.filters, (newFilters) => {
    localFilters.value = { ...newFilters };
}, { deep: true });
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
</style>
