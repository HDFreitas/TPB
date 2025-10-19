export interface TipoInteracao {
  id: number;
  tenant_id: number;
  nome: string;
  conector_id?: number;
  porta?: number;
  rota?: string;
  status: boolean;
  observacoes?: string;
  created_at: string;
  updated_at: string;
  tenant?: {
    id: number;
    nome: string;
    email: string;
  };
  conector?: {
    id: number;
    nome: string;
    codigo: string;
    status: boolean;
  };
}

export interface TipoInteracaoFormData {
  tenant_id: number;
  nome: string;
  conector_id?: number;
  porta?: number;
  rota?: string;
  status: boolean;
  observacoes?: string;
}

export interface TipoInteracaoSearchFilters {
  nome?: string;
  conector_id?: number;
  status?: boolean;
  tenant_id?: number;
}

export interface TipoInteracaoPagination {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

export interface TipoInteracaoStoreState {
  tiposInteracao: TipoInteracao[];
  loading: boolean;
  error: string | null;
  selectedTipoInteracao: TipoInteracao | null;
  pagination: TipoInteracaoPagination;
  filters: TipoInteracaoSearchFilters;
}

export interface ImportResult {
  success: boolean;
  message: string;
  imported_count: number;
}
