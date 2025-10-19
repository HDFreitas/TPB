import { defineStore } from 'pinia';
import interacaoService from '@/services/interacoes/interacoes';

  export interface InteracaoFilters {
    tipo_interacao_id?: number;
    data_interacao_from?: string;
    data_interacao_to?: string;
    descricao?: string;
    cliente_id?: number;
    cliente_nome?: string;
    titulo?: string;
    chave?: string;
    valor_from?: number;
    valor_to?: number;
  }

export const useInteracaoStore = defineStore('interacao', {
  state: () => ({
    interacoes: [] as any[],
    loading: false,
    error: null as string | null,
    selectedInteracao: null as any,
    filtersExpanded: false,
      filters: {
        tipo_interacao_id: undefined,
        data_interacao_from: undefined,
        data_interacao_to: undefined,
        descricao: undefined,
        cliente_id: undefined,
        cliente_nome: undefined,
        titulo: undefined,
        chave: undefined,
        valor_from: undefined,
        valor_to: undefined,
      } as InteracaoFilters,
    pagination: {
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1,
    },
  }),
  actions: {
    setFilter<K extends keyof InteracaoFilters>(key: K, value: InteracaoFilters[K]) {
      this.filters[key] = value as any;
    },
    async applyFilters() {
      await this.searchInteracoes({ ...this.filters });
    },
    clearFilters() {
  this.filters = {
    tipo_interacao_id: undefined,
    data_interacao_from: undefined,
    data_interacao_to: undefined,
    descricao: undefined,
    cliente_id: undefined,
    cliente_nome: undefined,
    titulo: undefined,
    chave: undefined,
    valor_from: undefined,
    valor_to: undefined,
  } as InteracaoFilters;
      this.applyFilters();
    },
    async fetchInteracoes(params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await interacaoService.getAll(params);
        this.interacoes = data.data || data;
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
          this.error = e.response?.data?.message || e.message || 'Erro ao buscar interações';
        }
      } finally {
        this.loading = false;
      }
    },
    async searchInteracoes(filters: any, params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await interacaoService.search(filters, params);
        this.interacoes = data.data || data;
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
          this.error = e.response?.data?.message || e.message || 'Erro ao buscar interações';
        }
      } finally {
        this.loading = false;
      }
    },
    async createInteracao(payload: any) {
      this.loading = true;
      this.error = null;
      try {
        await interacaoService.create(payload);
        await this.fetchInteracoes();
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para criar interações.';
        } else if (e.response?.status === 422) {
          this.error = 'Dados inválidos. Verifique os campos preenchidos.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível criar a interação. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao criar interação';
        }
      } finally {
        this.loading = false;
      }
    },
    async updateInteracao(id: number, payload: any) {
      this.loading = true;
      this.error = null;
      try {
        await interacaoService.update(id, payload);
        await this.fetchInteracoes();
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para editar interações.';
        } else if (e.response?.status === 422) {
          this.error = 'Dados inválidos. Verifique os campos preenchidos.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível atualizar a interação. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao atualizar interação';
        }
      } finally {
        this.loading = false;
      }
    },
    async deleteInteracao(id: number) {
      this.loading = true;
      this.error = null;
      try {
        await interacaoService.delete(id);
        await this.fetchInteracoes();
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para excluir interações.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível excluir a interação. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao deletar interação';
        }
      } finally {
        this.loading = false;
      }
    },
    async getInteracaoById(id: number) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await interacaoService.getById(id);
        this.selectedInteracao = data;
      } catch (e: any) {
        if (e.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
        } else if (e.response?.status === 403) {
          this.error = 'Você não tem permissão para visualizar esta interação.';
        } else if (e.response?.status === 404) {
          this.error = 'Interação não encontrada.';
        } else if (e.response?.status === 500) {
          this.error = 'Ops! Não foi possível carregar a interação. Tente novamente.';
        } else if (!e.response) {
          this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        } else {
          this.error = e.response?.data?.message || e.message || 'Erro ao buscar interação';
        }
      } finally {
        this.loading = false;
      }
    },
    clearSelectedInteracao() {
      this.selectedInteracao = null;
    },
  },
});
