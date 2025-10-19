<template>
  <v-container fluid class="management-container">
    <v-row>
      <v-col cols="12">
        <v-card class="management-card">
          <v-card-title class="management-title">            
            {{ isEdit ? 'Editar Interação' : 'Nova Interação' }}
          </v-card-title>
          
          <v-card-text class="management-form-container">
            <v-form ref="formRef" @submit.prevent="saveInteracao" class="management-form">
              <!-- Dados Básicos -->
              <v-row>
                <v-col cols="12">
                  <h3 class="section-title">Dados Básicos</h3>
                </v-col>
                <v-col cols="12" md="6">
                  <v-autocomplete
                    class="management-form-field"
                    v-model="form.cliente_id"
                    :items="clienteStore.clientes"
                    item-title="razao_social"
                    item-value="id"
                    label="Cliente *"
                    required
                    :rules="[rules.required]"
                    :loading="clienteStore.loading"
                    prepend-icon="mdi-account"
                  >
                    <template v-slot:item="{ props, item }">
                      <v-list-item v-bind="props" :title="item.raw.razao_social" :subtitle="item.raw.cnpj_cpf">
                        <template v-slot:prepend>
                          <v-avatar color="primary" size="small">
                            <span class="text-white">{{ item.raw.razao_social.charAt(0).toUpperCase() }}</span>
                          </v-avatar>
                        </template>
                      </v-list-item>
                    </template>
                  </v-autocomplete>
                </v-col>
                <v-col cols="12" md="6">
                  <v-autocomplete
                    class="management-form-field"
                    v-model="form.tipo_interacao_id"
                    :items="tipoInteracaoStore.tiposInteracao"
                    item-title="nome"
                    item-value="id"
                    label="Tipo de Interação *"
                    required
                    :rules="[rules.required]"
                    :loading="tipoInteracaoStore.loading"
                    prepend-icon="mdi-tag"
                  >
                    <template v-slot:no-data>
                      <v-list-item>
                        <v-list-item-title>
                          Nenhum tipo de interação cadastrado. Cadastre um tipo de interação para continuar.
                        </v-list-item-title>
                      </v-list-item>
                    </template>
                  </v-autocomplete>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select
                    class="management-form-field"
                    v-model="form.origem"
                    :items="origens"
                    label="Origem *"
                    required
                    :rules="[rules.required]"
                    prepend-icon="mdi-source-branch"
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    class="management-form-field"
                    v-model="form.titulo"
                    label="Título *"
                    required
                    :rules="[rules.required]"
                    prepend-icon="mdi-form-textbox"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    class="management-form-field"
                    v-model="form.data_interacao"
                    label="Data da Interação *"
                    type="date"
                    required
                    :rules="[rules.required]"
                    prepend-icon="mdi-calendar"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    class="management-form-field"
                    v-model="form.chave"
                    label="Chave"
                    prepend-icon="mdi-key"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    class="management-form-field"
                    v-model="valorInput"
                    label="Valor"
                    prepend-icon="mdi-currency-brl"
                    @blur="normalizeValor"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-autocomplete
                    class="management-form-field"
                    v-model="form.user_id"
                    :items="userStore.users"
                    item-title="name"
                    item-value="id"
                    label="Usuário"
                    :clearable="true"
                    prepend-icon="mdi-account-circle"
                  >
                    <template v-slot:item="{ props, item }">
                      <v-list-item v-bind="props" :title="item.raw.name" :subtitle="item.raw.email">
                        <template v-slot:prepend>
                          <v-avatar color="secondary" size="small">
                            <span class="text-white">{{ item.raw.name.charAt(0).toUpperCase() }}</span>
                          </v-avatar>
                        </template>
                      </v-list-item>
                    </template>
                  </v-autocomplete>
                </v-col>
                <v-col cols="12">
                  <v-textarea 
                    class="management-form-field" 
                    v-model="form.descricao" 
                    label="Descrição" 
                    rows="4"
                    :rules="[rules.minDescricao]"
                    :error-messages="descricaoErrors"
                    prepend-icon="mdi-text"
                  ></v-textarea>
                </v-col>
              </v-row>
              
              <v-row class="mt-6">
                <v-col cols="12" class="d-flex justify-space-between">
                  <v-btn 
                    class="management-action-btn edit-btn" 
                    @click="goBack"
                  >
                    <v-icon left>mdi-arrow-left</v-icon>
                    Voltar
                  </v-btn>                
                  <v-btn 
                    class="management-new-btn" 
                    type="submit"
                    :loading="interacaoStore.loading"
                  >
                    <v-icon left>mdi-content-save</v-icon>
                    Salvar
                  </v-btn>
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useInteracaoStore } from '@/modules/csi/stores/interacao';
import { useClienteStore } from '@/modules/csi/stores/cliente';
import { useUserStore } from '@/modules/plataforma/stores/user';
import type { Interacao } from '@/types/tenant';
import { INTERACAO_ORIGENS } from '@/modules/csi/types/interacao';
import { useTipoInteracaoStore } from '@/modules/csi/stores/tipoInteracao';

