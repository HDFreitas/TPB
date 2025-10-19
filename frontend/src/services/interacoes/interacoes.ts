import apiClient from '../api';

export default {
  getAll(params?: any) {
    return apiClient.get('/interacoes', { params });
  },
  getById(id: number) {
    return apiClient.get(`/interacoes/${id}`);
  },
  create(data: any) {
    return apiClient.post('/interacoes', data);
  },
  update(id: number, data: any) {
    return apiClient.put(`/interacoes/${id}`, data);
  },
  delete(id: number) {
    return apiClient.delete(`/interacoes/${id}`);
  },
  search(filters: any, params?: any) {
    return apiClient.post('/interacoes/search', filters, { params });
  },
};
