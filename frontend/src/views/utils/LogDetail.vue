<template>
    <v-container fluid class="log-detail-container">
      <!-- Header com navegação e ações -->
      <v-row class="mb-4">
        <v-col cols="12">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-btn 
                icon 
                variant="text"
                @click="$router.go(-1)"
                class="mr-3"
              >
                <v-icon>mdi-arrow-left</v-icon>
              </v-btn>
              <v-icon color="primary" class="mr-2">mdi-file-document-outline</v-icon>
              <span class="text-h5">Detalhes do Log</span>
              
              <v-spacer></v-spacer>
              
              <!-- Botões de ação -->
              <div class="d-flex" v-if="log">
                <v-btn 
                  variant="outlined"
                  color="primary"
                  size="default"
                  @click="refreshLog"
                  :loading="loading"
                  class="mr-2"
                >
                  <v-icon left>mdi-refresh</v-icon>
                  Atualizar
                </v-btn>
                
                <v-btn 
                  variant="outlined"
                  color="grey-darken-1"
                  size="default"
                  @click="exportLog"
                  class="mr-2"
                >
                  <v-icon left>mdi-download</v-icon>
                  Exportar
                </v-btn>
                
                <v-btn 
                  variant="outlined"
                  color="error"
                  size="default"
                  @click="deleteLog"
                  :loading="loading"
                >
                  <v-icon left>mdi-delete</v-icon>
                  Excluir
                </v-btn>
              </div>
            </v-card-title>
          </v-card>
        </v-col>
      </v-row>
  
      <!-- Loading -->
      <v-row v-if="loading">
        <v-col cols="12">
          <v-card>
            <v-card-text class="text-center">
              <v-progress-circular indeterminate color="primary"></v-progress-circular>
              <p class="mt-3">Carregando detalhes do log...</p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
  
      <!-- Erro -->
      <v-row v-else-if="error">
        <v-col cols="12">
          <v-card>
            <v-card-text class="text-center">
              <v-icon size="48" color="error">mdi-alert-circle</v-icon>
              <h3 class="mt-3">Erro ao carregar log</h3>
              <p>{{ error }}</p>
              <v-btn color="primary" @click="fetchLogDetail" class="mt-3">
                Tentar Novamente
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
  
      <!-- Conteúdo do Log -->
      <v-row v-else-if="log">
        <!-- Informações Básicas -->
        <v-col cols="12" md="6">
          <v-card elevation="3" class="info-card-modern">
            <v-card-title class="card-title-modern">
              <v-icon color="primary" class="mr-2">mdi-information</v-icon>
              <span>Informações Básicas</span>
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text class="pa-0">
              <div class="info-item">
                <div class="info-label">
                  <v-icon size="small" color="grey-darken-1">mdi-identifier</v-icon>
                  <span>ID do Log</span>
                </div>
                <div class="info-value">{{ log.id }}</div>
              </div>
              
              <div class="info-item">
                <div class="info-label">
                  <v-icon size="small" color="grey-darken-1">mdi-cog</v-icon>
                  <span>Ação</span>
                </div>
                <div class="info-value">
                  <v-chip 
                    :color="getActionColor(log.action)"
                    size="small"
                    variant="flat"
                  >
                    {{ log.action }}
                  </v-chip>
                </div>
              </div>
              
              <div class="info-item">
                <div class="info-label">
                  <v-icon size="small" color="grey-darken-1">mdi-tag</v-icon>
                  <span>Tipo</span>
                </div>
                <div class="info-value">
                  <v-chip 
                    :color="getTypeColor(log.log_type)"
                    size="small"
                    variant="flat"
                  >
                    {{ getTypeLabel(log.log_type) }}
                  </v-chip>
                </div>
              </div>
              
              <div class="info-item">
                <div class="info-label">
                  <v-icon size="small" color="grey-darken-1">mdi-calendar-plus</v-icon>
                  <span>Data de Criação</span>
                </div>
                <div class="info-value">{{ formatDateTime(log.created_at) }}</div>
              </div>
              
              <div class="info-item">
                <div class="info-label">
                  <v-icon size="small" color="grey-darken-1">mdi-calendar-edit</v-icon>
                  <span>Última Atualização</span>
                </div>
                <div class="info-value">{{ formatDateTime(log.updated_at) }}</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
  
        <!-- Informações do Usuário e Tenant -->
        <v-col cols="12" md="6">
          <v-card elevation="3" class="info-card-modern">
            <v-card-title class="card-title-modern">
              <v-icon color="primary" class="mr-2">mdi-account</v-icon>
              <span>Usuário e Tenant</span>
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text class="pa-0">
              <div class="info-item">
                <div class="info-label">
                  <v-icon size="small" color="grey-darken-1">mdi-account-box</v-icon>
                  <span>Usuário ID</span>
                </div>
                <div class="info-value">{{ log.user_id || 'N/A' }}</div>
              </div>
              
              <div class="info-item">
                <div class="info-label">
                  <v-icon size="small" color="grey-darken-1">mdi-account-circle</v-icon>
                  <span>Nome do Usuário</span>
                </div>
                <div class="info-value">{{ log.user?.name || 'N/A' }}</div>
              </div>
              
              <div class="info-item">
                <div class="info-label">
                  <v-icon size="small" color="grey-darken-1">mdi-email</v-icon>
                  <span>Email do Usuário</span>
                </div>
                <div class="info-value">{{ log.user?.email || 'N/A' }}</div>
              </div>
              
              <div class="info-item">
                <div class="info-label">
                  <v-icon size="small" color="grey-darken-1">mdi-office-building</v-icon>
                  <span>Tenant ID</span>
                </div>
                <div class="info-value">{{ log.tenant_id || 'N/A' }}</div>
              </div>
              
              <div class="info-item">
                <div class="info-label">
                  <v-icon size="small" color="grey-darken-1">mdi-domain</v-icon>
                  <span>Nome do Tenant</span>
                </div>
                <div class="info-value">{{ log.tenant?.nome || 'N/A' }}</div>
              </div>
              
              <div class="info-item">
                <div class="info-label">
                  <v-icon size="small" color="grey-darken-1">mdi-ip-network</v-icon>
                  <span>IP Address</span>
                </div>
                <div class="info-value">{{ log.ip_address || 'N/A' }}</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
  
        <!-- Descrição -->
        <v-col cols="12" v-if="log.description">
          <v-card elevation="3" class="info-card-modern">
            <v-card-title class="card-title-modern">
              <v-icon color="primary" class="mr-2">mdi-text</v-icon>
              <span>Descrição</span>
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text class="pa-6">
              <div class="description-box">
                <v-icon color="grey-darken-1" class="mr-3">mdi-text-box</v-icon>
                <span class="description-content">{{ log.description }}</span>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
  
        <!-- Conteúdo Detalhado -->
        <v-col cols="12" v-if="log.content">
          <v-card elevation="3" class="info-card-modern">
            <v-card-title class="card-title-modern">
              <v-icon color="primary" class="mr-2">mdi-code-json</v-icon>
              <span>Conteúdo Detalhado</span>
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text class="pa-4">
              <v-sheet color="grey-lighten-5" rounded="lg" class="pa-4">
                <pre class="content-pre">{{ formatContent(log.content) }}</pre>
              </v-sheet>
            </v-card-text>
          </v-card>
        </v-col>

      </v-row>
    </v-container>
  </template>
  
  <script setup lang="ts">
  import { ref, onMounted } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { useLogStore } from '@/modules/plataforma/stores/log';
  
  const route = useRoute();
  const router = useRouter();
  const logStore = useLogStore();
  
  const loading = ref(false);
  const error = ref<string | null>(null);
  const log = ref<any>(null);
  
  // Buscar detalhes do log
  async function fetchLogDetail() {
    loading.value = true;
    error.value = null;
    
    try {
      const logId = route.params.id;
      await logStore.getLogById(Number(logId));
      log.value = logStore.selectedLog;
      
      if (!log.value) {
        error.value = 'Log não encontrado';
      }
    } catch (err: any) {
      error.value = err.message || 'Erro ao carregar log';
    } finally {
      loading.value = false;
    }
  }
  
  // Atualizar log
  async function refreshLog() {
    await fetchLogDetail();
  }
  
  // Excluir log
  async function deleteLog() {
    if (!log.value) return;
    
    if (confirm('Deseja realmente excluir este log?')) {
      loading.value = true;
      try {
        await logStore.deleteLog(log.value.id);
        router.push('/logs');
      } catch (err: any) {
        error.value = err.message || 'Erro ao excluir log';
      } finally {
        loading.value = false;
      }
    }
  }
  
  // Exportar log (sem dados sensíveis - LGPD)
  function exportLog() {
    if (!log.value) return;
    
    // Criar cópia sanitizada do usuário (sem e-mail)
    const sanitizedUser = log.value.user ? {
      id: log.value.user.id,
      name: log.value.user.name,
      usuario: log.value.user.usuario
      // email: removido por conformidade com LGPD
    } : null;
    
    const data = {
      id: log.value.id,
      action: log.value.action,
      description: log.value.description,
      log_type: log.value.log_type,
      status: log.value.status,
      content: log.value.content,
      user: sanitizedUser,
      tenant: log.value.tenant,
      ip_address: log.value.ip_address,
      created_at: log.value.created_at,
      updated_at: log.value.updated_at
    };
    
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `log-${log.value.id}.json`;
    a.click();
    URL.revokeObjectURL(url);
  }
  
  // Formatação de data
  function formatDateTime(dateString: string) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    });
  }
  
  // Formatação de conteúdo
  function formatContent(content: string) {
    try {
      const parsed = JSON.parse(content);
      return JSON.stringify(parsed, null, 2);
    } catch {
      return content;
    }
  }
  
  // Cores para ações
  function getActionColor(action: string) {
    const actionLower = action?.toLowerCase() || '';
    
    if (actionLower.includes('create') || actionLower.includes('criar')) {
      return 'green';
    } else if (actionLower.includes('update') || actionLower.includes('edit')) {
      return 'orange';
    } else if (actionLower.includes('delete') || actionLower.includes('exclu')) {
      return 'red';
    } else if (actionLower.includes('login') || actionLower.includes('auth')) {
      return 'blue';
    } else {
      return 'grey';
    }
  }
  
  // Cores para tipos
  function getTypeColor(type: string) {
    switch (type) {
      case 'info': return 'blue';
      case 'error': return 'red';
      case 'warning': return 'orange';
      case 'debug': return 'purple';
      default: return 'grey';
    }
  }
  
  // Labels para tipos
  function getTypeLabel(type: string) {
    switch (type) {
      case 'info': return 'Informativo';
      case 'error': return 'Erro';
      case 'warning': return 'Aviso';
      case 'debug': return 'Debug';
      default: return 'Desconhecido';
    }
  }
  
  // Cores para status
  function getStatusColor(status: string) {
    switch (status) {
      case 'active': return 'green';
      case 'inactive': return 'grey';
      case 'processed': return 'blue';
      default: return 'grey';
    }
  }
  
  // Labels para status
  function getStatusLabel(status: string) {
    switch (status) {
      case 'active': return 'Ativo';
      case 'inactive': return 'Inativo';
      case 'processed': return 'Processado';
      default: return 'Desconhecido';
    }
  }
  
  // Lifecycle
  onMounted(() => {
    fetchLogDetail();
  });
  </script>
  
  <style scoped>
  .log-detail-container {
    padding: 24px;
  }

  /* Cards modernos */
  .info-card-modern {
    border-radius: 12px !important;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  .info-card-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
  }

  .info-card-modern .v-card-text {
    flex: 1;
  }

  .card-title-modern {
    background: white;
    padding: 20px 24px !important;
    font-size: 1.125rem !important;
    font-weight: 600 !important;
    display: flex;
    align-items: center;
  }

  /* Item de informação */
  .info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    border-bottom: 1px solid #f5f5f5;
    transition: background-color 0.2s ease;
  }

  .info-item:last-child {
    border-bottom: none;
  }

  .info-item:hover {
    background-color: #fafafa;
  }

  .info-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #616161;
    min-width: 180px;
  }

  .info-value {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #212121;
    text-align: right;
  }

  /* Descrição */
  .description-box {
    display: flex;
    align-items: flex-start;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid #2196F3;
  }

  .description-content {
    font-size: 1rem;
    line-height: 1.7;
    color: #424242;
  }

  /* Conteúdo detalhado */
  .content-pre {
    font-family: 'Courier New', 'Consolas', monospace;
    font-size: 0.875rem;
    line-height: 1.6;
    color: #37474f;
    margin: 0;
    white-space: pre-wrap;
    word-wrap: break-word;
    max-height: 400px;
    overflow-y: auto;
  }

  .content-pre::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }

  .content-pre::-webkit-scrollbar-track {
    background: #e0e0e0;
    border-radius: 4px;
  }

  .content-pre::-webkit-scrollbar-thumb {
    background: #9e9e9e;
    border-radius: 4px;
  }

  .content-pre::-webkit-scrollbar-thumb:hover {
    background: #757575;
  }
  </style>