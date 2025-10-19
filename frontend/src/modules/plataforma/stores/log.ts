import { defineStore } from 'pinia';
import logService from '@/services/utils/log';

export const useLogStore = defineStore('log', {
  state: () => ({
    logs: [] as any[],
    loading: false,
    error: null as string | null,
    selectedLog: null as any,
    dashboardStats: {} as any,
    trendData: {} as any,
    pagination: {
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1,
    },
    filters: {
      action: null,
      log_type: null,
      user_id: null,
      ip_address: null,
      content: null,
      created_at_from: null,
      created_at_to: null,
    },
    filtersExpanded: false,
  }),
  actions: {
    // Métodos existentes
    async fetchLogs(params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await logService.getAll(params);
        this.logs = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar logs';
      } finally {
        this.loading = false;
      }
    },

    async searchLogs(filters: any, params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await logService.search(filters, params);
        this.logs = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar logs';
      } finally {
        this.loading = false;
      }
    },

    async createLog(payload: any) {
      this.loading = true;
      this.error = null;
      try {
        await logService.create(payload);
        await this.fetchLogs();
      } catch (e: any) {
        this.error = e.message || 'Erro ao criar log';
      } finally {
        this.loading = false;
      }
    },

    async deleteLog(id: number) {
      this.loading = true;
      this.error = null;
      try {
        await logService.delete(id);
        await this.fetchLogs();
      } catch (e: any) {
        this.error = e.message || 'Erro ao deletar log';
      } finally {
        this.loading = false;
      }
    },

    async getLogById(id: number) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await logService.getById(id);
        this.selectedLog = data;
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar log';
      } finally {
        this.loading = false;
      }
    },

    clearSelectedLog() {
      this.selectedLog = null;
    },

    // ==================== NOVOS MÉTODOS DE DASHBOARD ====================

    // Buscar estatísticas do dashboard
    async fetchDashboardStats(tenantId?: number) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await logService.getDashboardStats(tenantId);
        this.dashboardStats = data;
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar estatísticas do dashboard';
      } finally {
        this.loading = false;
      }
    },

    // Buscar tendência horária (últimas 24h)
    async fetchHourlyTrend(tenantId?: number) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await logService.getHourlyTrend(tenantId);
        return data;
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar tendência horária';
        return null;
      } finally {
        this.loading = false;
      }
    },

    // Buscar dados de tendência
    async fetchTrendData(days: number = 7, tenantId?: number) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await logService.getTrendData(days, tenantId);
        this.trendData = data;
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar dados de tendência';
      } finally {
        this.loading = false;
      }
    },

    // Buscar logs por período
    async fetchLogsByPeriod(days: number = 7, tenantId?: number) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await logService.getLogsByPeriod(days, tenantId);
        return data;
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar logs por período';
        return null;
      } finally {
        this.loading = false;
      }
    },

    // Buscar logs de erro
    async fetchErrorLogs(params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await logService.getErrors(params);
        this.logs = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar logs de erro';
      } finally {
        this.loading = false;
      }
    },

    // Buscar logs por tipo
    async fetchLogsByType(type: string, params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await logService.getByType(type, params);
        this.logs = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar logs por tipo';
      } finally {
        this.loading = false;
      }
    },

    // Buscar logs por usuário
    async fetchLogsByUser(userId: number, params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await logService.getByUser(userId, params);
        this.logs = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar logs por usuário';
      } finally {
        this.loading = false;
      }
    },

    // Exportar logs
    async exportLogs(filters: any, format: 'json' | 'csv' = 'json') {
      this.loading = true;
      this.error = null;
      try {
        const response = await logService.exportLogs(filters, format);
        
        // Criar download
        const blob = new Blob([response.data], { type: 'application/octet-stream' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `logs-export.${format}`;
        a.click();
        window.URL.revokeObjectURL(url);
        
        return true;
      } catch (e: any) {
        this.error = e.message || 'Erro ao exportar logs';
        return false;
      } finally {
        this.loading = false;
      }
    },

    // Limpar dados do dashboard
    clearDashboardData() {
      this.dashboardStats = {};
      this.trendData = {};
    },

    // ==================== GERENCIAMENTO DE FILTROS ====================

    // Definir um filtro específico
    setFilter(key: string, value: any) {
      if (key in this.filters) {
        (this.filters as any)[key] = value;
      }
    },

    // Aplicar filtros (buscar logs com filtros atuais)
    async applyFilters() {
      const activeFilters: any = {};
      
      // Apenas adicionar filtros com valores preenchidos
      Object.entries(this.filters).forEach(([key, value]) => {
        if (value !== null && value !== '') {
          // Formatar datas se necessário
          if (key === 'created_at_from') {
            activeFilters[key] = value + ' 00:00:00';
          } else if (key === 'created_at_to') {
            activeFilters[key] = value + ' 23:59:59';
          } else {
            activeFilters[key] = value;
          }
        }
      });

      // Se não houver filtros, buscar todos
      if (Object.keys(activeFilters).length === 0) {
        await this.fetchLogs();
      } else {
        await this.searchLogs(activeFilters);
      }
    },

    // Limpar todos os filtros
    clearFilters() {
      this.filters = {
        action: null,
        log_type: null,
        user_id: null,
        ip_address: null,
        content: null,
        created_at_from: null,
        created_at_to: null,
      };
      this.fetchLogs();
    },

    // Toggle expansão dos filtros
    toggleFiltersExpanded() {
      this.filtersExpanded = !this.filtersExpanded;
    },
  },
});