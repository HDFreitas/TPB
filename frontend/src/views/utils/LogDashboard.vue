<template>
    <v-container fluid class="dashboard-container">
      <!-- Header do Dashboard -->
      <v-row class="mb-4">
        <v-col cols="12">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon color="primary" class="mr-2">mdi-chart-line</v-icon>
              <span class="text-h5">Dashboard de Logs</span>
              <v-spacer></v-spacer>
              <v-btn 
                variant="outlined" 
                color="primary" 
                @click="refreshDashboard"
                :loading="loading"
              >
                <v-icon left>mdi-refresh</v-icon>
                Atualizar
              </v-btn>
            </v-card-title>
          </v-card>
        </v-col>
      </v-row>
  
      <!-- Cards de Estatísticas -->
      <v-row>
        <!-- Total de Logs -->
        <v-col cols="12" md="3">
          <v-card elevation="2" class="stat-card">
            <v-card-text>
              <div class="d-flex align-center mb-3">
                <v-avatar color="blue-lighten-4" size="56">
                  <v-icon color="blue-darken-2" size="32">mdi-file-document-multiple</v-icon>
                </v-avatar>
                <div class="ml-4">
                  <div class="text-h4 font-weight-bold">{{ stats.total_logs || 0 }}</div>
                  <div class="text-subtitle-2 text-medium-emphasis">Total de Logs</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
  
        <!-- Logs Últimas 24h -->
        <v-col cols="12" md="3">
          <v-card elevation="2" class="stat-card">
            <v-card-text>
              <div class="d-flex align-center mb-3">
                <v-avatar color="green-lighten-4" size="56">
                  <v-icon color="green-darken-2" size="32">mdi-clock-outline</v-icon>
                </v-avatar>
                <div class="ml-4">
                  <div class="text-h4 font-weight-bold">{{ stats.logs_last_24h || 0 }}</div>
                  <div class="text-subtitle-2 text-medium-emphasis">Últimas 24h</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
  
        <!-- Logs de Erro -->
        <v-col cols="12" md="3">
          <v-card elevation="2" class="stat-card">
            <v-card-text>
              <div class="d-flex align-center mb-3">
                <v-avatar color="red-lighten-4" size="56">
                  <v-icon color="red-darken-2" size="32">mdi-alert-circle</v-icon>
                </v-avatar>
                <div class="ml-4">
                  <div class="text-h4 font-weight-bold">{{ stats.error_logs || 0 }}</div>
                  <div class="text-subtitle-2 text-medium-emphasis">Logs de Erro</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
  
        <!-- Erros Últimas 24h -->
        <v-col cols="12" md="3">
          <v-card elevation="2" class="stat-card">
            <v-card-text>
              <div class="d-flex align-center mb-3">
                <v-avatar color="orange-lighten-4" size="56">
                  <v-icon color="orange-darken-2" size="32">mdi-alert</v-icon>
                </v-avatar>
                <div class="ml-4">
                  <div class="text-h4 font-weight-bold">{{ stats.error_logs_last_24h || 0 }}</div>
                  <div class="text-subtitle-2 text-medium-emphasis">Erros 24h</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
  
      <!-- Gráficos -->
      <v-row class="mt-4">
        <!-- Logs por Tipo - Gráfico de Pizza -->
        <v-col cols="12" md="6">
          <v-card elevation="3" class="modern-card chart-height">
            <v-card-title class="d-flex align-center">
              <v-icon color="primary" class="mr-2">mdi-chart-donut</v-icon>
              <span>Distribuição por Tipo</span>
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text class="pa-4">
              <div v-if="Object.keys(stats.logs_by_type || {}).length > 0" class="d-flex align-center justify-center">
                <div class="type-chart-container">
                  <canvas ref="typeChartCanvas"></canvas>
                </div>
              </div>
              <div v-else class="text-center py-12">
                <v-icon size="64" color="grey-lighten-1">mdi-chart-donut</v-icon>
                <div class="text-body-2 text-medium-emphasis mt-2">Nenhum log registrado ainda</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
  
        <!-- Top 10 Ações - Lista Simples -->
        <v-col cols="12" md="6">
          <v-card elevation="3" class="modern-card chart-height">
            <v-card-title class="d-flex align-center">
              <v-icon color="primary" class="mr-2">mdi-format-list-numbered</v-icon>
              <span>Top 10 Ações Mais Frequentes</span>
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text class="pa-0 actions-list-container">
              <v-list v-if="Object.keys(stats.logs_by_action || {}).length > 0" bg-color="transparent" class="py-0">
                <v-list-item
                  v-for="(count, action, index) in stats.logs_by_action"
                  :key="action"
                  class="action-item-bw px-6 py-4"
                >
                  <template v-slot:prepend>
                    <div class="rank-number mr-4">
                      {{ index + 1 }}
                    </div>
                  </template>
                  <v-list-item-title class="text-body-1 font-weight-medium text-grey-darken-3">
                    {{ formatActionName(action) }}
                  </v-list-item-title>
                  <template v-slot:append>
                    <div class="count-badge">
                      <span class="count-number">{{ count }}</span>
                      <span class="count-label">logs</span>
                    </div>
                  </template>
                </v-list-item>
              </v-list>
              <div v-else class="text-center py-12">
                <v-icon size="64" color="grey-lighten-1">mdi-format-list-numbered-off</v-icon>
                <div class="text-body-2 text-medium-emphasis mt-2">Nenhuma ação registrada ainda</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
  
      <!-- Gráfico de Tendência Temporal - Últimas 24h -->
      <v-row class="mt-4">
        <v-col cols="12">
          <v-card elevation="3" class="modern-card">
            <v-card-title class="d-flex align-center">
              <v-icon color="primary" class="mr-2">mdi-chart-timeline-variant</v-icon>
              <span>Volume de Logs - Últimas 24 Horas</span>
              <v-spacer></v-spacer>
              <v-chip color="primary" variant="outlined" size="small">
                <v-icon start size="small">mdi-clock-outline</v-icon>
                Atualização em tempo real
              </v-chip>
            </v-card-title>
            <v-divider></v-divider>

            <v-card-text class="pa-6">
              <v-sheet color="white" rounded="lg" class="pa-6 elevation-2">
                <div class="text-subtitle-2 text-medium-emphasis mb-4">
                  Volume de Logs por Hora
                </div>
              <div class="chart-container">
                  <canvas ref="trendChartCanvas"></canvas>
                </div>
              </v-sheet>

              <!-- Legenda das horas -->
              <v-row class="mt-4 px-2">
                <v-col cols="12" class="d-flex justify-space-between align-center">
                  <div class="text-caption text-medium-emphasis">
                    <v-icon size="small" color="grey">mdi-arrow-left</v-icon>
                    {{ get24HoursAgo() }}
                  </div>
                  <div class="text-caption text-medium-emphasis">
                    <v-icon size="small" color="grey">mdi-clock</v-icon>
                    Agora: {{ getCurrentTime() }}
              </div>
                </v-col>
              </v-row>

              <!-- Estatísticas rápidas -->
              <v-row class="mt-4">
                <v-col cols="4" class="text-center">
                  <div class="text-h5 font-weight-bold text-primary">{{ getTodayTotal() }}</div>
                  <div class="text-caption text-medium-emphasis">Total 24h</div>
                </v-col>
                <v-col cols="4" class="text-center">
                  <div class="text-h5 font-weight-bold text-success">{{ getMaxHourlyLogs() }}</div>
                  <div class="text-caption text-medium-emphasis">Pico</div>
                </v-col>
                <v-col cols="4" class="text-center">
                  <div class="text-h5 font-weight-bold text-info">{{ getAverageHourlyLogs() }}</div>
                  <div class="text-caption text-medium-emphasis">Média/Hora</div>
                </v-col>
              </v-row>
            </v-card-text>

            <v-divider></v-divider>

          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </template>
  
  <script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
  import { useLogStore } from '@/modules/plataforma/stores/log';
