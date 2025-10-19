<template>
  <v-chip
    :class="{
      'status-chip--active': status,
      'status-chip--inactive': !status
    }"
    :size="size"
    :prepend-icon="status ? 'mdi-check-circle' : 'mdi-close-circle'"
    class="status-badge"
  >
    {{ status ? activeText : inactiveText }}
  </v-chip>
</template>

<script setup lang="ts">
interface Props {
  status: boolean | undefined;
  size?: 'x-small' | 'small' | 'default' | 'large' | 'x-large';
  activeText?: string;
  inactiveText?: string;
}

withDefaults(defineProps<Props>(), {
  size: 'small',
  activeText: 'Ativo',
  inactiveText: 'Inativo'
});
</script>
