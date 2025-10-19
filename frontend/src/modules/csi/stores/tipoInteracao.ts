import { defineStore } from 'pinia';
import tipoInteracaoService from '@/services/tiposInteracao/tipoInteracao';
import { useAuthStore } from '@/modules/plataforma/stores/auth';
import type {  
  TipoInteracaoFormData, 
  TipoInteracaoSearchFilters, 
  TipoInteracaoStoreState,
  ImportResult 
} from '@/types/tipoInteracao';

export const useTipoInteracaoStore = defineStore('tipoInteracao', {
  state: (): TipoInteracaoStoreState => ({
    tiposInteracao: [],
    loading: false,
    error: null,
    selectedTipoInteracao: null,
    pagination: {
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1,
    },
    filters: {
      nome: '',
      conector_id: undefined,
      status: undefined,
    },
  }),

  getters: {
    getTipoInteracaoById: (state) => (id: number) => {
      return state.tiposInteracao.find(tipo => tipo.id === id);
    },
    
    getTiposInteracaoAtivos: (state) => {
      return state.tiposInteracao.filter(tipo => tipo.status === true);
    },
    
    hasFilters: (state) => {
      return Object.values(state.filters).some(value => 
        value !== null && value !== undefined && value !== ''
      );
    }
  },

  actions: {
    async fetchTiposInteracao(params?: any) {
      this.loading = true;
      this.error = null;
      
      try {
        const authStore = useAuthStore();
        const user = authStore.getUser;
        
        let requestParams = { ...params };
        
        // Sempre usar o tenant do usuário logado
        if (user && user.tenant_id) {
          requestParams.tenant_id = user.tenant_id;
        }
        
        const response = await tipoInteracaoService.getAll(requestParams);
        
        if (response?.data) {
          this.tiposInteracao = response.data.data || [];
          this.pagination = {
            current_page: response.data.current_page || 1,
            per_page: response.data.per_page || 15,
            total: response.data.total || 0,
            last_page: response.data.last_page || 1,
          };
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao buscar tipos de interação';
        this.tiposInteracao = [];
      } finally {
        this.loading = false;
      }
    },

    async getTipoInteracaoById(id: number) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await tipoInteracaoService.getById(id);
        
        if (response?.data?.data) {
          this.selectedTipoInteracao = response.data.data;
          return response.data.data;
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao buscar tipo de interação';
      } finally {
        this.loading = false;
      }
      
      return null;
    },

    async createTipoInteracao(data: TipoInteracaoFormData) {
      this.loading = true;
      this.error = null;
      
      try {
        const authStore = useAuthStore();
        const user = authStore.getUser;
        
        // Sempre usar o tenant do usuário logado
        if (user && user.tenant_id) {
          data.tenant_id = user.tenant_id;
        }
        
        const response = await tipoInteracaoService.create(data);
        
        if (response?.data?.data) {
          // Atualizar a lista local
          this.tiposInteracao.unshift(response.data.data);
          this.pagination.total += 1;
          return response.data.data;
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao criar tipo de interação';
        throw error;
      } finally {
        this.loading = false;
      }
      
      return null;
    },

    async updateTipoInteracao(id: number, data: Partial<TipoInteracaoFormData>) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await tipoInteracaoService.update(id, data);
        
        if (response?.data?.data) {
          // Atualizar na lista local
          const index = this.tiposInteracao.findIndex(tipo => tipo.id === id);
          if (index !== -1) {
            this.tiposInteracao[index] = response.data.data;
          }
          
          // Atualizar selecionado se for o mesmo
          if (this.selectedTipoInteracao?.id === id) {
            this.selectedTipoInteracao = response.data.data;
          }
          
          return response.data.data;
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao atualizar tipo de interação';
        throw error;
      } finally {
        this.loading = false;
      }
      
      return null;
    },

    async deleteTipoInteracao(id: number) {
      this.loading = true;
      this.error = null;
      
      try {
        await tipoInteracaoService.delete(id);
        
        // Remover da lista local
        this.tiposInteracao = this.tiposInteracao.filter(tipo => tipo.id !== id);
        this.pagination.total -= 1;
        
        // Limpar selecionado se for o mesmo
        if (this.selectedTipoInteracao?.id === id) {
          this.selectedTipoInteracao = null;
        }
        
        return true;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao deletar tipo de interação';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async searchTiposInteracao(filters: TipoInteracaoSearchFilters, params?: any) {
      this.loading = true;
      this.error = null;
      
      try {
        const authStore = useAuthStore();
        const user = authStore.getUser;
        
        let searchFilters = { ...filters };
        
        // Sempre usar o tenant do usuário logado
        if (user && user.tenant_id) {
          searchFilters.tenant_id = user.tenant_id;
        }
        
        const response = await tipoInteracaoService.search(searchFilters, params);
        
        if (response?.data) {
          this.tiposInteracao = response.data.data || [];
          this.pagination = {
            current_page: response.data.current_page || 1,
            per_page: response.data.per_page || 15,
            total: response.data.total || 0,
            last_page: response.data.last_page || 1,
          };
          
          // Atualizar filtros
          this.filters = { ...searchFilters };
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao buscar tipos de interação';
        this.tiposInteracao = [];
      } finally {
        this.loading = false;
      }
    },

    async getTiposInteracaoAtivos(tenantId?: number) {
      this.loading = true;
      this.error = null;
      
      try {
        const params = tenantId ? { tenant_id: tenantId } : {};
        const response = await tipoInteracaoService.getAtivos(params);
        
        if (response?.data) {
          return response.data.data || [];
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao buscar tipos de interação ativos';
      } finally {
        this.loading = false;
      }
      
      return [];
    },

    async getTiposInteracaoByConector(conectorId: number) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await tipoInteracaoService.getByConector(conectorId);
        
        if (response?.data?.data) {
          return response.data.data;
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao buscar tipos de interação por conector';
      } finally {
        this.loading = false;
      }
      
      return [];
    },

    async importFromConector(id: number): Promise<ImportResult | null> {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await tipoInteracaoService.importFromConector(id);
        
        if (response?.data && typeof response.data === 'object') {
          const resultado = response.data;
          
          if ('sucesso' in resultado || 'erro' in resultado) {

            await this.fetchTiposInteracao();
            
            return resultado as ImportResult;
          }
        }
        
        return null;
        
      } catch (error: any) {
        
        this.error = error.response?.data?.message 
          || error.response?.data?.error 
          || 'Erro ao importar do conector';
        
        return null;
        
      } finally {
        this.loading = false;
      }
    },

    clearSelectedTipoInteracao() {
      this.selectedTipoInteracao = null;
    },

    clearError() {
      this.error = null;
    },

    clearFilters() {
      this.filters = {
        nome: '',
        conector_id: undefined,
        status: undefined,
      };
    },

    setFilters(filters: Partial<TipoInteracaoSearchFilters>) {
      this.filters = { ...this.filters, ...filters };
    }
  }
});
