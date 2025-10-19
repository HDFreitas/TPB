<script setup lang="ts">
import { ref, shallowRef } from 'vue';
import sidebarItems from './vertical-sidebar/sidebarItem';
import NavGroup from './vertical-sidebar/NavGroup/index.vue';
import NavItem from './vertical-sidebar/NavItem/index.vue';
import Logo from './logo/Logo.vue';
// Icon Imports
import { Menu2Icon } from 'vue-tabler-icons';
import ProfileDD from './vertical-header/ProfileDD.vue';
const sidebarMenu = shallowRef(sidebarItems);
const sDrawer = ref(true);

</script>

<template>
    <v-navigation-drawer left v-model="sDrawer" app class="leftSidebar bg-containerBg" elevation="10"
        width="300">
        <div class="pa-5 pl-4 ">
            <Logo />
        </div>
        <!-- ---------------------------------------------- -->
        <!---Navigation -->
        <!-- ---------------------------------------------- -->
        <perfect-scrollbar class="scrollnavbar bg-containerBg overflow-y-hidden">
            <v-list class="py-4 px-4 bg-containerBg">
                <!---Menu Loop -->
                <template v-for="item in sidebarMenu" :key="item.title || item.header">
                    <!---Item Sub Header -->
                    <NavGroup :item="item" v-if="item.header" :key="item.title" />
                    <!---Single Item-->
                    <NavItem :item="item" v-else class="leftPadding" />
                    <!---End Single Item-->
                </template>
                <!-- <Moreoption/> -->
            </v-list>
        </perfect-scrollbar>
    </v-navigation-drawer>
    <div class="container verticalLayout">
            <v-app-bar elevation="0" height="70">
                <div class="d-flex align-center justify-space-between w-100">
                    <div>
                        <v-btn class="hidden-lg-and-up text-muted" @click="sDrawer = !sDrawer" icon
                            variant="flat" size="small">
                            <Menu2Icon size="20" stroke-width="1.5" />
                        </v-btn>
                        <!-- Notifications - Ative se necessário -->
                        <!-- <NotificationDD /> -->
                    </div>
                    <div>
                        <!-- User Profile -->
                        <ProfileDD />
                    </div>
                </div>
            </v-app-bar>
    </div>
</template>
