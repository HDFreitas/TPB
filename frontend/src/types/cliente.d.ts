export interface Cliente {
  id?: number; // ID opcional para compatibilidade com diferentes APIs
  tenant_id?: number; // ID do tenant
  codigo_erp?: number; // Código legacy
  razao_social: string; // Nome do cliente
  nome_fantasia?: string; // Apelido/Nome fantasia
  codigo_ramo?: string; // Código de ramo
  cidade?: string; // Cidade
  estado?: string; // Sigla UF
  cnpj_cpf: string; // CNPJ ou CPF
  codigo_senior?: string; // Código do Senior
  status?: boolean; // Status ativo/inativo
  cliente_referencia?: boolean; // Cliente Referência (S/N)
  tipo_perfil?: 'Relacional' | 'Transacional'; // Tipo do Perfil
  classificacao?: 'Promotor' | 'Neutro' | 'Detrator'; // Classificação do Cliente
  email?: string; // Email
  telefone?: string; // Telefone
  celular?: string; // Celular
  endereco?: string; // Endereço
  numero?: string; // Número
  complemento?: string; // Complemento
  bairro?: string; // Bairro
  cep?: string; // CEP
  observacoes?: string; // Observações
  created_by?: number; // ID do usuário que criou
  updated_by?: number; // ID do usuário que atualizou
  created_at?: string; // Data de criação
  updated_at?: string; // Data de atualização
}
