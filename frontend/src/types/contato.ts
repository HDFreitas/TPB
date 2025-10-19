export interface Contato {
  id?: number;
  tenant_id?: number;
  cliente_id: number;
  codigo?: string;
  nome: string;
  email?: string;
  cargo?: string;
  telefone?: string;
  tipo_perfil?: 'Relacional' | 'Transacional';
  promotor: boolean;
  created_by?: number;
  updated_by?: number;
  created_at?: string;
  updated_at?: string;
  // Relacionamentos
  cliente?: {
    id: number;
    razao_social: string;
    nome_fantasia?: string;
  };
  tenant?: {
    id: number;
    nome: string;
  };
  creator?: {
    id: number;
    name: string;
  };
  updater?: {
    id: number;
    name: string;
  };
}

export interface ContatoFilters {
  nome: string;
  codigo: string;
  email: string;
  cargo: string;
  tipo_perfil: 'Relacional' | 'Transacional' | null;
  promotor: boolean | null;
}