import { Chart, ChartConfiguration, registerables } from 'chart.js';

Chart.register(...registerables);

// Interface para stats
interface DashboardStats {
  total_logs: number;
  logs_last_24h: number;
  error_logs: number;
  error_logs_last_24h: number;
  logs_by_type: Record<string, number>;
  logs_by_action: Record<string, number>;
}
  
  const logStore = useLogStore();
  const loading = ref(false);
  const stats = ref<DashboardStats>({
  total_logs: 0,
  logs_last_24h: 0,
  error_logs: 0,
  error_logs_last_24h: 0,
  logs_by_type: {},
  logs_by_action: {}
});
  
  // Dados para o gráfico de tendência (24 horas)
  const hourlyTrendData = ref<number[]>(Array(24).fill(0));
  const hourlyLabels = ref<string[]>([]);
  
  // Referências
  const trendChartCanvas = ref<HTMLCanvasElement | null>(null);
  const typeChartCanvas = ref<HTMLCanvasElement | null>(null);
  let trendChartInstance: Chart | null = null;
  let typeChartInstance: Chart | null = null;
  
  // Labels com indicador de dia (Hoje/Ontem) para exibição
  const hourlyLabelsWithDay = computed(() => {
    if (hourlyLabels.value.length === 0) return [];
    
    const now = new Date();
    return hourlyLabels.value.map((label, index) => {
      const hoursAgo = 23 - index;
      const hourDate = new Date(now.getTime() - (hoursAgo * 60 * 60 * 1000));
      const isToday = hourDate.toDateString() === now.toDateString();
      
      // Mostrar apenas a hora, simplificado
      return label;
    });
  });
  
  // Buscar dados do dashboard
  async function fetchDashboardData() {
    loading.value = true;
    try {
      await logStore.fetchDashboardStats();
      stats.value = logStore.dashboardStats;
      
      // Buscar dados de tendência por hora
      await fetchHourlyTrend();
      
      // Atualizar gráfico de tipos
      updateTypeChart();
    } catch (error) {
      console.error('Erro ao buscar dados do dashboard:', error);
    } finally {
      loading.value = false;
    }
  }
  
  // Buscar dados de tendência por hora do backend
  async function fetchHourlyTrend() {
    try {
      const response = await logStore.fetchHourlyTrend();
      if (response && response.data && response.labels) {
        // Dados já vêm na ordem correta do backend: passado (esquerda) → presente (direita)
        hourlyTrendData.value = response.data;
        hourlyLabels.value = response.labels;
        
        // Atualizar gráfico Chart.js
    updateTrendChart();
      }
    } catch (error) {
      console.error('Erro ao buscar tendência horária:', error);
      // Em caso de erro, usar dados zerados
      hourlyTrendData.value = Array(24).fill(0);
      hourlyLabels.value = Array(24).fill(0).map((_, index) => {
        const hour = (new Date().getHours() - (23 - index) + 24) % 24;
        return `${hour.toString().padStart(2, '0')}h`;
      });
    }
  }
  
  // Criar/Atualizar gráfico Chart.js
  function updateTrendChart() {
    if (!trendChartCanvas.value) return;
    
    const ctx = trendChartCanvas.value.getContext('2d');
    if (!ctx) return;
    
    // Destruir gráfico anterior se existir
    if (trendChartInstance) {
      trendChartInstance.destroy();
    }
    
    // Criar gradiente
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(25, 118, 210, 0.8)');
    gradient.addColorStop(0.5, 'rgba(25, 118, 210, 0.4)');
    gradient.addColorStop(1, 'rgba(25, 118, 210, 0.05)');
    
    // Configuração do gráfico
    const config: ChartConfiguration = {
      type: 'line',
      data: {
        labels: hourlyLabels.value,
        datasets: [{
          label: 'Logs por hora',
          data: hourlyTrendData.value,
          fill: true,
          backgroundColor: gradient,
          borderColor: '#1976D2',
          borderWidth: 2.5,
          tension: 0.4,
          pointRadius: 4,
          pointBackgroundColor: '#1976D2',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointHoverRadius: 6,
          pointHoverBackgroundColor: '#1976D2',
          pointHoverBorderColor: '#fff',
          pointHoverBorderWidth: 3,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            mode: 'index',
            intersect: false,
            backgroundColor: 'rgba(25, 118, 210, 0.95)',
            titleColor: '#fff',
            bodyColor: '#fff',
            titleFont: {
              size: 13,
              weight: 'bold'
            },
            bodyFont: {
              size: 14
            },
            padding: 12,
            borderColor: '#1976D2',
            borderWidth: 2,
            callbacks: {
              title: (context) => {
                const index = context[0].dataIndex;
                return getFullLabelForIndex(index);
              },
              label: (context) => {
                return `${context.parsed.y} logs`;
              }
            }
          }
        },
        scales: {
          x: {
            grid: {
              display: false
            },
            ticks: {
              color: '#757575',
              font: {
                size: 11,
                weight: '500'
              }
            }
          },
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)',
              drawBorder: false
            },
            ticks: {
              color: '#757575',
              font: {
                size: 11
              },
              stepSize: 1
            }
          }
        },
        interaction: {
          mode: 'nearest',
          axis: 'x',
          intersect: false
        }
      }
    };
    
    // Criar nova instância
    trendChartInstance = new Chart(ctx, config);
  }
  
  // Criar/Atualizar gráfico de pizza (Tipos de Log)
  function updateTypeChart() {
    if (!typeChartCanvas.value) return;
    
    const ctx = typeChartCanvas.value.getContext('2d');
    if (!ctx) return;
  
    // Destruir gráfico anterior se existir
    if (typeChartInstance) {
      typeChartInstance.destroy();
    }
  
    const typeData = stats.value.logs_by_type || {};
    const types = Object.keys(typeData);
    const counts = Object.values(typeData);
    
    if (types.length === 0) return;
    
    // Cores para cada tipo
    const backgroundColors = types.map(type => getTypeColor(type));
    const hoverColors = types.map(type => {
      const color = getTypeColor(type);
      // Escurecer 10% no hover
      return color.replace(')', ', 0.8)').replace('rgb', 'rgba');
    });
    
    // Configuração do gráfico
    const config: ChartConfiguration = {
      type: 'doughnut',
        data: {
        labels: types.map(type => getTypeLabel(type)),
          datasets: [{
          data: counts,
          backgroundColor: backgroundColors,
          hoverBackgroundColor: hoverColors,
          borderWidth: 3,
          borderColor: '#fff',
          hoverBorderWidth: 4,
          hoverBorderColor: '#fff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
            display: true,
            position: 'right',
            labels: {
              padding: 15,
              font: {
                size: 13,
                weight: '500'
              },
              color: '#424242',
              usePointStyle: true,
              pointStyle: 'circle',
              generateLabels: (chart) => {
                const data = chart.data;
                if (data.labels.length && data.datasets.length) {
                  return data.labels.map((label, i) => {
                    const value = data.datasets[0].data[i];
                    const percentage = getPercentage(value as number, stats.value.total_logs);
                    return {
                      text: `${label}: ${value} (${percentage.toFixed(1)}%)`,
                      fillStyle: backgroundColors[i],
                      hidden: false,
                      index: i
                    };
                  });
                }
                return [];
              }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            titleFont: {
              size: 14,
              weight: 'bold'
            },
            bodyFont: {
              size: 13
            },
            padding: 12,
            borderColor: 'rgba(255, 255, 255, 0.3)',
            borderWidth: 1,
            callbacks: {
              label: (context) => {
                const label = context.label || '';
                const value = context.parsed;
                const percentage = getPercentage(value, stats.value.total_logs);
                return `${label}: ${value} logs (${percentage.toFixed(1)}%)`;
              }
            }
          }
        },
        cutout: '60%',
        animation: {
          animateRotate: true,
          animateScale: true
        }
      }
    };
    
    // Criar nova instância
    typeChartInstance = new Chart(ctx, config);
  }
  
  // Obter hora atual formatada
  function getCurrentTime(): string {
    const now = new Date();
    return now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
  }
  
  // Obter hora de 24 horas atrás
  function get24HoursAgo(): string {
    const date = new Date();
    date.setHours(date.getHours() - 24);
    return date.toLocaleString('pt-BR', { 
      day: '2-digit', 
      month: '2-digit', 
      hour: '2-digit', 
      minute: '2-digit' 
    });
  }
  
  // Obter total de logs do dia
  function getTodayTotal(): number {
    return hourlyTrendData.value.reduce((a, b) => a + b, 0);
  }
  
  // Obter máximo de logs por hora
  function getMaxHourlyLogs(): number {
    return Math.max(...hourlyTrendData.value, 0);
  }
  
  // Obter média de logs por hora
  function getAverageHourlyLogs(): number {
    const sum = hourlyTrendData.value.reduce((a, b) => a + b, 0);
    return Math.floor(sum / 24);
  }
  
  // Obter label completo com hora e dia para o tooltip
  function getFullLabelForIndex(index: number): string {
    if (index < 0 || index >= hourlyLabels.value.length) return '';
    
    const now = new Date();
    const hoursAgo = 23 - index;
    const hourDate = new Date(now.getTime() - (hoursAgo * 60 * 60 * 1000));
    const isToday = hourDate.toDateString() === now.toDateString();
    const isNow = index === 23;
    
    const hourLabel = hourlyLabels.value[index];
    const dayLabel = isNow ? 'Agora' : (isToday ? 'Hoje' : 'Ontem');
    
    return `${hourLabel} - ${dayLabel}`;
  }
  
  // Obter labels para o eixo X (6 pontos principais)
  function getAxisLabels() {
    if (hourlyLabels.value.length === 0) return [];
    
    const now = new Date();
    const labels = [];
    
    // Selecionar 6 pontos ao longo do eixo (início, meio, fim)
    const indices = [0, 5, 10, 15, 20, 23];
    
    indices.forEach(index => {
      if (index < hourlyLabels.value.length) {
        const hoursAgo = 23 - index;
        const hourDate = new Date(now.getTime() - (hoursAgo * 60 * 60 * 1000));
        const isToday = hourDate.toDateString() === now.toDateString();
        const isNow = index === 23;
        
        labels.push({
          hour: hourlyLabels.value[index],
          day: isNow ? 'Agora' : (isToday ? 'Hoje' : 'Ontem')
        });
      }
    });
    
    return labels;
  }
  
  // Calcular porcentagem
  function getPercentage(value: number, total: number): number {
    if (total === 0) return 0;
    return (value / total) * 100;
  }
  
  // Obter cor para tipo de log
  function getTypeColor(type: string): string {
    const colors: Record<string, string> = {
      info: '#4CAF50',      // Verde
      error: '#F44336',     // Vermelho
      warning: '#FF9800',   // Laranja
      debug: '#2196F3'      // Azul
    };
    return colors[type] || '#9E9E9E';
  }
  
  // Obter label para tipo de log
  function getTypeLabel(type: string): string {
    const labels: Record<string, string> = {
      info: 'Info',
      error: 'Erro',
      warning: 'Aviso',
      debug: 'Debug'
    };
    return labels[type] || type;
  }
  
  // Obter cor para ação (gradiente baseado no index)
  function getActionColor(index: number): string {
    const colors = [
      '#1976D2', // Azul escuro
      '#42A5F5', // Azul médio
      '#64B5F6', // Azul claro
      '#4CAF50', // Verde
      '#66BB6A', // Verde claro
      '#FF9800', // Laranja
      '#FFA726', // Laranja claro
      '#F44336', // Vermelho
      '#EF5350', // Vermelho claro
      '#9C27B0'  // Roxo
    ];
    return colors[index % colors.length];
  }
  
  // Formatar nome da ação para exibição
  function formatActionName(action: string): string {
    if (!action) return '';
    
    // Mapeamento de operações CRUD
    const operationMap: Record<string, string> = {
      'CREATE': 'Criação',
      'UPDATE': 'Atualização',
      'EDIT': 'Edição',
      'DELETE': 'Exclusão',
      'REMOVE': 'Remoção',
      'VIEW': 'Visualização',
      'LIST': 'Listagem',
      'SEARCH': 'Busca',
      'LOGIN': 'Login',
      'LOGOUT': 'Logout',
      'AUTH': 'Autenticação',
      'REGISTER': 'Registro',
      'EXPORT': 'Exportação',
      'IMPORT': 'Importação',
      'SYNC': 'Sincronização',
      'SEND': 'Envio',
      'RECEIVE': 'Recebimento'
    };
    
    // Mapeamento de entidades
    const entityMap: Record<string, string> = {
      'USUARIO': 'Usuário',
      'USUARIOS': 'Usuários',
      'USER': 'Usuário',
      'PERFIL': 'Perfil',
      'PERFIS': 'Perfis',
      'TENANT': 'Tenant',
      'TENANTS': 'Tenants',
      'CLIENTE': 'Cliente',
      'CLIENTES': 'Clientes',
      'INTERACAO': 'Interação',
      'INTERACOES': 'Interações',
      'CONECTOR': 'Conector',
      'CONECTORES': 'Conectores',
      'LOG': 'Log',
      'LOGS': 'Logs',
      'PERMISSION': 'Permissão',
      'PERMISSIONS': 'Permissões',
      'ROLE': 'Função',
      'ROLES': 'Funções'
    };
    
    // Dividir por underscore ou espaço
    const parts = action.split(/[_\s]+/);
    
    // Formatar cada parte
    const formatted = parts.map(part => {
      const upperPart = part.toUpperCase();
      
      // Verificar se é uma operação conhecida
      if (operationMap[upperPart]) {
        return operationMap[upperPart];
      }
      
      // Verificar se é uma entidade conhecida
      if (entityMap[upperPart]) {
        return 'de ' + entityMap[upperPart];
      }
      
      // Capitalizar primeira letra
      return part.charAt(0).toUpperCase() + part.slice(1).toLowerCase();
    });
    
    return formatted.join(' ').replace(/\s+de\s+de\s+/g, ' de ');
  }
  // Atualizar dashboard
  function refreshDashboard() {
    fetchDashboardData();
  }
  
  // Lifecycle
  onMounted(() => {
    fetchDashboardData();
  });
  
  onBeforeUnmount(() => {
    if (trendChartInstance) {
      trendChartInstance.destroy();
    }
    if (typeChartInstance) {
      typeChartInstance.destroy();
    }
  });
  
  </script>
  
  <style scoped>
  .dashboard-container {
    padding: 24px;
  }
  
  /* Container do gráfico Chart.js - ALTURA FIXA */
  .chart-container {
    position: relative;
    height: 250px;
    width: 100%;
  }
  
  .chart-container canvas {
    max-height: 250px !important;
  }
  
  /* Container do gráfico de pizza (Tipos) */
  .type-chart-container {
    position: relative;
    height: 300px;
    width: 100%;
    max-width: 500px;
  }
  
  .type-chart-container canvas {
    max-height: 300px !important;
  }
  
  /* Cards com animações */
  .stat-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    animation: fadeInUp 0.5s ease-out;
  }
  
  .stat-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 12px 24px rgba(0,0,0,0.15) !important;
  }
  
  .modern-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    animation: fadeInUp 0.6s ease-out;
    overflow: hidden;
  }
  
  .modern-card:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
  }
  
  /* Altura uniforme para gráficos */
  .chart-height {
    height: 490px;
    display: flex;
    flex-direction: column;
  }
  
  .chart-height .v-card-text {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    overflow: hidden;
  }
  
  /* Remover scroll do Top 10 */
  .chart-height .v-list {
    overflow-y: visible !important;
    overflow-x: hidden !important;
    max-height: none !important;
  }
  
  /* Prevenir scroll horizontal nos itens da lista */
  .action-item {
    overflow-x: hidden !important;
    overflow-y: visible !important;
  }
  
  .action-item .v-list-item-title {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
  }
  
  /* Espaçamento para chips de quantidade */
  .action-item .v-chip {
    min-width: 100px;
    justify-content: center;
  }
  
  /* Animação de fade-in para cards */
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  /* Container da lista de ações com scroll horizontal */
  .actions-list-container {
    max-height: 485px;
    overflow-y: auto;
    overflow-x: hidden;
  }
  
  /* Estilização da scrollbar para a lista de ações */
  .actions-list-container::-webkit-scrollbar {
    width: 8px;
  }
  
  .actions-list-container::-webkit-scrollbar-track {
    background: #f5f5f5;
    border-radius: 4px;
  }
  
  .actions-list-container::-webkit-scrollbar-thumb {
    background: #bdbdbd;
    border-radius: 4px;
    transition: background 0.3s ease;
  }
  
  .actions-list-container::-webkit-scrollbar-thumb:hover {
    background: #9e9e9e;
  }
  
  /* Itens de ação - Preto e Branco */
  .action-item-bw {
    transition: all 0.3s ease;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    overflow-x: hidden !important;
  }
  
  .action-item-bw:hover {
    background-color: rgba(0, 0, 0, 0.03);
    border-left: 3px solid #424242;
    padding-left: 21px !important;
  }
  
  /* Número do ranking */
  .rank-number {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #424242 0%, #616161 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  }
  
  /* Badge de contagem */
  .count-badge {
    display: flex;
    flex-direction: row;
    align-items: baseline;
    gap: 6px;
    padding: 8px 16px;
    background-color: #f5f5f5;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
  }
  
  .count-number {
    font-size: 1.375rem;
    font-weight: 700;
    color: #212121;
    line-height: 1;
  }
  
  .count-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #757575;
    text-transform: lowercase;
  }
  
  /* Animação dos círculos progressivos */
  .v-progress-circular {
    animation: scaleIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  }
  
  @keyframes scaleIn {
    from {
      opacity: 0;
      transform: scale(0.5);
    }
    to {
      opacity: 1;
      transform: scale(1);
    }
  }
  
  /* Animação das barras de progresso */
  .v-progress-linear {
    animation: slideIn 0.6s ease-out;
  }
  
  @keyframes slideIn {
    from {
      opacity: 0;
      transform: scaleX(0);
      transform-origin: left;
    }
    to {
      opacity: 1;
      transform: scaleX(1);
    }
  }
  
  /* Sparkline - limpo e moderno */
  .v-sparkline {
    cursor: crosshair;
    transition: opacity 0.3s ease;
  }
  
  .v-sparkline:hover {
    opacity: 0.95;
  }
  
  /* Labels do eixo X customizados */
  .axis-label {
    font-weight: 600;
    color: #424242;
    font-size: 0.75rem;
  }
  
  /* Animação suave ao carregar */
  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  .v-sheet .v-sparkline {
    animation: fadeIn 0.6s ease-out;
  }
  
  /* Responsividade */
  @media (max-width: 960px) {
    .dashboard-container {
      padding: 16px;
    }
  }
  </style>