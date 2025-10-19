# Documentação da Estrutura - Plataforma Modular

## 📋 **Informações do Documento**
- **Projeto:** CSI - Customer Success Intelligence
- **Versão:** 1.0
- **Data:** 29/09/2025
- **Autor(es):** Luan Felipe Tavares

## 📑 **Documentos Relacionados**
- [Documentação de Requisitos do CSI v1.0](./1%20Documentação%20de%20Requisitos%20do%20CSI%20v1.0.md)
- [Documento de Arquitetura de Software](./2%20Documento%20de%20Arquitetura%20de%20Software.md)
- [Especificações Técnicas](./4%20Especificações%20Técnicas.md)
- [Sistema de Permissões](./6%20Sistema%20de%20Permissões.md)
- [Guia de Comandos Docker](./7%20Guia%20de%20Comandos%20Docker.md)

## 📋 **Visão Geral**

Este documento descreve a estrutura completa do projeto, incluindo organização de pastas do frontend (Vue.js) e backend (Laravel), além de todas as rotas disponíveis.

---

## 🎯 **Backend - Laravel (Estrutura Modular)**

### 📁 **Estrutura Geral**
```
backend/
├── app/
│   ├── Console/              # Comandos Artisan
│   ├── Helpers/              # Funções auxiliares
│   ├── Http/
│   │   ├── Controllers/      # Controllers base
│   │   │   ├── Controller.php           # Base controller
│   │   │   └── API/V1/
│   │   │       └── LogsController.php   # Não modularizado
│   │   ├── Middleware/       # Middlewares customizados
│   │   └── Requests/         # Form requests globais
│   ├── Interfaces/           # Interfaces base
│   │   ├── BaseInterface.php            # Interface base
│   │   └── LogRepositoryInterface.php   # Não modularizado
│   ├── Models/               # Models não modularizados
│   │   └── Log.php
│   ├── Modules/              # 🎯 ESTRUTURA MODULAR
│   │   ├── Plataforma/       # Módulo de autenticação e gestão
│   │   └── Csi/              # Módulo Centro de Serviços de TI
│   ├── Providers/            # Service providers
│   │   └── RepositoryServiceProvider.php # DI Container
│   ├── Repositories/         # Repositories base
│   │   ├── BaseRepository.php           # Repository base
│   │   └── LogRepository.php            # Não modularizado
│   ├── Services/             # Services globais
│   ├── Traits/               # Traits reutilizáveis
│   └── Utils/                # Utilitários
├── bootstrap/
│   └── app.php               # Configuração da aplicação
├── config/                   # Configurações do Laravel
├── database/                 # Migrations, seeders, factories
├── routes/                   # 🚀 ROTAS ORGANIZADAS
│   ├── api.php               # Rotas API base
│   ├── plataforma.php        # Rotas módulo Plataforma
│   ├── csi.php               # Rotas módulo CSI
│   ├── console.php           # Comandos console
│   └── web.php               # Rotas web
├── storage/                  # Arquivos de storage
└── vendor/                   # Dependências Composer
```

### 🏗️ **Módulo Plataforma**
```
app/Modules/Plataforma/
├── Controllers/
│   ├── AuthController.php           # Autenticação (login, register, logout)
│   ├── TenantsController.php        # CRUD de tenants
│   ├── UsersController.php          # CRUD de usuários
│   ├── PerfisController.php         # CRUD de perfis/roles
│   ├── PermissionController.php     # CRUD de permissões
│   └── UserRoleController.php       # Atribuição de roles
├── Models/
│   ├── Tenant.php                   # Model tenant (multi-tenancy)
│   ├── User.php                     # Model usuário
│   └── Perfil.php                   # Model perfil/role
├── Repositories/
│   ├── TenantRepository.php         # Repository tenant
│   ├── UserRepository.php           # Repository usuário
│   ├── PerfilRepository.php         # Repository perfil
│   ├── PermissionRepository.php     # Repository permissão
│   └── UserRoleRepository.php       # Repository user-role
├── Interfaces/
│   ├── TenantRepositoryInterface.php    # Interface tenant
│   ├── UserRepositoryInterface.php      # Interface usuário
│   ├── PerfilRepositoryInterface.php    # Interface perfil
│   ├── PermissionRepositoryInterface.php # Interface permissão
│   └── UserRoleRepositoryInterface.php  # Interface user-role
├── Requests/
│   ├── LoginUserRequest.php         # Validação login
│   ├── RegisterUserRequest.php      # Validação registro
│   ├── StoreUserRequest.php         # Validação criação usuário
│   ├── UpdateUserRequest.php        # Validação atualização usuário
│   ├── AssignRoleRequest.php        # Validação atribuição role
│   └── AssignUserRoleRequest.php    # Validação user-role
└── Services/                        # Lógicas de negócio (futuro)
```