const route = useRoute();
const router = useRouter();
const interacaoStore = useInteracaoStore();
const clienteStore = useClienteStore();
const userStore = useUserStore();

type InteracaoForm = Omit<Interacao, 'tipo'> & { tipo_interacao_id?: number; titulo: string; chave?: string; valor?: number | undefined | null };

const formRef = ref();
const form = ref<InteracaoForm>({
  id: undefined,
  tenant_id: 0,
  cliente_id: 0,
  tipo_interacao_id: undefined,
  origem: '',
  titulo: '',
  descricao: '',
  data_interacao: '',
  chave: '',
  valor: 0,
  user_id: undefined
});

const isEdit = computed(() => !!route.params.id);

const tipoInteracaoStore = useTipoInteracaoStore();


const origens = INTERACAO_ORIGENS as unknown as string[];

const rules = {
  required: (value: string | number) => (!!value || value === 0) || 'Campo obrigatório',
  minDescricao: (value: string) => (!value || value.length >= 10) || 'A descrição deve ter pelo menos 10 caracteres'
};

const descricaoErrors = computed(() => {
  if (!form.value.descricao) return [] as string[];
  return form.value.descricao.length >= 10 ? [] : ['A descrição deve ter pelo menos 10 caracteres'];
});

async function loadInteracao() {
  if (isEdit.value && route.params.id) {
    const interacaoId = Number(route.params.id);
    try {
      await interacaoStore.getInteracaoById(interacaoId);
      if (interacaoStore.selectedInteracao) {
        const interacao = interacaoStore.selectedInteracao;
        form.value = {
          ...form.value,
          ...interacao,
          id: interacao.id,
          data_interacao: interacao.data_interacao 
            ? interacao.data_interacao.split('T')[0] 
            : ''
        };
        if (form.value.valor != null) {
          valorInput.value = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(form.value.valor));
        }
      }
    } catch (error) {
      router.push('/csi/interacoes');
    }
  }
}

async function saveInteracao() {
  const { valid } = await formRef.value.validate();
  
  if (!valid) {
    return;
  }

  try {
    // Remove campos vazios para não enviar nulls desnecessários
    const dataToSend = Object.fromEntries(
      Object.entries(form.value).filter(([_, value]) => value !== '' && value !== null && value !== undefined)
    );

    if (isEdit.value && form.value.id) {
      await interacaoStore.updateInteracao(form.value.id, dataToSend);
    } else {
      await interacaoStore.createInteracao(dataToSend);
    }
    
    if (!interacaoStore.error) {
      router.push('/csi/interacoes');
    }
  } catch (error) {
  }
}

function goBack() {
  router.push('/csi/interacoes');
}

onMounted(async () => {
  // Carrega as listas necessárias
  await Promise.all([
    clienteStore.fetchClientes(),
    userStore.fetchUsers(),
    tipoInteracaoStore.fetchTiposInteracao()
  ]);
  // Carrega a interação se estiver editando
  await loadInteracao();
});

// Valor - formatação pt-BR
const valorInput = ref('');

function normalizeValor() {
  const numeric = Number(valorInput.value.replace(/\./g, '').replace(',', '.'));
  if (!isNaN(numeric)) {
    form.value.valor = Number(numeric.toFixed(2));
    valorInput.value = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(form.value.valor as number);
  } else {
    form.value.valor = undefined as any;
    valorInput.value = '';
  }
}
</script>

<style scoped>
/* Estilos específicos do formulário de interação */
.management-form-container {
  padding: 32px !important;
  background: #fafafa;
}

.management-form {
  background: white;
  padding: 32px;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.section-title {
  color: #333;
  font-weight: 600;
  margin-bottom: 16px;
  padding-bottom: 8px;
  border-bottom: 2px solid #e0e0e0;
}
</style>
