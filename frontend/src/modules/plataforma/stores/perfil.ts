import { defineStore } from 'pinia';
import perfilService from '@/services/perfis/perfis';

// Define the state interface
export interface PerfilState {
  perfis: any[];
  loading: boolean;
  error: string | null;
  selectedPerfil: any;
  pagination: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
  perfilUsers: {
    associated: any[];
    available: any[];
  };
  perfilPermissions: {
    assigned: any[];
    available: any[];
  };
  usersLoading: boolean;
  permissionsLoading: boolean;
  filters: {
    name: string;
    status: boolean | null;
  };
  filtersExpanded: boolean;
}

export const usePerfilStore = defineStore('perfil', {
  state: (): PerfilState => ({
    perfis: [] as any[],
    loading: false,
    error: null as string | null,
    selectedPerfil: null as any,
    pagination: {
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1,
    },
    // Novos estados para gerenciamento de usuários e permissões
    perfilUsers: {
      associated: [] as any[],
      available: [] as any[],
    },
    perfilPermissions: {
      assigned: [] as any[],
      available: [] as any[],
    },
    usersLoading: false,
    permissionsLoading: false,
    filters: {
      name: '',
      status: null as boolean | null,
    },
    filtersExpanded: false,
  }),
  actions: {
    async fetchPerfis(params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await perfilService.getAll(params);
        this.perfis = data.data || data;
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
          this.error = e.response?.data?.message || e.message || 'Erro ao buscar perfis';
        }
      } finally {
        this.loading = false;
      }
    },
    async createPerfil(payload: any) {
      this.loading = true;
      this.error = null;
      try {
        await perfilService.create(payload);
        await this.fetchPerfis();
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para criar perfis.';
        } else if (e.response?.status === 422) {
          this.error = 'Dados inválidos. Verifique os campos preenchidos.';
        } else if (e.response?.status === 500) {
          // Verificar se é erro de perfil duplicado
          const errorMessage = e.response?.data?.message || e.message || '';
          if (errorMessage.includes('Já existe um perfil com o nome') || 
              errorMessage.includes('already exists for guard')) {
            this.error = errorMessage.includes('Já existe um perfil com o nome') 
              ? errorMessage 
              : `Já existe um perfil com o nome "${payload.name}". Por favor, escolha outro nome.`;
          } else {
            this.error = 'Ops! Não foi possível criar o perfil. Tente novamente.';
          }
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          // Verificar se é erro de perfil duplicado em outros status codes
          const errorMessage = e.response?.data?.message || e.message || '';
          if (errorMessage.includes('Já existe um perfil com o nome') || 
              errorMessage.includes('already exists for guard')) {
            this.error = errorMessage.includes('Já existe um perfil com o nome') 
              ? errorMessage 
              : `Já existe um perfil com o nome "${payload.name}". Por favor, escolha outro nome.`;
          } else {
            this.error = errorMessage || 'Erro ao criar perfil';
          }
        }
      } finally {
        this.loading = false;
      }
    },
    async updatePerfil(id: number, payload: any) {
      this.loading = true;
      this.error = null;
      try {
        await perfilService.update(id, payload);
        // Recarregar o perfil específico para atualizar os dados na tela
        await this.getPerfilById(id);
        await this.fetchPerfis();
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para editar perfis.';
        } else if (e.response?.status === 422) {
          this.error = 'Dados inválidos. Verifique os campos preenchidos.';
        } else if (e.response?.status === 500) {
          // Verificar se é erro de perfil duplicado
          const errorMessage = e.response?.data?.message || e.message || '';
          if (errorMessage.includes('Já existe um perfil com o nome') || 
              errorMessage.includes('already exists for guard')) {
            this.error = errorMessage.includes('Já existe um perfil com o nome') 
              ? errorMessage 
              : `Já existe um perfil com o nome "${payload.name}". Por favor, escolha outro nome.`;
          } else {
            this.error = 'Ops! Não foi possível atualizar o perfil. Tente novamente.';
          }
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          // Verificar se é erro de perfil duplicado em outros status codes
          const errorMessage = e.response?.data?.message || e.message || '';
          if (errorMessage.includes('Já existe um perfil com o nome') || 
              errorMessage.includes('already exists for guard')) {
            this.error = errorMessage.includes('Já existe um perfil com o nome') 
              ? errorMessage 
              : `Já existe um perfil com o nome "${payload.name}". Por favor, escolha outro nome.`;
          } else {
            this.error = errorMessage || 'Erro ao atualizar perfil';
          }
        }
      } finally {
        this.loading = false;
      }
    },
    async deletePerfil(id: number) {
      this.loading = true;
      this.error = null;
      try {
        await perfilService.delete(id);
        await this.fetchPerfis();
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para excluir perfis.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível excluir o perfil. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao deletar perfil';
        }
      } finally {
        this.loading = false;
      }
    },
    async getPerfilById(id: number) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await perfilService.getById(id);
        this.selectedPerfil = data;
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para visualizar este perfil.';
        } else if (e.response?.status === 404) {
          this.error = 'Perfil não encontrado.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível carregar o perfil. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao buscar perfil';
        }
      } finally {
        this.loading = false;
      }
    },
    clearSelectedPerfil() {
      this.selectedPerfil = null;
    },
    
    // Novas ações para gerenciamento de usuários
    async fetchPerfilUsers(perfilId: number) {
      this.usersLoading = true;
      this.error = null;
      try {
        const { data } = await perfilService.getUsers(perfilId);
        this.perfilUsers = {
          associated: data.associated || [],
          available: data.available || [],
        };
      } catch (e: any) {
        this.handleError(e, 'Erro ao buscar usuários do perfil');
      } finally {
        this.usersLoading = false;
      }
    },
    
    async associateUsers(perfilId: number, userIds: number[]) {
      this.usersLoading = true;
      this.error = null;
      try {
        await perfilService.associateUsers(perfilId, userIds);
        await this.fetchPerfilUsers(perfilId);
      } catch (e: any) {
        this.handleError(e, 'Erro ao associar usuários ao perfil');
      } finally {
        this.usersLoading = false;
      }
    },
    
    async removeUser(perfilId: number, userId: number) {
      this.usersLoading = true;
      this.error = null;
      try {
        await perfilService.removeUser(perfilId, userId);
        await this.fetchPerfilUsers(perfilId);
      } catch (e: any) {
        this.handleError(e, 'Erro ao remover usuário do perfil');
      } finally {
        this.usersLoading = false;
      }
    },
    
    // Novas ações para gerenciamento de permissões
    async fetchPerfilPermissions(perfilId: number) {
      this.permissionsLoading = true;
      this.error = null;
      try {
        const { data } = await perfilService.getPermissions(perfilId);
        this.perfilPermissions = {
          assigned: data.assigned || [],
          available: data.available || [],
        };
      } catch (e: any) {
        this.handleError(e, 'Erro ao buscar permissões do perfil');
      } finally {
        this.permissionsLoading = false;
      }
    },
    
    async syncPermissions(perfilId: number, permissionIds: number[]) {
      this.permissionsLoading = true;
      this.error = null;
      try {
        await perfilService.syncPermissions(perfilId, permissionIds);
        await this.fetchPerfilPermissions(perfilId);
      } catch (e: any) {
        this.handleError(e, 'Erro ao sincronizar permissões do perfil');
      } finally {
        this.permissionsLoading = false;
      }
    },
    
    // Método auxiliar para tratamento de erros
    handleError(e: any, defaultMessage: string) {
      if (e.response?.status === 401) {
        this.error = 'Sessão expirada. Faça login novamente.';
      } else if (e.response?.status === 403) {
        this.error = 'Você não tem permissão para realizar esta ação.';
      } else if (e.response?.status === 422) {
        this.error = 'Dados inválidos. Verifique os campos preenchidos.';
      } else if (e.response?.status === 500) {
        this.error = 'Ops! Algo deu errado no servidor. Tente novamente.';
      } else if (!e.response) {
        this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
      } else {
        this.error = e.response?.data?.message || e.message || defaultMessage;
      }
    },
    
    // Limpar dados de usuários e permissões
    clearPerfilData() {
      this.perfilUsers = {
        associated: [],
        available: [],
      };
      this.perfilPermissions = {
        assigned: [],
        available: [],
      };
    },

    // NOVOS MÉTODOS PARA FILTROS
    async searchPerfis(filters: any, params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const searchParams = { ...filters, ...params };
        const { data } = await perfilService.getAll(searchParams);
        this.perfis = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        this.handleError(e, 'Erro ao buscar perfis');
      } finally {
        this.loading = false;
      }
    },

    setFilter<K extends keyof PerfilState['filters']>(key: K, value: PerfilState['filters'][K]) {
      this.filters[key] = value;
    },

    clearFilters() {
      this.filters = {
        name: '',
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
      
      // Mapear para o formato esperado pelo backend
      const backendFilters: any = {};
      if (cleanFilters.name) {
        backendFilters.search = cleanFilters.name;
      }
      if (cleanFilters.status !== undefined) {
        backendFilters.status = cleanFilters.status;
      }
      
      await this.searchPerfis(backendFilters);
    },
  },
}); 