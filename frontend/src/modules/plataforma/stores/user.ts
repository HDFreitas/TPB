import { defineStore } from 'pinia';
import userService from '@/services/usuarios/usuarios';

// Define the state interface
export interface UserState {
  users: any[];
  loading: boolean;
  error: string | null;
  selectedUser: any;
  pagination: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
  filters: {
    name: string;
    usuario: string;
    status: boolean | null;
  };
  filtersExpanded: boolean;
}

export const useUserStore = defineStore('user', {
  state: (): UserState => ({
    users: [] as any[],
    loading: false,
    error: null as string | null,
    selectedUser: null as any,
    pagination: {
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1,
    },
    filters: {
      name: '',
      usuario: '',
      status: null as boolean | null,
    },
    filtersExpanded: false,
  }),
  actions: {
    async fetchUsers(params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await userService.getAll(params);
        this.users = data.data || data;
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
          this.error = e.response?.data?.message || e.message || 'Erro ao buscar usuários';
        }
      } finally {
        this.loading = false;
      }
    },
    async createUser(payload: any) {
      this.loading = true;
      this.error = null;
      try {
        await userService.create(payload);
        await this.fetchUsers();
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para criar usuários.';
        } else if (e.response?.status === 422) {
          this.error = 'Dados inválidos. Verifique os campos preenchidos.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível criar o usuário. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao criar usuário';
        }
      } finally {
        this.loading = false;
      }
    },
    async updateUser(id: number, payload: any) {
      this.loading = true;
      this.error = null;
      try {
        await userService.update(id, payload);
        await this.fetchUsers();
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para editar usuários.';
        } else if (e.response?.status === 422) {
          this.error = 'Dados inválidos. Verifique os campos preenchidos.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível atualizar o usuário. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao atualizar usuário';
        }
      } finally {
        this.loading = false;
      }
    },
    async deleteUser(id: number) {
      this.loading = true;
      this.error = null;
      try {
        await userService.delete(id);
        await this.fetchUsers();
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para excluir usuários.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível excluir o usuário. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao deletar usuário';
        }
      } finally {
        this.loading = false;
      }
    },
    async getUserById(id: number) {
      this.loading = true;
      this.error = null;
      try {
        const response = await userService.getById(id);
        
        // Verificar se os dados estão encapsulados em 'data' (Laravel Resource)
        const userData = response.data?.data || response.data;
        
        this.selectedUser = userData;
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para visualizar este usuário.';
        } else if (e.response?.status === 404) {
          this.error = 'Usuário não encontrado.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível carregar o usuário. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao buscar usuário';
        }
      } finally {
        this.loading = false;
      }
    },
    clearSelectedUser() {
      this.selectedUser = null;
    },
    async assignRole(payload: { user_id: number; roles: string[] }) {
      this.loading = true;
      this.error = null;
      try {
        await userService.assignRole(payload);
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para gerenciar roles.';
        } else if (e.response?.status === 422) {
          this.error = 'Dados inválidos. Verifique os campos preenchidos.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível atribuir a role. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao atribuir role ao usuário';
        }
        throw e;
      } finally {
        this.loading = false;
      }
    },
    async removeRole(payload: { user_id: number; roles: string[] }) {
      this.loading = true;
      this.error = null;
      try {
        await userService.removeRole(payload);
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para gerenciar roles.';
        } else if (e.response?.status === 422) {
          this.error = 'Dados inválidos. Verifique os campos preenchidos.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível remover a role. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao remover role do usuário';
        }
        throw e;
      } finally {
        this.loading = false;
      }
    },
    async getUserRoles(userId: number) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await userService.getUserRoles(userId);
        return data;
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para visualizar roles.';
        } else if (e.response?.status === 404) {
          this.error = 'Usuário não encontrado.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível carregar as roles. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao buscar roles do usuário';
        }
        throw e;
      } finally {
        this.loading = false;
      }
    },
    
    // NOVOS MÉTODOS PARA FILTROS
    async searchUsers(filters: any, params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await userService.search(filters, params);
        this.users = data.data || data;
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
          this.error = e.response?.data?.message || e.message || 'Erro ao buscar usuários';
        }
      } finally {
        this.loading = false;
      }
    },

    setFilter<K extends keyof UserState['filters']>(key: K, value: UserState['filters'][K]) {
      this.filters[key] = value;
    },

    clearFilters() {
      this.filters = {
        name: '',
        usuario: '',
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
      await this.searchUsers(cleanFilters);
    },
  },
}); 