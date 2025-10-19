import apiClient from '../api';

export default {
  getAll(params?: any) {
    return apiClient.get('/tenants', { params });
  },
  getById(id: number) {
    return apiClient.get(`/tenants/${id}`);
  },
  create(data: any) {
    return apiClient.post('/tenants', data);
  },
  update(id: number, data: any) {
    return apiClient.put(`/tenants/${id}`, data);
  },
  delete(id: number) {
    return apiClient.delete(`/tenants/${id}`);
  },
}; 