### 🎫 **Módulo CSI (Centro de Serviços de TI)**
```
app/Modules/Csi/
├── Controllers/
│   ├── ClientesController.php       # CRUD de clientes
│   └── InteracoesController.php     # CRUD de interações
├── Models/
│   ├── Cliente.php                  # Model cliente
│   └── Interacao.php                # Model interação
├── Repositories/
│   ├── ClienteRepository.php        # Repository cliente
│   └── InteracaoRepository.php      # Repository interação
├── Interfaces/
│   ├── ClienteRepositoryInterface.php   # Interface cliente
│   └── InteracaoRepositoryInterface.php # Interface interação
├── Requests/
│   └── InteracaoRequest.php         # Validação interação
└── Services/
    └── TicketService.php            # Lógica de negócio tickets
```

---

## 🎨 **Frontend - Vue.js (Estrutura Modular)**

### 📁 **Estrutura Geral**
```
frontend/src/
├── App.vue                   # Componente raiz
├── main.ts                   # Entry point da aplicação
├── assets/                   # Recursos estáticos
│   ├── images/
│   │   ├── backgrounds/      # Imagens de fundo
│   │   ├── profile/          # Imagens de perfil
│   │   └── logos/            # Logotipos
│   └── styles/               # Estilos globais
├── components/               # 🧩 COMPONENTES GLOBAIS
│   ├── auth/                 # Componentes de autenticação
│   ├── common/               # Componentes comuns
│   │   ├── StatusBadge.vue   # Badge de status
│   │   └── StatusSwitch.vue  # Switch de status
│   ├── layouts/              # Componentes de layout
│   └── shared/               # Componentes compartilhados
├── composables/              # Composables Vue 3
├── layouts/                  # 🎨 LAYOUTS
│   ├── full/                 # Layout completo
│   │   ├── FullLayout.vue    # Layout principal
│   │   ├── logo/             # Componentes de logo
│   │   ├── vertical-header/  # Header vertical
│   │   └── vertical-sidebar/ # Sidebar vertical
│   │       ├── NavItem/      # Item de navegação
│   │       ├── NavGroup/     # Grupo de navegação
│   │       └── sidebarItem.ts # Configuração do menu
│   └── blank/                # Layout em branco
├── modules/                  # 📦 MÓDULOS ORGANIZADOS
│   ├── csi/                  # Módulo CSI
│   │   ├── stores/           # Stores específicos do CSI
│   │   │   ├── cliente.ts    # Store clientes
│   │   │   └── interacao.ts  # Store interações
│   │   └── views/            # Views do CSI
│   │       ├── clientes/     # Views clientes
│   │       │   ├── Clientes.vue      # Listagem
│   │       │   └── shared/
│   │       │       └── ClienteForm.vue # Formulário
│   │       └── interacoes/   # Views interações
│   │           ├── Interacoes.vue    # Listagem
│   │           └── shared/
│   │               └── InteracaoForm.vue # Formulário
│   └── plataforma/           # Módulo Plataforma
│       ├── stores/           # Stores específicos da Plataforma
│       │   ├── auth.ts       # Store autenticação
│       │   ├── tenant.ts     # Store tenants
│       │   ├── user.ts       # Store usuários
│       │   ├── perfil.ts     # Store perfis
│       │   ├── permission.ts # Store permissões
│       │   └── log.ts        # Store logs
│       └── views/            # Views da Plataforma
│           ├── tenants/      # Views tenants
│           │   ├── TenantsIndex.vue  # Listagem
│           │   └── shared/
│           │       └── TenantForm.vue # Formulário
│           ├── usuarios/     # Views usuários
│           │   ├── Usuarios.vue      # Listagem
│           │   └── shared/
│           │       └── UserForm.vue  # Formulário
│           └── perfis/       # Views perfis
│               ├── Perfis.vue        # Listagem
│               └── shared/
│                   └── PerfilForm.vue # Formulário
├── plugins/                  # Plugins Vue
├── router/                   # 🚀 ROTEAMENTO
│   ├── index.ts              # Configuração principal
│   ├── guard.ts              # Guards de autenticação
│   └── modules/
│       └── main.ts           # Rotas principais
├── services/                 # 🌐 SERVICES (CENTRALIZADOS)
│   ├── api.ts                # Cliente HTTP base
│   ├── clientes/
│   │   └── cliente.ts        # Service clientes
│   ├── interacoes/
│   │   └── interacoes.ts     # Service interações
│   ├── tenants/              # Services tenants
│   ├── usuarios/             # Services usuários
│   ├── perfis/               # Services perfis
│   ├── permissions/          # Services permissões
│   └── utils/                # Utilitários HTTP
├── stores/                   # 🗄️ STORES GLOBAIS
│   └── counter.ts            # Store exemplo/demo
├── styles/                   # Estilos globais
├── themes/                   # Temas da aplicação
├── types/                    # 📝 TYPES TYPESCRIPT (CENTRALIZADOS)
│   ├── cliente.d.ts          # Types cliente
│   ├── tenant.d.ts           # Types tenant
│   └── theme/                # Types tema
├── utils/                    # Utilitários
├── views/                    # 📄 VIEWS ANTIGAS (COMPATIBILIDADE)
│   ├── tenants/              # Views tenants (migradas)
│   ├── usuarios/             # Views usuários (migradas)
│   ├── perfis/               # Views perfis (migradas)
│   └── utils/                # Views sistema
│       └── Logs.vue          # Listagem de logs
├── vite-env.d.ts             # Types Vite
└── shims-vue.d.ts            # Types Vue
```

