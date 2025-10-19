import apiClient from '../api';

export default {
  getAll(params?: any) {
    return apiClient.get('/clientes', { params });
  },
  getById(id: number) {
    return apiClient.get(`/clientes/${id}`);
  },
  create(data: any) {
    return apiClient.post('/clientes', data);
  },
  update(id: number, data: any) {
    return apiClient.put(`/clientes/${id}`, data);
  },
  delete(id: number) {
    return apiClient.delete(`/clientes/${id}`);
  },
  search(filters: any, params?: any) {
    return apiClient.post('/clientes/search', filters, { params });
  },
};
