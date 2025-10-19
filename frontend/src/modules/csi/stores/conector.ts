import { defineStore } from 'pinia';
import conectorService from '@/services/conectores/conector';
import { useAuthStore } from '@/modules/plataforma/stores/auth';

export const useConectorStore = defineStore('conector', {
  state: () => ({
    conectores: [] as any[],
    loading: false,
    error: null as string | null,
    selectedConector: null as any,
    pagination: {
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1,
    },
    filters: {
      tenant_id: null as number | null,
      nome: '',
      status: null as boolean | null,
    },
    filtersExpanded: false,
  }),
  actions: {
    async fetchConectores(params?: any) {
      this.loading = true;
      this.error = null;
      try {
        // Incluir tenant_id baseado no perfil do usuário
        const authStore = useAuthStore();
        const user = authStore.getUser;
        
        let requestParams = { ...params };
        
        // Se não é usuário HUB, sempre usar o tenant do usuário logado
        if (user && !user.roles?.includes('HUB')) {
          requestParams.tenant_id = user.tenant_id;
        }
        // Se é usuário HUB, usar o filtro de tenant se especificado, senão usar o tenant do usuário
        else if (user && user.roles?.includes('HUB')) {
          // Se não há filtro de tenant específico, usar o tenant do usuário HUB
          if (!this.filters.tenant_id) {
            requestParams.tenant_id = user.tenant_id;
          }
          // Se há filtro de tenant, usar o filtro (pode ser null para "todos")
          else {
            requestParams.tenant_id = this.filters.tenant_id;
          }
        }
        
        const { data } = await conectorService.getAll(requestParams);
        this.conectores = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para acessar esta funcionalidade.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Algo deu errado no servidor. Tente novamente em alguns instantes.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao buscar conectores';
        }
      } finally {
        this.loading = false;
      }
    },
    async updateConector(id: number, payload: any) {
      this.loading = true;
      this.error = null;
      try {
        await conectorService.update(id, payload);
        // Não recarrega automaticamente - deixa para o componente decidir
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para editar conectores.';
        } else if (e.response?.status === 422) {
          const errors = e.response?.data?.errors;
          if (errors) {
            if (errors.codigo && Array.isArray(errors.codigo) && errors.codigo.some((msg: string) => msg.includes('Já existe um conector com este código'))) {
              this.error = 'Já existe um conector com este código. Escolha outro código.';
            } else {
              const firstError = Object.values(errors)[0];
              this.error = Array.isArray(firstError) ? firstError[0] : firstError;
            }
          } else {
            this.error = e.response?.data?.message || 'Dados inválidos. Verifique os campos preenchidos.';
          }
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível atualizar o conector. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao atualizar conector';
        }
      } finally {
        this.loading = false;
      }
    },
    async getConectorById(id: number) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await conectorService.getById(id);
        this.selectedConector = data;
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para visualizar este conector.';
        } else if (e.response?.status === 404) {
          this.error = 'Conector não encontrado.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível carregar o conector. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao buscar conector';
        }
      } finally {
        this.loading = false;
      }
    },
    async testConnection(id: number) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await conectorService.testConnection(id);
        return data;
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para testar conexões.';
        } else if (e.response?.status === 404) {
          this.error = 'Conector não encontrado.';
        } else if (e.response?.status === 500) {
          this.error = 'Erro ao testar conexão: ' + (e.response?.data?.message || 'Erro interno do servidor');
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao testar conexão';
        }
        throw e;
      } finally {
        this.loading = false;
      }
    },
    async searchConectores(filters: any, params?: any) {
      this.loading = true;
      this.error = null;
      try {
        // Incluir tenant_id baseado no perfil do usuário
        const authStore = useAuthStore();
        const user = authStore.getUser;
        
        let requestParams = { ...params };
        
        // Se não é usuário HUB, sempre usar o tenant do usuário logado
        if (user && !user.roles?.includes('HUB')) {
          requestParams.tenant_id = user.tenant_id;
        }
        // Se é usuário HUB, usar o filtro de tenant se especificado, senão usar o tenant do usuário
        else if (user && user.roles?.includes('HUB')) {
          // Se não há filtro de tenant específico, usar o tenant do usuário HUB
          if (!this.filters.tenant_id) {
            requestParams.tenant_id = user.tenant_id;
          }
          // Se há filtro de tenant, usar o filtro (pode ser null para "todos")
          else {
            requestParams.tenant_id = this.filters.tenant_id;
          }
        }
        
        const { data } = await conectorService.search(filters, requestParams);
        this.conectores = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para buscar conectores.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Algo deu errado no servidor. Tente novamente em alguns instantes.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao buscar conectores';
        }
      } finally {
        this.loading = false;
      }
    },
    clearSelectedConector() {
      this.selectedConector = null;
    },
    setFilter(key: string, value: any) {
      (this.filters as any)[key] = value;
    },
    clearFilters() {
      this.filters = {
        tenant_id: null,
        nome: '',
        status: null,
      };
    },
    toggleFiltersExpanded() {
      this.filtersExpanded = !this.filtersExpanded;
    },
    async applyFilters() {
      // remove filtros vazios antes de aplicar
      const cleanFilters = Object.fromEntries(
        Object.entries(this.filters).filter(([_, value]) =>
          value !== null && value !== undefined && value !== ''
        )
      );
      await this.searchConectores(cleanFilters);
    },
  },
});