### 🎯 **Padrão Arquitetural Frontend**

**✅ Centralizado (Infraestrutura):**
- **Services:** Comunicação com APIs
- **Stores:** Estado global da aplicação  
- **Types:** Definições TypeScript
- **Components:** Componentes reutilizáveis

**✅ Modularizado (Apresentação):**
- **Views:** Organizadas por domínio de negócio
- **Routes:** Agrupadas por funcionalidade

---

## 🚀 **Rotas do Backend**

### 🔐 **Módulo Plataforma - `/api/v1/`**

#### **Autenticação - `/auth/`**
```http
POST   /api/v1/auth/login      # Login do usuário
POST   /api/v1/auth/register   # Registro de usuário
POST   /api/v1/auth/logout     # Logout (requer JWT)
POST   /api/v1/auth/refresh    # Refresh token (requer JWT)
GET    /api/v1/auth/me         # Dados do usuário logado (requer JWT)
```

#### **Usuários - `/users/` (Protegidas com JWT)**
```http
GET    /api/v1/users           # Listar usuários
POST   /api/v1/users           # Criar usuário
GET    /api/v1/users/{id}      # Visualizar usuário
PUT    /api/v1/users/{id}      # Atualizar usuário
DELETE /api/v1/users/{id}      # Excluir usuário
```

#### **Perfis/Roles - `/perfis/` (Protegidas com JWT)**
```http
GET    /api/v1/perfis          # Listar perfis
POST   /api/v1/perfis          # Criar perfil
GET    /api/v1/perfis/{id}     # Visualizar perfil
PUT    /api/v1/perfis/{id}     # Atualizar perfil
DELETE /api/v1/perfis/{id}     # Excluir perfil
```

#### **Permissões - `/permissions/` (Protegidas com JWT)**
```http
GET    /api/v1/permissions     # Listar permissões
POST   /api/v1/permissions     # Criar permissão
GET    /api/v1/permissions/{id} # Visualizar permissão
PUT    /api/v1/permissions/{id} # Atualizar permissão
DELETE /api/v1/permissions/{id} # Excluir permissão
```

#### **User Roles - `/user-roles/` (Protegidas com JWT)**
```http
POST   /api/v1/user-roles/assign      # Atribuir role a usuário
POST   /api/v1/user-roles/revoke      # Revogar role de usuário
GET    /api/v1/user-roles/user/{user} # Listar roles do usuário
```

#### **Tenants - `/tenants/` (Protegidas com JWT)**
```http
GET    /api/v1/tenants         # Listar tenants
POST   /api/v1/tenants         # Criar tenant
GET    /api/v1/tenants/{id}    # Visualizar tenant
PUT    /api/v1/tenants/{id}    # Atualizar tenant
DELETE /api/v1/tenants/{id}    # Excluir tenant
```

### 🎫 **Módulo CSI - `/api/v1/` (Todas protegidas com JWT)**

#### **Clientes - `/clientes/`**
```http
GET    /api/v1/clientes                    # Listar clientes
POST   /api/v1/clientes                    # Criar cliente
GET    /api/v1/clientes/search             # Buscar clientes (filtros)
GET    /api/v1/clientes/tenant/{tenantId}  # Clientes por tenant
GET    /api/v1/clientes/cnpj-cpf/{cnpjCpf} # Buscar por CNPJ/CPF
GET    /api/v1/clientes/{id}               # Visualizar cliente
PUT    /api/v1/clientes/{id}               # Atualizar cliente
DELETE /api/v1/clientes/{id}               # Excluir cliente
```

#### **Interações - `/interacoes/`**
```http
GET    /api/v1/interacoes                     # Listar interações
POST   /api/v1/interacoes                     # Criar interação
GET    /api/v1/interacoes/search              # Buscar interações (filtros)
GET    /api/v1/interacoes/cliente/{clienteId} # Interações por cliente
GET    /api/v1/interacoes/tenant/{tenantId}   # Interações por tenant
GET    /api/v1/interacoes/{id}                # Visualizar interação
PUT    /api/v1/interacoes/{id}                # Atualizar interação
DELETE /api/v1/interacoes/{id}                # Excluir interação
```

