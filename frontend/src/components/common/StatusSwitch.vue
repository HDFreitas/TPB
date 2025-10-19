<template>
  <v-switch
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', !!$event)"
    :label="label"
    :true-icon="modelValue ? 'mdi-check-circle' : 'mdi-circle-outline'"
    :false-icon="!modelValue ? 'mdi-close-circle' : 'mdi-circle-outline'"
    class="status-switch"
    :class="{
      'status-switch--active v-switch--active': modelValue,
      'status-switch--inactive v-switch--inactive': !modelValue
    }"
    hide-details
  >
    <template v-slot:label>
      <span :class="{
        'status-text--active': modelValue,
        'status-text--inactive': !modelValue
      }">
        {{ label }}
        <v-chip 
          :class="{
            'status-chip--active': modelValue,
            'status-chip--inactive': !modelValue
          }"
          size="small"
          class="ml-2"
        >
          {{ modelValue ? 'Ativo' : 'Inativo' }}
        </v-chip>
      </span>
    </template>
  </v-switch>
</template>

<script setup lang="ts">
interface Props {
  modelValue: boolean;
  label?: string;
}

withDefaults(defineProps<Props>(), {
  label: 'Status'
});

defineEmits<{
  'update:modelValue': [value: boolean];
}>();
</script>

<style scoped>
.status-switch {
  margin-bottom: 8px;
}
</style>
