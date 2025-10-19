import apiClient from '../api';

export default {
  getAll(params?: any) {
    return apiClient.get('/logs', { params });
  },
  getById(id: number) {
    return apiClient.get(`/logs/${id}`);
  },
  create(data: any) {
    return apiClient.post('/logs', data);
  },
  delete(id: number) {
    return apiClient.delete(`/logs/${id}`);
  },
  search(filters: any, params?: any) {
    return apiClient.post('/logs/search', filters, { params });
  },
  // Dashboard - Estatísticas gerais
  getDashboardStats(tenantId?: number) {
    const params = tenantId ? { tenant_id: tenantId } : {};
    return apiClient.get('/logs-dashboard', { params });
  },

  // Dashboard - Tendência por hora (últimas 24h)
  getHourlyTrend(tenantId?: number) {
    const params = tenantId ? { tenant_id: tenantId } : {};
    return apiClient.get('/logs-dashboard/hourly-trend', { params });
  },

  // Dashboard - Logs por período
  getLogsByPeriod(days: number = 7, tenantId?: number) {
    const params = { days, ...(tenantId && { tenant_id: tenantId }) };
    return apiClient.get('/logs-dashboard/period', { params });
  },

  // Logs de erro
  getErrors(params?: any) {
    return apiClient.get('/logs-dashboard/errors', { params });
  },

  // Logs por tipo
  getByType(type: string, params?: any) {
    return apiClient.get(`/logs-dashboard/type/${type}`, { params });
  },

  // Logs por usuário
  getByUser(userId: number, params?: any) {
    return apiClient.get(`/logs/user/${userId}`, { params });
  },

  // Estatísticas gerais
  getStats(tenantId?: number) {
    const params = tenantId ? { tenant_id: tenantId } : {};
    return apiClient.get('/logs/stats', { params });
  },

  // Top ações mais frequentes
  getTopActions(limit: number = 10, tenantId?: number) {
    const params = { limit, ...(tenantId && { tenant_id: tenantId }) };
    return apiClient.get('/logs/top-actions', { params });
  },

  // Logs por período (dados para gráficos)
  getTrendData(days: number = 7, tenantId?: number) {
    const params = { days, ...(tenantId && { tenant_id: tenantId }) };
    return apiClient.get('/logs/trend', { params });
  },

  // Exportar logs
  exportLogs(filters: any, format: 'json' | 'csv' = 'json') {
    return apiClient.post('/logs/export', { filters, format }, { 
      responseType: 'blob' 
    });
  },

  // Logs em tempo real (WebSocket - se implementado)
  subscribeToLogs(_callback: (log: any) => void) {
    // Implementação futura para WebSocket
    console.log('WebSocket subscription not implemented yet');
  },

  // Unsubscribe de logs em tempo real
  unsubscribeFromLogs() {
    // Implementação futura para WebSocket
    console.log('WebSocket unsubscription not implemented yet');
  },

  // Lista de operações disponíveis (CREATE, UPDATE, DELETE, etc)
  getActions(tenantId?: number) {
    const params = tenantId ? { tenant_id: tenantId } : {};
    return apiClient.get('/logs-dashboard/actions', { params });
  }
};