export interface Tenant {
  id?: number;
  nome: string;
  email: string;
  status: boolean;
  dominio: string;
  descricao: string;
  created_at?: string;
  updated_at?: string;
}

export interface User {
  id?: number;
  tenant_id: number;
  name: string;
  email: string;
  password?: string;
  created_at?: string;
  updated_at?: string;
  tenant?: Tenant;
  usuario: string;
  dominio: string;
  roles?: string[];
  permissions?: string[];
}

export interface Perfil {
  id?: number;
  tenant_id: number;
  name: string;
  description?: string;
  created_at?: string;
  updated_at?: string;
  tenant?: Tenant;
}

export interface Interacao {
  id?: number;
  tenant_id: number;
  cliente_id: number;
  tipo: string;
  origem: string;
  titulo?: string;
  descricao?: string;
  data_interacao: string;
  chave?: string;
  valor?: number | null;
  user_id?: number;
  created_at?: string;
  updated_at?: string;
  tenant?: Tenant;
  cliente?: any; // TODO: Import Cliente type
  user?: User;
}
