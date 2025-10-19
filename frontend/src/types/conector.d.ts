export interface Conector {
  id: number;
  tenant_id: number;
  codigo: string;
  nome: string;
  url?: string;
  status: boolean;
  usuario?: string;
  senha?: string;
  token?: string;
  configuracao_adicional?: any;
  observacoes?: string;
  created_at: string;
  updated_at: string;
  tenant?: {
    id: number;
    nome: string;
    email: string;
  };
}

export interface ConectorFormData {
  tenant_id: number;
  codigo: string;
  nome: string;
  url?: string;
  status: boolean;
  usuario?: string;
  senha?: string;
  token?: string;
  configuracao_adicional?: any;
  observacoes?: string;
}

export interface ConectorSearchFilters {
  tenant_id?: number;
  codigo?: string;
  nome?: string;
  url?: string;
  status?: boolean;
}

export interface ConectorTestResult {
  success: boolean;
  message: string;
  data?: {
    type: string;
    url: string;
    status: string;
    message: string;
  };
}

export interface ConectorPagination {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

export interface ConectorStoreState {
  conectores: Conector[];
  loading: boolean;
  error: string | null;
  selectedConector: Conector | null;
  pagination: ConectorPagination;
}

export type ConectorType = '1-ERP' | '2-Movidesk' | '3-CRM Eleve';

export interface ConectorTypeConfig {
  codigo: ConectorType;
  nome: string;
  campos: string[];
  descricao: string;
}
