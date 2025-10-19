import { defineStore } from 'pinia';
import tenantService from '@/services/tenants/tenant';

export const useTenantStore = defineStore('tenant', {
  state: () => ({
    tenants: [] as any[],
    loading: false,
    error: null as string | null,
    selectedTenant: null as any,
    pagination: {
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1,
    },
    filters: {
      nome: '',
      status: null,
    },
    filtersExpanded: false,
  }),
  actions: {
    setFilters(newFilters: Record<string, any>) {
      this.filters = { ...this.filters, ...newFilters };
    },
    applyFilters() {
      this.fetchTenants({ ...this.filters });
    },
    clearFilters() {
      this.filters = { nome: '', status: null };
      this.fetchTenants();
    },
    toggleFiltersExpanded() {
      this.filtersExpanded = !this.filtersExpanded;
    },
    async fetchTenants(params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await tenantService.getAll(params);
        this.tenants = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        // Tratamento específico por tipo de erro
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para acessar esta funcionalidade.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Algo deu errado no servidor. Tente novamente em alguns instantes.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao buscar tenants';
        }
      } finally {
        this.loading = false;
      }
    },
    async createTenant(payload: any) {
      this.loading = true;
      this.error = null;
      try {
        await tenantService.create(payload);
        await this.fetchTenants();
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para criar tenants.';
        } else if (e.response?.status === 422) {
          // Tratar erros de validação específicos
          const errors = e.response?.data?.errors;
          if (errors) {
            if (errors.nome && Array.isArray(errors.nome) && errors.nome.some((msg: string) => msg.includes('Já existe um tenant com este nome'))) {
              this.error = 'Já existe um tenant com este nome. Escolha outro nome.';
            } else if (errors.dominio && Array.isArray(errors.dominio) && errors.dominio.some((msg: string) => msg.includes('Já existe um tenant com este domínio'))) {
              this.error = 'Já existe um tenant com este domínio. Escolha outro domínio.';
            } else {
              // Se há múltiplos erros, mostrar o primeiro
              const firstError = Object.values(errors)[0];
              this.error = Array.isArray(firstError) ? firstError[0] : firstError;
            }
          } else {
            this.error = e.response?.data?.message || 'Dados inválidos. Verifique os campos preenchidos.';
          }
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível salvar o tenant. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao criar tenant';
        }
      } finally {
        this.loading = false;
      }
    },
    async updateTenant(id: number, payload: any) {
      this.loading = true;
      this.error = null;
      try {
        await tenantService.update(id, payload);
        await this.fetchTenants();
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para editar tenants.';
        } else if (e.response?.status === 422) {
          // Tratar erros de validação específicos
          const errors = e.response?.data?.errors;
          if (errors) {
            if (errors.nome && Array.isArray(errors.nome) && errors.nome.some((msg: string) => msg.includes('Já existe um tenant com este nome'))) {
              this.error = 'Já existe um tenant com este nome. Escolha outro nome.';
            } else if (errors.dominio && Array.isArray(errors.dominio) && errors.dominio.some((msg: string) => msg.includes('Já existe um tenant com este domínio'))) {
              this.error = 'Já existe um tenant com este domínio. Escolha outro domínio.';
            } else {
              // Se há múltiplos erros, mostrar o primeiro
              const firstError = Object.values(errors)[0];
              this.error = Array.isArray(firstError) ? firstError[0] : firstError;
            }
          } else {
            this.error = e.response?.data?.message || 'Dados inválidos. Verifique os campos preenchidos.';
          }
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível atualizar o tenant. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao atualizar tenant';
        }
      } finally {
        this.loading = false;
      }
    },
    async deleteTenant(id: number) {
      this.loading = true;
      this.error = null;
      try {
        await tenantService.delete(id);
        await this.fetchTenants();
      } catch (e: any) {
        this.error = e.message || 'Erro ao deletar tenant';
        
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para excluir tenants.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível excluir o tenant. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao deletar tenant';
        }
      } finally {
        this.loading = false;
      }
    },
    async getTenantById(id: number) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await tenantService.getById(id);
        this.selectedTenant = data;
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar tenant';
        
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para visualizar este tenant.';
        } else if (e.response?.status === 404) {
          this.error = 'Tenant não encontrado.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível carregar o tenant. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao buscar tenant';
        }
      } finally {
        this.loading = false;
      }
    },
    clearSelectedTenant() {
      this.selectedTenant = null;
    },
  },
}); 