import { defineStore } from 'pinia';
import clienteService from '@/services/clientes/cliente';
import { Cliente } from '@/types/cliente';

// Define the state interface
export interface ClienteState {
  clientes: Cliente[];
  loading: boolean;
  error: string | null;
  selectedCliente: Cliente | null;
  pagination: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
  filters: {
    nome_fantasia: string;
    codigo: string;
    cnpj_cpf: string;
    codigo_senior: string;
    status: boolean | null;
  };
  filtersExpanded: boolean;
}

export const useClienteStore = defineStore('cliente', {
  state: () => ({
    clientes: [] as Cliente[],
    loading: false,
    error: null as string | null,
    selectedCliente: null as Cliente | null,
    pagination: {
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1,
    },
    filters: {
      nome_fantasia: '',
      codigo: '',
      cnpj_cpf: '',
      codigo_senior: '',
      status: null as boolean | null,
    },
    filtersExpanded: false,
  }),
  actions: {
    async fetchClientes(params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await clienteService.getAll(params);
        this.clientes = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar clientes';
      } finally {
        this.loading = false;
      }
    },
    async searchClientes(filters: any, params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await clienteService.search(filters, params);
        this.clientes = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar clientes';
      } finally {
        this.loading = false;
      }
    },
    async createCliente(payload: any) {
      this.loading = true;
      this.error = null;
      try {
        await clienteService.create(payload);
        await this.fetchClientes();
      } catch (e: any) {
        this.error = e.message || 'Erro ao criar cliente';
      } finally {
        this.loading = false;
      }
    },
    async updateCliente(id: number, payload: any) {
      this.loading = true;
      this.error = null;
      try {
        await clienteService.update(id, payload);
        await this.fetchClientes();
      } catch (e: any) {
        this.error = e.message || 'Erro ao atualizar cliente';
      } finally {
        this.loading = false;
      }
    },
    async deleteCliente(id: number) {
      this.loading = true;
      this.error = null;
      try {
        await clienteService.delete(id);
        await this.fetchClientes();
      } catch (e: any) {
        this.error = e.message || 'Erro ao deletar cliente';
      } finally {
        this.loading = false;
      }
    },
    async getClienteById(id: number) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await clienteService.getById(id);
        this.selectedCliente = data;
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar cliente';
      } finally {
        this.loading = false;
      }
    },
    clearSelectedCliente() {
      this.selectedCliente = null;
    },
    setFilter<K extends keyof ClienteState['filters']>(key: K, value: ClienteState['filters'][K]) {
      this.filters[key] = value;
    },
    clearFilters() {
      this.filters = {
        nome_fantasia: '',
        codigo: '',
        cnpj_cpf: '',
        codigo_senior: '',
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
          value !== null && value !== undefined
        )
      );
      await this.searchClientes(cleanFilters);
    },
  },
});
