import apiClient from '../api';

export default {
  getAll(params?: any) {
    return apiClient.get('/conectores', { params });
  },
  getById(id: number) {
    return apiClient.get(`/conectores/${id}`);
  },
  update(id: number, data: any) {
    return apiClient.put(`/conectores/${id}`, data);
  },
  search(filters: any, params?: any) {
    return apiClient.post('/conectores/search', filters, { params });
  },
  getAtivos(params?: any) {
    return apiClient.get('/conectores/ativos', { params });
  },
  getByTenant(tenantId: number, params?: any) {
    return apiClient.get(`/conectores/tenant/${tenantId}`, { params });
  },
  getByCodigo(codigo: string) {
    return apiClient.get(`/conectores/codigo/${codigo}`);
  },
  getByCodigoAndTenant(codigo: string, tenantId: number) {
    return apiClient.get(`/conectores/codigo/${codigo}/tenant/${tenantId}`);
  },
  testConnection(id: number) {
    return apiClient.post(`/conectores/${id}/test-connection`);
  },
};
