import apiClient from '../api';

export default {
  getAll(params?: any) {
    return apiClient.get('/permissions', { params });
  },
  getById(id: number) {
    return apiClient.get(`/permissions/${id}`);
  },
  create(data: any) {
    return apiClient.post('/permissions', data);
  },
  update(id: number, data: any) {
    return apiClient.put(`/permissions/${id}`, data);
  },
  delete(id: number) {
    return apiClient.delete(`/permissions/${id}`);
  },
};
