import apiClient from '../api';
import type { TipoInteracaoFormData, TipoInteracaoSearchFilters } from '@/types/tipoInteracao';

export default {
  getAll(params?: any) {
    return apiClient.get('/tipos-interacao', { params });
  },
  
  getById(id: number) {
    return apiClient.get(`/tipos-interacao/${id}`);
  },
  
  create(data: TipoInteracaoFormData) {
    return apiClient.post('/tipos-interacao', data);
  },
  
  update(id: number, data: Partial<TipoInteracaoFormData>) {
    return apiClient.put(`/tipos-interacao/${id}`, data);
  },
  
  delete(id: number) {
    return apiClient.delete(`/tipos-interacao/${id}`);
  },
  
  search(filters: TipoInteracaoSearchFilters, params?: any) {
    return apiClient.post('/tipos-interacao/search', filters, { params });
  },
  
  getAtivos(params?: any) {
    return apiClient.get('/tipos-interacao/ativos', { params });
  },
  
  getByTenant(tenantId: number, params?: any) {
    return apiClient.get(`/tipos-interacao/tenant/${tenantId}`, { params });
  },
  
  getByConector(conectorId: number) {
    return apiClient.get(`/tipos-interacao/conector/${conectorId}`);
  },
  
  importFromConector(id: number) {
    return apiClient.post(`/tipos-interacao/${id}/importar-erp`)
      .then(response => {
        console.log('🔍 TIPO de response:', typeof response);
        console.log('🔍 KEYS de response:', Object.keys(response));
        console.log('🔍 response completo:', JSON.stringify(response, null, 2));
        return response;
      });
  }
};
