import apiClient from '../api';

export default {
  getAll(params?: any) {
    return apiClient.get('/perfis', { params });
  },
  getById(id: number) {
    return apiClient.get(`/perfis/${id}`);
  },
  create(data: any) {
    return apiClient.post('/perfis', data);
  },
  update(id: number, data: any) {
    return apiClient.put(`/perfis/${id}`, data);
  },
  delete(id: number) {
    return apiClient.delete(`/perfis/${id}`);
  },
  
  // Novos endpoints para gerenciamento de usuários
  getUsers(perfilId: number) {
    return apiClient.get(`/perfis/${perfilId}/users`);
  },
  associateUsers(perfilId: number, userIds: number[]) {
    return apiClient.post(`/perfis/${perfilId}/users`, { user_ids: userIds });
  },
  removeUser(perfilId: number, userId: number) {
    return apiClient.delete(`/perfis/${perfilId}/users/${userId}`);
  },
  
  // Novos endpoints para gerenciamento de permissões
  getPermissions(perfilId: number) {
    return apiClient.get(`/perfis/${perfilId}/permissions`);
  },
  syncPermissions(perfilId: number, permissionIds: number[]) {
    return apiClient.post(`/perfis/${perfilId}/permissions`, { permission_ids: permissionIds });
  },
}; 