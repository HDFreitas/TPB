import { defineStore } from 'pinia';
import contatoService from '@/services/contatos/contato';
import { Contato, ContatoFilters } from '@/types/contato';

export interface ContatoState {
  contatos: Contato[];
  loading: boolean;
  error: string | null;
  selectedContato: Contato | null;
  pagination: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
  filters: ContatoFilters;
  filtersExpanded: boolean;
}

export const useContatoStore = defineStore('contato', {
  state: (): ContatoState => ({
    contatos: [] as Contato[],
    loading: false,
    error: null as string | null,
    selectedContato: null as Contato | null,
    pagination: {
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1,
    },
    filters: {
      nome: '',
      email: '',
      codigo: '',
      cargo: '',
      tipo_perfil: null,
      promotor: null,
    },
    filtersExpanded: false,
  }),

  getters: {
    getContatos: (state) => state.contatos,
    getLoading: (state) => state.loading,
    getError: (state) => state.error,
    getSelectedContato: (state) => state.selectedContato,
    getPagination: (state) => state.pagination,
    getFilters: (state) => state.filters,
    getFiltersExpanded: (state) => state.filtersExpanded,
  },

  actions: {
    async fetchContatos(params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await contatoService.getAll(params);
        this.contatos = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar contatos';
      } finally {
        this.loading = false;
      }
    },

    async searchContatos(filters: any, params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await contatoService.search(filters, params);
        this.contatos = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar contatos';
      } finally {
        this.loading = false;
      }
    },

    async getContatoById(id: number) {
      this.loading = true;
      this.error = null;
      try {
        const contato = await contatoService.getById(id);
        this.selectedContato = contato;
        return contato;
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar contato';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async createContato(data: any) {
      this.loading = true;
      this.error = null;
      try {
        const contato = await contatoService.create(data);
        this.contatos.unshift(contato);
        return contato;
      } catch (e: any) {
        this.error = e.message || 'Erro ao criar contato';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async updateContato(id: number, data: any) {
      this.loading = true;
      this.error = null;
      try {
        const contato = await contatoService.update(id, data);
        const index = this.contatos.findIndex(c => c.id === id);
        if (index !== -1) {
          this.contatos[index] = contato;
        }
        if (this.selectedContato?.id === id) {
          this.selectedContato = contato;
        }
        return contato;
      } catch (e: any) {
        this.error = e.message || 'Erro ao atualizar contato';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async deleteContato(id: number) {
      this.loading = true;
      this.error = null;
      try {
        await contatoService.delete(id);
        this.contatos = this.contatos.filter(c => c.id !== id);
        if (this.selectedContato?.id === id) {
          this.selectedContato = null;
        }
      } catch (e: any) {
        this.error = e.message || 'Erro ao excluir contato';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async getContatosByCliente(clienteId: number, params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await contatoService.getByCliente(clienteId, params);
        this.contatos = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar contatos do cliente';
      } finally {
        this.loading = false;
      }
    },

    async getContatosByTenant(tenantId: number, params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await contatoService.getByTenant(tenantId, params);
        this.contatos = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar contatos do tenant';
      } finally {
        this.loading = false;
      }
    },

    setFilter<K extends keyof ContatoFilters>(key: K, value: ContatoFilters[K]) {
      this.filters[key] = value;
    },

    clearFilters() {
      this.filters = {
        nome: '',
        email: '',
        codigo: '',
        cargo: '',
        tipo_perfil: null,
        promotor: null,
      };
    },

    toggleFiltersExpanded() {
      this.filtersExpanded = !this.filtersExpanded;
    },

    async applyFilters(clienteId?: number, tenantId?: number) {
      // remove filtros vazios antes de aplicar
      const cleanFilters = Object.fromEntries(
        Object.entries(this.filters).filter(([_, value]) =>
          value !== null && value !== undefined && value !== ''
        )
      );
      
      if (tenantId) {
        // Se temos um tenantId, buscar contatos desse tenant com filtros
        await this.getContatosByTenant(tenantId, { filters: cleanFilters });
      } else if (clienteId) {
        // Se temos um clienteId, buscar contatos desse cliente com filtros
        await this.getContatosByCliente(clienteId, { filters: cleanFilters });
      } else {
        await this.searchContatos(cleanFilters);
      }
    },
  },
});
