# Componentes de Status Padronizados

Este projeto utiliza um sistema padronizado de cores e componentes para representar status ativo/inativo:

## Cores Padrão
- **Ativo**: Verde (#4caf50)
- **Inativo**: Vermelho (#f44336)

## Composable useStatus

Para facilitar o trabalho com status, use o composable `useStatus`:

```typescript
import { useStatus } from '@/composables/useStatus';

const { statusToBoolean, booleanToStatus, getStatusClass, getStatusText } = useStatus();

// Converte string para boolean
const isActive = statusToBoolean('ativo'); // true
const isInactive = statusToBoolean('inativo'); // false

// Converte boolean para string
const status = booleanToStatus(true); // 'ativo'

// Obtém classe CSS
const cssClass = getStatusClass(true); // 'status-chip--active'

// Obtém texto formatado
const text = getStatusText(true); // 'Ativo'
```

## Componentes Disponíveis

### 1. StatusSwitch
Componente de switch para alternar entre ativo/inativo com visual padronizado.

```vue
<template>
  <StatusSwitch v-model="form.active" label="Status do Cliente" />
</template>

<script setup>
import StatusSwitch from '@/components/common/StatusSwitch.vue';
</script>
```

### 2. StatusBadge
Componente para exibir status como badge/chip.

```vue
<template>
  <StatusBadge :status="cliente.active" />
  <!-- ou com textos customizados -->
  <StatusBadge 
    :status="cliente.active" 
    active-text="Habilitado"
    inactive-text="Desabilitado"
  />
</template>

<script setup>
import StatusBadge from '@/components/common/StatusBadge.vue';
</script>
```

## Classes CSS Disponíveis

### Status
- `.status-badge--active`: Badge verde para status ativo
- `.status-badge--inactive`: Badge vermelho para status inativo
- `.status-btn--active`: Botão verde
- `.status-btn--inactive`: Botão vermelho
- `.status-chip--active`: Chip verde
- `.status-chip--inactive`: Chip vermelho
- `.status-icon--active`: Ícone verde
- `.status-icon--inactive`: Ícone vermelho
- `.status-text--active`: Texto verde
- `.status-text--inactive`: Texto vermelho

### Actions (para logs)
- `.action-create`: Chip verde para ações de criação
- `.action-update`: Chip azul para ações de atualização
- `.action-delete`: Chip vermelho para ações de exclusão
- `.action-auth`: Chip roxo para ações de autenticação
- `.action-other`: Chip cinza para outras ações

## Exemplos de Uso

### Em Data Tables

```vue
<template #item.active="{ item }">
  <StatusBadge :status="item.active" />
</template>
```

### Em Formulários

```vue
<StatusSwitch v-model="form.active" label="Status do Item" />
```

### Com Composable

```vue
<script setup>
import { useStatus } from '@/composables/useStatus';

const { getStatusText, getStatusColor } = useStatus();

function getDisplayStatus(item) {
  return getStatusText(item.active, 'Habilitado', 'Desabilitado');
}
</script>
```
