import apiClient from '../api';

export default {
  getAll(params?: any) {
    return apiClient.get('/users', { params });
  },
  getById(id: number) {
    return apiClient.get(`/users/${id}`);
  },
  create(data: any) {
    return apiClient.post('/users', data);
  },
  update(id: number, data: any) {
    return apiClient.put(`/users/${id}`, data);
  },
  delete(id: number) {
    return apiClient.delete(`/users/${id}`);
  },
  search(filters: any, params?: any) {
    return apiClient.post('/users/search', filters, { params });
  },
  assignRole(data: { user_id: number; roles: string[] }) {
    return apiClient.post('/users/roles/assign', data);
  },
  removeRole(data: { user_id: number; roles: string[] }) {
    return apiClient.post('/users/roles/remove', data);
  },
  getUserRoles(userId: number) {
    return apiClient.get(`/users/${userId}/roles`);
  },
}; 