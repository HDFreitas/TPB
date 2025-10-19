<script setup>
import Icon from '../Icon.vue';
import { ref, defineAsyncComponent } from 'vue';
const props = defineProps({ item: Object, level: Number });
const groupOpen = ref(false);
// Registro recursivo explícito
const NavItem = defineAsyncComponent(() => import('./index.vue'));
defineExpose();
</script>

<template>
  <div class="mb-1">
    <template v-if="item.children && item.children.length">
      <v-list-group v-model="groupOpen" :value="false" no-action>
        <template #activator="{ props: groupProps }">
          <v-list-item v-bind="groupProps" rounded :class="'bg-hover-' + item.BgColor" :color="item.BgColor" :ripple="false" :disabled="item.disabled">
            <template v-slot:prepend>
              <div :class="'navbox bg-hover-' + item.BgColor" :color="item.BgColor">
                <span class="icon-box">
                  <Icon :item="item.icon" :level="level" :class="'position-relative z-index-2 texthover-' + item.BgColor" />
                </span>
              </div>
            </template>
            <v-list-item-title class="text-subtitle-1 font-weight-medium" :color="item.BgColor">{{ item.title }}</v-list-item-title>
          </v-list-item>
        </template>
        <NavItem v-for="(child, idx) in item.children" :key="child.title + idx" :item="child" :level="(level || 0) + 1" />
      </v-list-group>
    </template>
    <template v-else>
      <v-list-item :to="item.type === 'external' ? '' : item.to" :href="item.type === 'external' ? item.to : ''" rounded
        :class="'bg-hover-' + item.BgColor" :color="item.BgColor" :ripple="false" :disabled="item.disabled"
        :target="item.type === 'external' ? '_blank' : ''">
        <template v-slot:prepend>
          <div :class="'navbox bg-hover-' + item.BgColor" :color="item.BgColor">
            <span class="icon-box">
              <Icon :item="item.icon" :level="level" :class="'position-relative z-index-2 texthover-' + item.BgColor" />
            </span>
          </div>
        </template>
        <v-list-item-title class="text-subtitle-1 font-weight-medium" :color="item.BgColor">{{ item.title }}</v-list-item-title>
        <v-list-item-subtitle v-if="item.subCaption" class="text-caption mt-n1 hide-menu">
          {{ item.subCaption }}
        </v-list-item-subtitle>
        <template v-slot:append v-if="item.chip">
          <v-chip :color="item.chipColor" class="sidebarchip hide-menu"
            :size="item.chipIcon ? 'x-small' : 'x-small'" :variant="item.chipVariant" :prepend-icon="item.chipIcon">
            {{ item.chip }}
          </v-chip>
        </template>
      </v-list-item>
    </template>
  </div>
</template>
