<script setup lang="ts">
import { ref, computed } from "vue";
import Logo from "@/layouts/full/logo/Logo.vue";
import { useAuthStore } from "@/modules/plataforma/stores/auth";
import { useRouter } from "vue-router";

const router = useRouter();
const auth = useAuthStore();

// Ative caso necessário - Remenber me
// const checkbox = ref(false);
const login = ref("");
const password = ref("");

// Computed para obter erros específicos por campo
const loginErrors = computed(() => auth.getValidationErrors.login || []);
const passwordErrors = computed(() => auth.getValidationErrors.password || []);
const generalError = computed(() => auth.getError);

// Validação do formato de login
const loginFormatError = computed(() => {
  if (!login.value) return [];
  const regex = /^[a-z.]+@[a-z]+\.com\.br$/;
  return regex.test(login.value) ? [] : ['Formato deve ser: usuario@dominio.com.br'];
});

const submit = async () => {
	try {
		await auth.login({
			login: login.value,
			password: password.value,
		});

		router.push("/");
	} catch (error) {
		// Os erros já são tratados no store
	}
};
</script>

<template>
	<div class="d-flex justify-center align-center">
		<div
			class="d-flex flex-column align-center justify-center text-center"
			style="min-width: 400px"
		>
			<div class="mb-6" style="width: 200px">
				<Logo />
			</div>
			<div
				class="text-h6 w-100 px-15 font-weight-regular auth-divider position-relative mb-6"
			>
				<span
					class="bg-surface px-5 py-3 position-relative text-subtitle-1 text-grey100"
				>
					Entre na sua conta
				</span>
			</div>
		</div>
	</div>
	<div>
		<v-row class="mb-3">
			<v-col v-if="generalError" cols="12">
				<v-alert type="error" variant="tonal" density="compact">
					{{ generalError }}
				</v-alert>
			</v-col>

			<v-col cols="12">
				<v-label class="font-weight-medium mb-1">Login:</v-label>
				<v-text-field
					v-model="login"
					variant="outlined"
					class="pwdInput"
					placeholder="usuario@dominio.com.br"
					:error-messages="[...loginErrors, ...loginFormatError]"
					color="primary"
					:disabled="auth.isLoading"
					hint="Digite seu login no formato: usuario@dominio.com.br"
					persistent-hint
				></v-text-field>
			</v-col>

			<v-col cols="12">
				<v-label class="font-weight-medium mb-1">Senha:</v-label>
				<v-text-field
					v-model="password"
					variant="outlined"
					class="border-borderColor"
					type="password"
					:error-messages="passwordErrors"
					color="primary"
					:disabled="auth.isLoading"
					@keyup.enter="submit"
				></v-text-field>
			</v-col>

			<!-- Habilitar e configurar se necessário -->
			<!-- <v-col cols="12" class="py-0">
        <div class="d-flex flex-wrap align-center w-100">
          <v-checkbox v-model="checkbox" hide-details color="primary" :disabled="loading">
            <template v-slot:label>Lembrar de mim</template>
          </v-checkbox>
          <div class="ml-sm-auto">
            <RouterLink
              to="/forgot-password"
              class="text-primary text-decoration-none text-body-1 opacity-1 font-weight-medium"
            >
              Esqueceu sua senha?
            </RouterLink>
          </div>
        </div>
      </v-col> -->

			<v-col cols="12">
				<v-btn
					size="large"
					color="primary"
					block
					:loading="auth.isLoading"
					@click="submit"
					flat
				>
					{{ auth.isLoading ? "Entrando..." : "Entrar" }}
				</v-btn>
			</v-col>
		</v-row>
	</div>
</template>
