<script setup lang="ts">
// Icons removidos - não utilizados no template
import { useAuthStore } from '@/modules/plataforma/stores/auth';
import { useRouter } from 'vue-router';

const router = useRouter();
const auth = useAuthStore();

const logout = async () => {
    await auth.logout();

    router.push("/auth/login");
}
</script>

<template>
    <!-- ---------------------------------------------- -->
    <!-- notifications DD -->
    <!-- ---------------------------------------------- -->
    <v-menu :close-on-content-click="false">
        <template v-slot:activator="{ props }">
            <v-btn class="" variant="text" v-bind="props" icon>
                <v-avatar size="35">
                    <img src="@/assets/images/profile/user-1.jpg" height="35" alt="user" />
                </v-avatar>
            </v-btn>
        </template>
        <v-sheet rounded="xl" width="200" elevation="10" class="mt-2">
            <!-- Adicione menus conforme o necessário - Exemplo abaixo -->
            <!-- <v-list class="py-0" lines="one" density="compact">
                <v-list-item value="item1" color="primary" >
                    <template v-slot:prepend>
                        <UserIcon stroke-width="1.5" size="20"/>
                    </template>
                    <v-list-item-title class="pl-4 text-body-1">Meu Perfil</v-list-item-title>
                </v-list-item>
            </v-list> -->
            <div class="pt-4 pb-4 px-5 text-center">
                <v-btn to="" color="primary" variant="outlined" block @click="logout">Sair</v-btn>
            </div>
        </v-sheet>
    </v-menu>
</template>
