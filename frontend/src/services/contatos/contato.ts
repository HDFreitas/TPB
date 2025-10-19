import api from '@/services/api';

export default {
  async getAll(params?: any) {
    const response = await api.get('/contatos', { params });
    return response.data;
  },

  async getById(id: number) {
    const response = await api.get(`/contatos/${id}`);
    return response.data;
  },

  async create(data: any) {
    const response = await api.post('/contatos', data);
    return response.data;
  },

  async update(id: number, data: any) {
    const response = await api.put(`/contatos/${id}`, data);
    return response.data;
  },

  async delete(id: number) {
    const response = await api.delete(`/contatos/${id}`);
    return response.data;
  },

  async search(filters: any, params?: any) {
    const response = await api.post('/contatos/search', filters, { params });
    return response.data;
  },

  async getByCliente(clienteId: number, params?: any) {
    const response = await api.get(`/contatos/cliente/${clienteId}`, { params });
    return response.data;
  },

  async getByTenant(tenantId: number, params?: any) {
    const response = await api.get(`/contatos/tenant/${tenantId}`, { params });
    return response.data;
  }
};