### 🏦 **Sistema (Não Modularizado) - `/api/v1/`**

#### **Logs**
```http
GET    /api/v1/logs            # Listar logs
POST   /api/v1/logs            # Criar log
GET    /api/v1/logs/{id}       # Visualizar log
PUT    /api/v1/logs/{id}       # Atualizar log
DELETE /api/v1/logs/{id}       # Excluir log
```

---

## 🎨 **Rotas do Frontend**

### 🏠 **Layout Principal - `/`**
```typescript
// Layout: FullLayout.vue
// Sidebar: Menu de navegação modular
```

### 🔐 **Módulo Plataforma**

#### **Tenants**
```typescript
GET  /tenants              # Lista de tenants
GET  /tenants/novo         # Formulário novo tenant
GET  /tenants/editar/:id   # Formulário editar tenant
```

#### **Usuários**
```typescript
GET  /usuarios             # Lista de usuários
GET  /usuarios/novo        # Formulário novo usuário
GET  /usuarios/editar/:id  # Formulário editar usuário
```

#### **Perfis**
```typescript
GET  /perfis               # Lista de perfis
GET  /perfis/novo          # Formulário novo perfil
GET  /perfis/editar/:id    # Formulário editar perfil
```

### 🎫 **Módulo CSI**

#### **Clientes**
```typescript
GET  /csi/clientes              # Lista de clientes
GET  /csi/clientes/novo         # Formulário novo cliente
GET  /csi/clientes/editar/:id   # Formulário editar cliente
```

#### **Interações**
```typescript
GET  /csi/interacoes            # Lista de interações
GET  /csi/interacoes/nova       # Formulário nova interação
GET  /csi/interacoes/editar/:id # Formulário editar interação
```

### 📊 **Sistema**

#### **Logs**
```typescript
GET  /logs                 # Lista de logs
```

---

## 🎯 **Menu de Navegação**

### 📋 **Estrutura do Sidebar**
```typescript
// Arquivo: src/layouts/full/vertical-sidebar/sidebarItem.ts

Menu Principal:
├── 🏢 Administração
│   ├── Tenants           → /tenants
│   ├── Usuários          → /usuarios
│   └── Perfis            → /perfis
├── 🎫 CSI - Centro de Serviços
│   ├── Clientes          → /csi/clientes
│   └── Interações        → /csi/interacoes
└── 📊 Monitoramento
    └── Logs              → /logs
```

---

## 🔧 **Configurações Importantes**

### **Backend**
- **Base URL:** `http://localhost:8000`
- **API Prefix:** `/api/v1/`
- **Autenticação:** JWT (middleware `jwt`)
- **CORS:** Configurado para frontend
- **Multi-tenancy:** Suportado

### **Frontend**
- **Base URL:** `http://localhost:5173`
- **Framework:** Vue 3 + TypeScript
- **Estado:** Pinia (stores modulares)
- **Roteamento:** Vue Router
- **HTTP Client:** Axios (configurado em `services/api.ts`)
- **Build Tool:** Vite 6.3.0

### **Docker & DevOps**
- **Backend Container:** PHP 8.4-FPM Alpine + Nginx + Supervisor
- **Frontend Container:** Node.js 23 Alpine + Vite Dev Server
- **Database:** PostgreSQL 17.0 com healthcheck
- **Volumes:** Persistência de dados e node_modules
- **Networks:** Rede isolada `plataforma-network`
- **Auto-setup:** Migrations e seeders automáticos

### **Padrões Arquiteturais**
- **Backend:** Repository Pattern + Interface Segregation
- **Frontend:** Composition API + Modular Stores
- **Modularização:** Domain-Driven Design
- **Dependency Injection:** Laravel Service Container
- **Containerização:** Docker Compose multi-service

---

## 🐳 **Comandos Docker Essenciais**

### **Inicialização**
```bash
# Primeira execução (build completo)
docker-compose build --no-cache
docker-compose up -d

# Verificar status
docker-compose ps
```

### **Desenvolvimento**
```bash
# Ver logs
docker-compose logs -f backend
docker-compose logs -f frontend

# Entrar nos containers
docker-compose exec backend bash
docker-compose exec frontend sh

# Reset do banco
docker-compose exec backend php artisan db:reset --force
```

### **Utilitários**
```bash
# Executar seeders manualmente
docker-compose exec backend php artisan db:seed

# Verificar extensões PHP
docker-compose exec backend php -m

# Rebuild de um serviço
docker-compose build --no-cache backend
docker-compose up -d backend
```
