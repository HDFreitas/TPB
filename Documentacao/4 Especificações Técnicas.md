## **Documento de Especificações Técnicas**

### **Informações do Documento**
- **Projeto:** CSI - Customer Success Intelligence
- **Versão:** 1.0
- **Data:** 16/07/2025
- **Autor(es):** Luan Felipe Tavares
- **Aprovado por:** Luan Felipe Tavares

### **Documentos Relacionados**
- [Documentação de Requisitos do CSI v1.0](./1%20Documentação%20de%20Requisitos%20do%20CSI%20v1.0.md)
- [Documento de Arquitetura de Software](./2%20Documento%20de%20Arquitetura%20de%20Software.md)
- [Diagramas Arquiteturais](./3%20Diagramas%20Arquiteturais.md)
- [Sistema de Permissões](./6%20Sistema%20de%20Permissões.md)

### **Histórico de Revisões**
| Versão | Data | Autor | Descrição das Alterações |
|--------|------|-------|-------------------------|
| 1.0 | 16/07/2025 | Luan Tavares | Versão inicial do documento |
| | | | |

---

## **1. Introdução**

### **1.1 Objetivo**
Este documento estabelece os padrões técnicos, convenções e diretrizes a serem seguidos por toda a equipe de desenvolvimento do projeto CSI (Customer Success Intelligence).

### **1.2 Escopo de Aplicação**
- **Aplicável a:** Todo código fonte Laravel/Vue.js, documentação técnica e infraestrutura Azure
- **Obrigatório para:** Desenvolvedores Backend (Laravel), Frontend (Vue.js), DevOps (Azure)
- **Revisão:** Trimestral ou conforme necessidade do projeto

### **1.3 Princípios Norteadores**
- **Consistência:** Código uniforme e previsível seguindo padrões Laravel/Vue.js
- **Manutenibilidade:** Facilitar evolução e correções com arquitetura multi-tenant
- **Segurança:** Security by design com foco em LGPD e isolamento de dados
- **Performance:** Otimização para processamento assíncrono e dashboards responsivos
- **Documentação:** Código auto-documentado e APIs bem especificadas

### **1.4 Stack Tecnológica do CSI**
- **Backend:** Laravel 12, PHP 8.4
- **Frontend:** Vue.js 3, TypeScript, Vite
- **Database:** PostgreSQL 15 (Azure Database)
- **Storage:** Azure Blob Storage
- **Hosting:** Microsoft Azure (App Service, WebJobs)
- **Monitoramento:** Application Insights, Azure Monitor

---

## **2. Padrões de Codificação e Convenções**

### **2.1 Convenções Gerais**

#### **2.1.1 Idioma**
- **Código PHP/Laravel:** Inglês (classes, métodos, variáveis)
- **Código Vue.js/TypeScript:** Inglês (componentes, funções, interfaces)
- **Documentação de Negócio:** Português
- **Commits:** Português (padrão da equipe)
- **Issues/Tasks:** Português
- **Comentários de código:** Português para regras de negócio, Inglês para código técnico

#### **2.1.2 Encoding**
- **Arquivos:** UTF-8 sem BOM
- **Line Endings:** LF (Unix)
- **Indentação:** 4 espaços (PHP) e 2 espaços (Vue.js/TypeScript)

### **2.2 Convenções Laravel (Backend)**

#### **2.2.1 Nomenclatura Laravel**
```php
<?php

// Classes - PascalCase
class TenantController extends Controller
{
    // ...
}

// Models - PascalCase singular
class Tenant extends Model
{
    use HasFactory, SoftDeletes;
    
    // Propriedades - snake_case
    protected $fillable = ['nome', 'ativo'];
    protected $dates = ['deleted_at'];
}

// Métodos - camelCase
public function createTenant(array $data): Tenant
{
    return Tenant::create($data);
}

// Variáveis - camelCase
$tenantData = ['nome' => 'Sancon', 'ativo' => true];
$isActive = true;

// Constantes - UPPER_SNAKE_CASE
const MAX_TENANTS_PER_USER = 5;
const DEFAULT_CACHE_TTL = 3600;

// Rotas - kebab-case
Route::get('/api/v1/tenants/{tenant}/kpis', [KpiController::class, 'index']);
Route::post('/api/v1/auth/login', [AuthController::class, 'login']);

// Migrations - snake_case com timestamp
2024_07_16_create_tenants_table.php
2024_07_16_add_tenant_id_to_users_table.php

// Controllers - PascalCase + Controller
TenantController.php
KpiController.php
ClienteController.php

// Requests - PascalCase + Request
StoreTenantRequest.php
UpdateKpiRequest.php

// Jobs - PascalCase + Job
CalculateKpiJob.php
ImportErpDataJob.php
```

#### **2.2.2 Estrutura de Controllers**
```php
<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Tenant\StoreTenantRequest;
use App\Http\Requests\V1\Tenant\UpdateTenantRequest;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;

class TenantController extends Controller
{
    public function __construct(
        private TenantService $tenantService
    ) {}

    /**
     * Lista todos os tenants (apenas usuários HUB)
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Tenant::class);
        
        $tenants = $this->tenantService->getAllTenants();
        
        return response()->json([
            'data' => $tenants,
            'message' => 'Tenants listados com sucesso'
        ]);
    }

    /**
     * Cria um novo tenant
     */
    public function store(StoreTenantRequest $request): JsonResponse
    {
        $tenant = $this->tenantService->createTenant($request->validated());
        
        return response()->json([
            'data' => $tenant,
            'message' => 'Tenant criado com sucesso'
        ], 201);
    }

    /**
     * Exibe um tenant específico
     */
    public function show(Tenant $tenant): JsonResponse
    {
        $this->authorize('view', $tenant);
        
        return response()->json([
            'data' => $tenant,
            'message' => 'Tenant encontrado'
        ]);
    }

    /**
     * Atualiza um tenant
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('update', $tenant);
        
        $tenant = $this->tenantService->updateTenant($tenant, $request->validated());
        
        return response()->json([
            'data' => $tenant,
            'message' => 'Tenant atualizado com sucesso'
        ]);
    }

    /**
     * Remove um tenant (soft delete)
     */
    public function destroy(Tenant $tenant): JsonResponse
    {
        $this->authorize('delete', $tenant);
        
        $this->tenantService->deleteTenant($tenant);
        
        return response()->json([
            'message' => 'Tenant removido com sucesso'
        ]);
    }
}
```

#### **2.2.3 Models e Relacionamentos**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome',
        'ativo'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relacionamentos - sempre tipados
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    public function kpis(): HasMany
    {
        return $this->hasMany(Kpi::class);
    }

    // Scopes - camelCase
    public function scopeActive($query)
    {
        return $query->where('ativo', true);
    }

    // Accessors/Mutators - camelCase
    public function getNomeUppercaseAttribute(): string
    {
        return strtoupper($this->nome);
    }

    public function setNomeAttribute(string $value): void
    {
        $this->attributes['nome'] = trim($value);
    }
}
```

### **2.3 Convenções Vue.js (Frontend)**

#### **2.3.1 Nomenclatura Vue.js**
```typescript
// Componentes - PascalCase
TenantList.vue
KpiDashboard.vue
ClienteForm.vue

// Composables - camelCase com prefixo 'use'
useTenants.ts
useKpis.ts
useAuth.ts

// Stores Pinia - camelCase
tenants.ts
kpis.ts
auth.ts

// Tipos/Interfaces - PascalCase com prefixo 'I' para interfaces
interface ITenant {
  id: number;
  nome: string;
  ativo: boolean;
  created_at: string;
  updated_at: string;
}

type TenantStatus = 'active' | 'inactive';

// Variáveis e funções - camelCase
const tenantList = ref<ITenant[]>([]);
const isLoading = ref(false);

const fetchTenants = async (): Promise<void> => {
  // ...
};

// Constantes - UPPER_SNAKE_CASE
const MAX_ITEMS_PER_PAGE = 20;
const API_ENDPOINTS = {
  TENANTS: '/api/v1/tenants',
  KPIS: '/api/v1/kpis'
} as const;
```

#### **2.3.2 Estrutura de Componentes Vue**
```vue
<template>
  <div class="tenant-list">
    <!-- Header com título e botão -->
    <div class="tenant-list__header">
      <h1 class="tenant-list__title">Gestão de Tenants</h1>
      <button 
        class="btn btn--primary"
        @click="openCreateModal"
      >
        Novo Tenant
      </button>
    </div>

    <!-- Filtros -->
    <div class="tenant-list__filters">
      <input
        v-model="searchTerm"
        type="text"
        placeholder="Buscar por nome..."
        class="form-input"
      >
      <select v-model="statusFilter" class="form-select">
        <option value="">Todos</option>
        <option value="active">Ativos</option>
        <option value="inactive">Inativos</option>
      </select>
    </div>

    <!-- Lista de tenants -->
    <div class="tenant-list__content">
      <tenant-table
        :tenants="filteredTenants"
        :loading="isLoading"
        @edit="handleEdit"
        @delete="handleDelete"
      />
    </div>

    <!-- Modal de criação/edição -->
    <tenant-modal
      v-model:show="showModal"
      :tenant="selectedTenant"
      @save="handleSave"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useTenants } from '@/composables/useTenants';
import { useNotifications } from '@/composables/useNotifications';
import TenantTable from './components/TenantTable.vue';
import TenantModal from './components/TenantModal.vue';
import type { ITenant } from '@/types/api';

// Composables
const { 
  tenants, 
  isLoading, 
  fetchTenants, 
  createTenant, 
  updateTenant, 
  deleteTenant 
} = useTenants();
const { showSuccess, showError } = useNotifications();

// Estado local
const searchTerm = ref('');
const statusFilter = ref('');
const showModal = ref(false);
const selectedTenant = ref<ITenant | null>(null);

// Computed
const filteredTenants = computed(() => {
  return tenants.value.filter(tenant => {
    const matchesSearch = tenant.nome
      .toLowerCase()
      .includes(searchTerm.value.toLowerCase());
    
    const matchesStatus = !statusFilter.value || 
      (statusFilter.value === 'active' && tenant.ativo) ||
      (statusFilter.value === 'inactive' && !tenant.ativo);
    
    return matchesSearch && matchesStatus;
  });
});

// Métodos
const openCreateModal = (): void => {
  selectedTenant.value = null;
  showModal.value = true;
};

const handleEdit = (tenant: ITenant): void => {
  selectedTenant.value = tenant;
  showModal.value = true;
};

const handleSave = async (tenantData: Partial<ITenant>): Promise<void> => {
  try {
    if (selectedTenant.value) {
      await updateTenant(selectedTenant.value.id, tenantData);
      showSuccess('Tenant atualizado com sucesso');
    } else {
      await createTenant(tenantData);
      showSuccess('Tenant criado com sucesso');
    }
    showModal.value = false;
  } catch (error) {
    showError('Erro ao salvar tenant');
  }
};

const handleDelete = async (tenant: ITenant): Promise<void> => {
  if (confirm(`Deseja realmente excluir o tenant "${tenant.nome}"?`)) {
    try {
      await deleteTenant(tenant.id);
      showSuccess('Tenant excluído com sucesso');
    } catch (error) {
      showError('Erro ao excluir tenant');
    }
  }
};

// Lifecycle
onMounted(() => {
  fetchTenants();
});
</script>

<style scoped lang="scss">
.tenant-list {
  &__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
  }

  &__title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--color-text-primary);
  }

  &__filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    
    .form-input,
    .form-select {
      min-width: 200px;
    }
  }

  &__content {
    background: var(--color-surface);
    border-radius: 0.5rem;
    padding: 1.5rem;
  }
}
</style>
```

### **2.4 Estrutura de Projetos**

#### **2.4.1 Estrutura Backend (Laravel)**

Ver [Documentação da Estrutura](./5%20Documentação%20da%20Estrutura.md)

#### **2.4.2 Estrutura Frontend (Vue.js)**

Ver [Documentação da Estrutura](./5%20Documentação%20da%20Estrutura.md)

- **Arquivos:** UTF-8 sem BOM
- **Line Endings:** LF (Unix)
- **Indentação:** 2 espaços 

### **2.2 Convenções por Linguagem**

#### **2.2.1 JavaScript/TypeScript**

##### **Nomenclatura**
```typescript
// Classes - PascalCase
class UserController {
  // ...
}

// Interfaces - PascalCase com prefixo 'I'
interface IUserService {
  // ...
}

// Funções e Métodos - camelCase
function calculateTotalPrice(items: Item[]): number {
  // ...
}

// Variáveis - camelCase
const userAge = 25;
let isActive = true;

// Constantes - UPPER_SNAKE_CASE
const MAX_RETRY_ATTEMPTS = 3;
const API_BASE_URL = 'https://api.example.com';

// Enums - PascalCase com valores em UPPER_SNAKE_CASE
enum UserStatus {
  ACTIVE = 'ACTIVE',
  INACTIVE = 'INACTIVE',
  SUSPENDED = 'SUSPENDED'
}

// Arquivos
// Componentes: UserProfile.tsx
// Utilitários: dateHelpers.ts
// Testes: UserService.test.ts
```

##### **Estrutura de Código**
```typescript
// Imports - Ordenados por tipo
// 1. Bibliotecas externas
import React from 'react';
import { useEffect, useState } from 'react';

// 2. Imports internos absolutos
import { UserService } from '@/services/UserService';

// 3. Imports relativos
import { formatDate } from './utils';

// 4. Tipos/Interfaces
import type { User } from './types';

// Exemplo de classe bem estruturada
export class UserRepository implements IUserRepository {
  private readonly database: Database;
  
  constructor(database: Database) {
    this.database = database;
  }

  /**
   * Busca um usuário pelo ID
   * @param id - ID do usuário
   * @returns Promise com o usuário ou null
   */
  async findById(id: string): Promise<User | null> {
    try {
      const user = await this.database.users.findOne({ id });
      return user ? this.mapToEntity(user) : null;
    } catch (error) {
      logger.error('Error finding user by id', { id, error });
      throw new RepositoryError('Failed to find user');
    }
  }

  private mapToEntity(dbUser: DbUser): User {
    return new User({
      id: dbUser.id,
      name: dbUser.name,
      email: dbUser.email,
      createdAt: new Date(dbUser.created_at)
    });
  }
}
```

#### **2.2.2 Python**

##### **Nomenclatura**
```python
# Classes - PascalCase
class UserController:
    pass

# Funções e Métodos - snake_case
def calculate_total_price(items: List[Item]) -> float:
    pass

# Variáveis - snake_case
user_age = 25
is_active = True

# Constantes - UPPER_SNAKE_CASE
MAX_RETRY_ATTEMPTS = 3
API_BASE_URL = 'https://api.example.com'

# Arquivos - snake_case
# user_controller.py
# date_helpers.py
# test_user_service.py
```

#### **2.2.3 SQL**

##### **Convenções de Banco de Dados**
```sql
-- Tabelas - snake_case plural
CREATE TABLE users (
    -- Colunas - snake_case
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Índices - idx_tabela_coluna(s)
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_created_at ON users(created_at DESC);

-- Foreign Keys - fk_tabela_origem_tabela_destino
ALTER TABLE orders 
ADD CONSTRAINT fk_orders_users 
FOREIGN KEY (user_id) REFERENCES users(id);

-- Procedures - sp_nome_acao
CREATE PROCEDURE sp_update_user_status(
    p_user_id UUID,
    p_status VARCHAR(20)
)

-- Views - vw_nome_descritivo
CREATE VIEW vw_active_users AS
SELECT * FROM users WHERE status = 'ACTIVE';
```

### **2.3 Estrutura de Projetos**

Ver [Documentação da Estrutura](./5%20Documentação%20da%20Estrutura.md)

### **2.4 Controle de Versão**

#### **2.4.1 Git Flow**
```
main (production)
  └── develop
       ├── feature/TASK-123-nova-funcionalidade
       ├── bugfix/TASK-456-correcao-login
       ├── hotfix/TASK-789-fix-critico
       └── release/v1.2.0
```

#### **2.4.2 Convenções de Commit**
```bash
# Formato
<tipo>(<escopo>): <descrição>

[corpo opcional]

[rodapé opcional]

# Tipos
feat: Nova funcionalidade
fix: Correção de bug
docs: Documentação
style: Formatação (não afeta lógica)
refactor: Refatoração de código
perf: Melhoria de performance
test: Adição/correção de testes
build: Build system ou dependências
ci: Configuração de CI
chore: Outras alterações

# Exemplos
feat(auth): add two-factor authentication

fix(api): handle null response in user service

docs(readme): update installation instructions

# Com breaking change
feat(api)!: change user endpoint response format

BREAKING CHANGE: The user endpoint now returns a different JSON structure
```

---

## **3. Guias de Estilo e Boas Práticas**

### **3.1 Clean Code Principles**

#### **3.1.1 Funções**
```typescript
// ❌ Evitar
function processData(d: any[], f: boolean, n: number) {
  // código complexo e difícil de entender
}

// ✅ Preferir
interface ProcessOptions {
  includeArchived: boolean;
  maxResults: number;
}

function processUserData(
  users: User[], 
  options: ProcessOptions
): ProcessedUser[] {
  const { includeArchived, maxResults } = options;
  
  return users
    .filter(user => includeArchived || !user.isArchived)
    .slice(0, maxResults)
    .map(user => processUser(user));
}
```

#### **3.1.2 Comentários**
```typescript
// ❌ Evitar comentários óbvios
// Incrementa o contador
counter++;

// ✅ Comentários que explicam o "porquê"
// Aplicamos um delay de 100ms para evitar race condition
// com a atualização do DOM no Safari
setTimeout(() => updateUI(), 100);

// ✅ TODOs com contexto
// TODO(joão): Refatorar após migração para v2 da API (TASK-1234)
```

### **3.2 Tratamento de Erros**

#### **3.2.1 Padrão de Erros Customizados**
```typescript
// Base error class
export class AppError extends Error {
  constructor(
    public message: string,
    public code: string,
    public statusCode: number = 500,
    public isOperational: boolean = true
  ) {
    super(message);
    Object.setPrototypeOf(this, AppError.prototype);
    Error.captureStackTrace(this, this.constructor);
  }
}

// Specific errors
export class ValidationError extends AppError {
  constructor(message: string, details?: any) {
    super(message, 'VALIDATION_ERROR', 400);
    this.details = details;
  }
}

export class NotFoundError extends AppError {
  constructor(resource: string) {
    super(`${resource} not found`, 'NOT_FOUND', 404);
  }
}

// Uso
async function getUser(id: string): Promise<User> {
  const user = await userRepository.findById(id);
  
  if (!user) {
    throw new NotFoundError('User');
  }
  
  return user;
}
```

### **3.3 Testes**

#### **3.3.1 Estrutura de Testes**
```typescript
// user.service.test.ts
describe('UserService', () => {
  let userService: UserService;
  let mockRepository: jest.Mocked<UserRepository>;

  beforeEach(() => {
    mockRepository = createMockRepository();
    userService = new UserService(mockRepository);
  });

  describe('createUser', () => {
    it('should create a new user with valid data', async () => {
      // Arrange
      const userData = {
        name: 'John Doe',
        email: 'john@example.com'
      };
      const expectedUser = new User({ ...userData, id: '123' });
      mockRepository.save.mockResolvedValue(expectedUser);

      // Act
      const result = await userService.createUser(userData);

      // Assert
      expect(result).toEqual(expectedUser);
      expect(mockRepository.save).toHaveBeenCalledWith(
        expect.objectContaining(userData)
      );
    });

    it('should throw ValidationError for invalid email', async () => {
      // Arrange
      const userData = {
        name: 'John Doe',
        email: 'invalid-email'
      };

      // Act & Assert
      await expect(userService.createUser(userData))
        .rejects
        .toThrow(ValidationError);
    });
  });
});
```

#### **3.3.2 Cobertura de Testes**
| Tipo de Teste | Cobertura Mínima | Ferramentas |
|---------------|------------------|-------------|
| Unitários | 80% | Jest, Mocha |
| Integração | 60% | Jest, Supertest |
| E2E | Fluxos críticos | Cypress, Playwright |
| Performance | APIs críticas | K6, JMeter |

---

## **4. Políticas de Segurança e Compliance**

### **4.1 Segurança de Código**

#### **4.1.1 OWASP Top 10 - Prevenções**

##### **SQL Injection**
```typescript
// ❌ Vulnerável
const query = `SELECT * FROM users WHERE id = ${userId}`;

// ✅ Seguro - Prepared statements
const query = 'SELECT * FROM users WHERE id = $1';
const result = await db.query(query, [userId]);

// ✅ Seguro - Query builder
const user = await db('users')
  .where('id', userId)
  .first();
```

##### **XSS Prevention**
```typescript
// ❌ Vulnerável
element.innerHTML = userInput;

// ✅ Seguro - React (auto-escaping)
return <div>{userInput}</div>;

// ✅ Seguro - Manual escaping
import DOMPurify from 'dompurify';
element.innerHTML = DOMPurify.sanitize(userInput);
```

##### **Authentication & Authorization**
```typescript
// Middleware de autenticação
export const authenticate = async (
  req: Request, 
  res: Response, 
  next: NextFunction
) => {
  try {
    const token = req.headers.authorization?.split(' ')[1];
    
    if (!token) {
      throw new UnauthorizedError('Token not provided');
    }

    const decoded = jwt.verify(token, process.env.JWT_SECRET!);
    req.user = decoded as User;
    
    next();
  } catch (error) {
    next(new UnauthorizedError('Invalid token'));
  }
};

// Middleware de autorização
export const authorize = (
  permissions: string[]
) => (req: Request, res: Response, next: NextFunction) => {
  const user = req.user as User;
  
  if (!hasPermissions(user, permissions)) {
    throw new ForbiddenError('Insufficient permissions');
  }
  
  next();
};
```

#### **4.1.2 Tratamento de Dados Sensíveis (LGPD)**
```typescript
// Anonimização de dados
export class DataAnonymizer {
  static anonymizeEmail(email: string): string {
    const [local, domain] = email.split('@');
    const maskedLocal = local.substring(0, 2) + '*'.repeat(local.length - 2);
    return `${maskedLocal}@${domain}`;
  }

  static anonymizePhone(phone: string): string {
    return phone.replace(/(\d{2})(\d{4,5})(\d{4})/, '$1****$3');
  }

  static anonymizeCpf(cpf: string): string {
    return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.***.***-$4');
  }
}

// Criptografia de dados sensíveis
export class EncryptionService {
  private static readonly algorithm = 'aes-256-gcm';
  private static readonly key = process.env.ENCRYPTION_KEY!;

  static encrypt(text: string): EncryptedData {
    const iv = crypto.randomBytes(16);
    const cipher = crypto.createCipher(this.algorithm, this.key);
    cipher.setAAD(Buffer.from('CSI-System'));
    
    let encrypted = cipher.update(text, 'utf8', 'hex');
    encrypted += cipher.final('hex');
    
    const authTag = cipher.getAuthTag();
    
    return {
      encrypted,
      iv: iv.toString('hex'),
      authTag: authTag.toString('hex')
    };
  }

  static decrypt(data: EncryptedData): string {
    const decipher = crypto.createDecipher(this.algorithm, this.key);
    decipher.setAAD(Buffer.from('CSI-System'));
    decipher.setAuthTag(Buffer.from(data.authTag, 'hex'));
    
    let decrypted = decipher.update(data.encrypted, 'hex', 'utf8');
    decrypted += decipher.final('utf8');
    
    return decrypted;
  }
}
```

### **4.2 Auditoria e Logs**

#### **4.2.1 Sistema de Auditoria**
```php
<?php

namespace App\Services;

use App\Models\LogAuditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Registra uma ação de auditoria
     */
    public function logAction(
        string $action,
        string $resource,
        array $data = [],
        ?int $resourceId = null
    ): void {
        LogAuditoria::create([
            'tenant_id' => Auth::user()?->tenant_id,
            'user_id' => Auth::id(),
            'action' => $action,
            'resource' => $resource,
            'resource_id' => $resourceId,
            'data' => $data,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'timestamp' => now()
        ]);
    }

    /**
     * Log de acesso a dados sensíveis
     */
    public function logDataAccess(
        string $dataType,
        int $recordId,
        array $fields = []
    ): void {
        $this->logAction('data_access', $dataType, [
            'fields_accessed' => $fields,
            'compliance_level' => 'LGPD'
        ], $recordId);
    }

    /**
     * Log de exportação de dados
     */
    public function logDataExport(
        string $format,
        array $filters,
        int $recordCount
    ): void {
        $this->logAction('data_export', 'bulk_data', [
            'format' => $format,
            'filters' => $filters,
            'record_count' => $recordCount,
            'export_reason' => request('export_reason')
        ]);
    }
}
```

### **4.3 Performance e Otimização**

#### **4.3.1 Otimização de Queries**
```php
<?php

namespace App\Repositories;

use App\Models\Kpi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class KpiRepository
{
    /**
     * Query otimizada para dashboard
     */
    public function getKpisForDashboard(
        int $tenantId,
        array $perfilKpis
    ): Collection {
        return Kpi::query()
            ->select([
                'id',
                'nome',
                'valor_calculado',
                'meta',
                'periodo_calculo',
                'ultima_atualizacao'
            ])
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $perfilKpis)
            ->where('ativo', true)
            ->with(['categoria' => function ($query) {
                $query->select('id', 'nome', 'cor');
            }])
            ->orderBy('ordem_exibicao')
            ->get();
    }

    /**
     * Bulk update otimizado para recálculos
     */
    public function bulkUpdateKpiValues(array $updates): void
    {
        DB::transaction(function () use ($updates) {
            $cases = [];
            $ids = [];
            
            foreach ($updates as $id => $valor) {
                $cases[] = "WHEN {$id} THEN {$valor}";
                $ids[] = $id;
            }
            
            $casesString = implode(' ', $cases);
            $idsString = implode(',', $ids);
            
            DB::update("
                UPDATE kpis 
                SET valor_calculado = CASE id {$casesString} END,
                    ultima_atualizacao = NOW()
                WHERE id IN ({$idsString})
            ");
        });
    }
}
```

---

## **5. Exemplos Práticos de Implementação**

### **5.1 Módulo de KPIs - Exemplo Completo**

#### **5.1.1 Model e Relacionamentos**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kpi extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'nome',
        'descricao',
        'formula_calculo',
        'meta',
        'periodo_calculo',
        'tipo_agregacao',
        'ativo',
        'ordem_exibicao'
    ];

    protected $casts = [
        'formula_calculo' => 'array',
        'meta' => 'decimal:2',
        'valor_calculado' => 'decimal:2',
        'ativo' => 'boolean',
        'ultima_atualizacao' => 'datetime'
    ];

    // Relacionamentos
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(KpiCategoria::class, 'kpi_categoria_id');
    }

    public function historico(): HasMany
    {
        return $this->hasMany(KpiHistorico::class);
    }

    // Scopes
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeParaDashboard($query)
    {
        return $query->ativos()
            ->orderBy('ordem_exibicao')
            ->with('categoria');
    }

    // Accessors
    public function getStatusMetaAttribute(): string
    {
        if (!$this->meta || !$this->valor_calculado) {
            return 'sem_meta';
        }

        $percentual = ($this->valor_calculado / $this->meta) * 100;

        return match (true) {
            $percentual >= 100 => 'atingida',
            $percentual >= 80 => 'proxima',
            default => 'distante'
        };
    }

    public function getPercentualMetaAttribute(): ?float
    {
        if (!$this->meta || !$this->valor_calculado) {
            return null;
        }

        return ($this->valor_calculado / $this->meta) * 100;
    }
}
```

#### **5.1.2 Service Layer**
```php
<?php

namespace App\Services;

use App\Models\Kpi;
use App\Repositories\KpiRepository;
use App\Jobs\CalculateKpiJob;
use Carbon\Carbon;

class KpiCalculationService
{
    public function __construct(
        private KpiRepository $kpiRepository,
        private AuditLogService $auditService
    ) {}

    /**
     * Calcula um KPI específico
     */
    public function calculateKpi(Kpi $kpi): float
    {
        $this->auditService->logAction(
            'kpi_calculation_started',
            'kpi',
            ['kpi_id' => $kpi->id],
            $kpi->id
        );

        try {
            $valor = match ($kpi->tipo_agregacao) {
                'sum' => $this->calculateSum($kpi),
                'avg' => $this->calculateAverage($kpi),
                'count' => $this->calculateCount($kpi),
                'custom' => $this->calculateCustom($kpi),
                default => throw new \InvalidArgumentException("Tipo de agregação inválido: {$kpi->tipo_agregacao}")
            };

            // Atualiza o KPI
            $kpi->update([
                'valor_calculado' => $valor,
                'ultima_atualizacao' => now()
            ]);

            // Salva no histórico
            $kpi->historico()->create([
                'valor' => $valor,
                'periodo_referencia' => $this->getPeriodoReferencia($kpi),
                'calculado_em' => now()
            ]);

            $this->auditService->logAction(
                'kpi_calculation_completed',
                'kpi',
                ['valor_calculado' => $valor],
                $kpi->id
            );

            return $valor;
        } catch (\Exception $e) {
            $this->auditService->logAction(
                'kpi_calculation_failed',
                'kpi',
                ['error' => $e->getMessage()],
                $kpi->id
            );

            throw $e;
        }
    }

    /**
     * Agenda recálculo de todos os KPIs do tenant
     */
    public function scheduleKpiRecalculation(int $tenantId): void
    {
        $kpis = $this->kpiRepository->getKpisForRecalculation($tenantId);

        foreach ($kpis as $kpi) {
            CalculateKpiJob::dispatch($kpi)
                ->delay(now()->addMinutes(rand(1, 5))); // Distribui a carga
        }
    }

    private function calculateSum(Kpi $kpi): float
    {
        $formula = $kpi->formula_calculo;
        $periodo = $this->getPeriodoReferencia($kpi);

        // Exemplo para KPI de faturamento MRR
        if ($formula['origem'] === 'erp_faturamento') {
            return DB::table('integracoes_erp')
                ->where('tenant_id', $kpi->tenant_id)
                ->where('tipo', 'faturamento_mrr')
                ->whereBetween('data_referencia', $periodo)
                ->sum('valor');
        }

        return 0;
    }

    private function calculateCustom(Kpi $kpi): float
    {
        // Implementação de fórmulas customizadas
        $formula = $kpi->formula_calculo;
        
        // Parser de fórmulas matemáticas
        $parser = new \NXP\MathExecutor();
        
        // Substitui variáveis por valores reais
        foreach ($formula['variaveis'] as $variavel => $config) {
            $valor = $this->getValorVariavel($kpi->tenant_id, $config);
            $parser->setVar($variavel, $valor);
        }

        return $parser->execute($formula['expressao']);
    }

    private function getPeriodoReferencia(Kpi $kpi): array
    {
        return match ($kpi->periodo_calculo) {
            'mensal' => [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ],
            'trimestral' => [
                Carbon::now()->startOfQuarter(),
                Carbon::now()->endOfQuarter()
            ],
            'anual' => [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear()
            ],
            default => [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ]
        };
    }
}
```

#### **5.1.3 API Controller**
```php
<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Kpi\StoreKpiRequest;
use App\Http\Requests\V1\Kpi\UpdateKpiRequest;
use App\Http\Resources\KpiResource;
use App\Models\Kpi;
use App\Services\KpiCalculationService;
use Illuminate\Http\JsonResponse;

class KpiController extends Controller
{
    public function __construct(
        private KpiCalculationService $kpiService
    ) {}

    /**
     * Lista KPIs do tenant atual
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Kpi::class);

        $kpis = Kpi::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->paraDashboard()
            ->get();

        return response()->json([
            'data' => KpiResource::collection($kpis),
            'message' => 'KPIs listados com sucesso'
        ]);
    }

    /**
     * Cria um novo KPI
     */
    public function store(StoreKpiRequest $request): JsonResponse
    {
        $kpi = Kpi::create([
            ...$request->validated(),
            'tenant_id' => auth()->user()->tenant_id
        ]);

        return response()->json([
            'data' => new KpiResource($kpi),
            'message' => 'KPI criado com sucesso'
        ], 201);
    }

    /**
     * Força recálculo de um KPI
     */
    public function recalculate(Kpi $kpi): JsonResponse
    {
        $this->authorize('update', $kpi);

        try {
            $valor = $this->kpiService->calculateKpi($kpi);

            return response()->json([
                'data' => [
                    'kpi_id' => $kpi->id,
                    'valor_calculado' => $valor,
                    'calculado_em' => now()
                ],
                'message' => 'KPI recalculado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao recalcular KPI',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Histórico de um KPI
     */
    public function historico(Kpi $kpi): JsonResponse
    {
        $this->authorize('view', $kpi);

        $historico = $kpi->historico()
            ->orderBy('calculado_em', 'desc')
            ->limit(30)
            ->get();

        return response()->json([
            'data' => $historico,
            'message' => 'Histórico recuperado com sucesso'
        ]);
    }
}
```

### **5.2 Módulo de Integrações - Exemplo ERP**

#### **5.2.1 Job de Integração**
```php
<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\IntegrationService;
use App\Services\AuditLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportErpDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutos
    public $tries = 3;

    public function __construct(
        private Tenant $tenant,
        private string $dataType,
        private array $config = []
    ) {}

    public function handle(
        IntegrationService $integrationService,
        AuditLogService $auditService
    ): void {
        $auditService->logAction(
            'integration_started',
            'erp_import',
            [
                'data_type' => $this->dataType,
                'config' => $this->config
            ]
        );

        try {
            $result = $integrationService->importFromERP(
                $this->tenant,
                $this->dataType,
                $this->config
            );

            $auditService->logAction(
                'integration_completed',
                'erp_import',
                [
                    'records_imported' => $result['imported'],
                    'records_updated' => $result['updated'],
                    'records_failed' => $result['failed']
                ]
            );
        } catch (\Exception $e) {
            $auditService->logAction(
                'integration_failed',
                'erp_import',
                ['error' => $e->getMessage()]
            );

            // Reagenda para tentar novamente
            if ($this->attempts() < $this->tries) {
                $this->release(now()->addMinutes(pow(2, $this->attempts())));
            }

            throw $e;
        }
    }
}
```

### **5.3 Frontend - Exemplo Dashboard Vue.js**

#### **5.3.1 Composable de KPIs**
```typescript
// composables/useKpis.ts
import { ref, computed } from 'vue';
import { kpiService } from '@/services/kpiService';
import { useNotifications } from '@/composables/useNotifications';
import type { IKpi, IKpiFilters } from '@/types/api';

export function useKpis() {
  const kpis = ref<IKpi[]>([]);
  const isLoading = ref(false);
  const error = ref<string | null>(null);
  const { showError } = useNotifications();

  const kpisPorCategoria = computed(() => {
    return kpis.value.reduce((acc, kpi) => {
      const categoria = kpi.categoria?.nome || 'Sem Categoria';
      if (!acc[categoria]) {
        acc[categoria] = [];
      }
      acc[categoria].push(kpi);
      return acc;
    }, {} as Record<string, IKpi[]>);
  });

  const kpisComMeta = computed(() => {
    return kpis.value.filter(kpi => kpi.meta && kpi.meta > 0);
  });

  async function fetchKpis(filters?: IKpiFilters): Promise<void> {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await kpiService.getKpis(filters);
      kpis.value = response.data;
    } catch (err) {
      error.value = 'Erro ao carregar KPIs';
      showError('Erro ao carregar KPIs');
      console.error('Erro ao buscar KPIs:', err);
    } finally {
      isLoading.value = false;
    }
  }

  async function recalculateKpi(kpiId: number): Promise<void> {
    try {
      const response = await kpiService.recalculateKpi(kpiId);
      
      // Atualiza o KPI na lista local
      const index = kpis.value.findIndex(k => k.id === kpiId);
      if (index !== -1) {
        kpis.value[index] = {
          ...kpis.value[index],
          valor_calculado: response.data.valor_calculado,
          ultima_atualizacao: response.data.calculado_em
        };
      }
    } catch (err) {
      showError('Erro ao recalcular KPI');
      throw err;
    }
  }

  return {
    kpis,
    isLoading,
    error,
    kpisPorCategoria,
    kpisComMeta,
    fetchKpis,
    recalculateKpi
  };
}
```

#### **5.3.2 Componente Dashboard**
```vue
<!-- views/kpis/KpisDashboard.vue -->
<template>
  <div class="kpis-dashboard">
    <div class="dashboard-header">
      <h1 class="dashboard-title">Dashboard de KPIs</h1>
      <div class="dashboard-controls">
        <button 
          class="btn btn--secondary"
          @click="refreshKpis"
          :disabled="isLoading"
        >
          <RefreshIcon class="btn-icon" />
          Atualizar
        </button>
        
        <select 
          v-model="selectedPeriodo"
          class="form-select"
          @change="applyFilters"
        >
          <option value="mensal">Este Mês</option>
          <option value="trimestral">Este Trimestre</option>
          <option value="anual">Este Ano</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Carregando KPIs...</p>
    </div>

    <!-- KPIs por Categoria -->
    <div v-else class="kpis-grid">
      <div 
        v-for="(kpisCategoria, categoria) in kpisPorCategoria"
        :key="categoria"
        class="kpi-category"
      >
        <h2 class="category-title">{{ categoria }}</h2>
        
        <div class="kpi-cards">
          <kpi-card
            v-for="kpi in kpisCategoria"
            :key="kpi.id"
            :kpi="kpi"
            @recalculate="handleRecalculate"
            @view-details="handleViewDetails"
          />
        </div>
      </div>
    </div>

    <!-- Modal de Detalhes -->
    <kpi-details-modal
      v-model:show="showDetailsModal"
      :kpi="selectedKpi"
      @close="closeDetailsModal"
    />
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useKpis } from '@/composables/useKpis';
import { useNotifications } from '@/composables/useNotifications';
import KpiCard from '@/components/kpis/KpiCard.vue';
import KpiDetailsModal from '@/components/kpis/KpiDetailsModal.vue';
import RefreshIcon from '@/components/icons/RefreshIcon.vue';
import type { IKpi } from '@/types/api';

// Composables
const { 
  kpis, 
  isLoading, 
  kpisPorCategoria, 
  fetchKpis, 
  recalculateKpi 
} = useKpis();
const { showSuccess, showError } = useNotifications();

// Estado local
const selectedPeriodo = ref('mensal');
const showDetailsModal = ref(false);
const selectedKpi = ref<IKpi | null>(null);

// Métodos
const refreshKpis = async (): Promise<void> => {
  await fetchKpis({ periodo: selectedPeriodo.value });
};

const applyFilters = async (): Promise<void> => {
  await fetchKpis({ periodo: selectedPeriodo.value });
};

const handleRecalculate = async (kpi: IKpi): Promise<void> => {
  try {
    await recalculateKpi(kpi.id);
    showSuccess(`KPI "${kpi.nome}" recalculado com sucesso`);
  } catch (error) {
    showError(`Erro ao recalcular KPI "${kpi.nome}"`);
  }
};

const handleViewDetails = (kpi: IKpi): void => {
  selectedKpi.value = kpi;
  showDetailsModal.value = true;
};

const closeDetailsModal = (): void => {
  showDetailsModal.value = false;
  selectedKpi.value = null;
};

// Lifecycle
onMounted(() => {
  fetchKpis({ periodo: selectedPeriodo.value });
});
</script>

<style scoped lang="scss">
.kpis-dashboard {
  padding: 2rem;
  min-height: 100vh;
  background: var(--color-background);
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--color-border);

  .dashboard-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--color-text-primary);
    margin: 0;
  }

  .dashboard-controls {
    display: flex;
    gap: 1rem;
    align-items: center;
  }
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  text-align: center;

  .loading-spinner {
    width: 3rem;
    height: 3rem;
    border: 3px solid var(--color-border);
    border-top-color: var(--color-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
  }
}

.kpis-grid {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.kpi-category {
  .category-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--color-text-secondary);
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .kpi-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

// Responsividade
@media (max-width: 768px) {
  .kpis-dashboard {
    padding: 1rem;
  }

  .dashboard-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;

    .dashboard-controls {
      justify-content: center;
    }
  }

  .kpi-cards {
    grid-template-columns: 1fr;
  }
}
</style>
```

---

## **6. Políticas de Versionamento e DevOps**

### **6.1 Versionamento de API**

#### **6.1.1 Estratégia de Versionamento**
```php
<?php

// Versionamento por URL path
Route::prefix('api/v1')->group(function () {
    Route::apiResource('tenants', TenantController::class);
    Route::apiResource('kpis', KpiController::class);
});

Route::prefix('api/v2')->group(function () {
    Route::apiResource('tenants', V2\TenantController::class);
    Route::apiResource('kpis', V2\KpiController::class);
});

// Middleware de versionamento
class ApiVersionMiddleware
{
    public function handle($request, Closure $next, $version = 'v1')
    {
        $request->attributes->set('api_version', $version);
        
        // Define configurações específicas da versão
        config(['api.version' => $version]);
        
        return $next($request);
    }
}
```

#### **6.1.2 Backward Compatibility**
```php
<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            'id' => $this->id,
            'nome' => $this->nome,
            'ativo' => $this->ativo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];

        // Campos deprecated com aviso
        if (request()->header('Accept-Deprecated-Fields') === 'true') {
            $data['status'] = $this->ativo ? 'active' : 'inactive'; // Deprecated
        }

        return $data;
    }
}
```

### **6.2 Pipeline de Deploy (Azure DevOps)**

#### **6.2.1 azure-pipelines.yml**
```yaml
trigger:
  branches:
    include:
      - main
      - develop
      - release/*

variables:
  azureSubscription: 'CSI-Production'
  resourceGroupName: 'rg-csi-prod'
  webAppName: 'app-csi-prod'

stages:
- stage: Test
  displayName: 'Run Tests'
  jobs:
  - job: BackendTests
    displayName: 'Backend Tests (Laravel)'
    pool:
      vmImage: 'ubuntu-latest'
    steps:
    - task: Setup@1
      displayName: 'Setup PHP 8.4'
      inputs:
        version: '8.4'
    
    - script: |
        composer install --no-dev --optimize-autoloader
        cp .env.testing .env
        php artisan key:generate
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
      displayName: 'Setup Laravel'
    
    - script: |
        php artisan test --coverage-html coverage/
      displayName: 'Run PHP Tests'
    
    - task: PublishTestResults@2
      inputs:
        testResultsFormat: 'JUnit'
        testResultsFiles: 'tests/results.xml'
        mergeTestResults: true
    
    - task: PublishCodeCoverageResults@1
      inputs:
        codeCoverageTool: 'PHPUnit'
        summaryFileLocation: 'coverage/clover.xml'

  - job: FrontendTests
    displayName: 'Frontend Tests (Vue.js)'
    pool:
      vmImage: 'ubuntu-latest'
    steps:
    - task: NodeTool@0
      inputs:
        versionSpec: '18.x'
    
    - script: |
        cd frontend
        npm ci
        npm run build
        npm run test:unit
        npm run test:e2e
      displayName: 'Frontend Tests'

- stage: Build
  displayName: 'Build Application'
  dependsOn: Test
  condition: succeeded()
  jobs:
  - job: BuildApp
    displayName: 'Build and Package'
    pool:
      vmImage: 'ubuntu-latest'
    steps:
    - script: |
        composer install --no-dev --optimize-autoloader
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
      displayName: 'Optimize Laravel'
    
    - script: |
        cd frontend
        npm ci
        npm run build
      displayName: 'Build Frontend'
    
    - task: ArchiveFiles@2
      inputs:
        rootFolderOrFile: '$(System.DefaultWorkingDirectory)'
        includeRootFolder: false
        archiveType: 'zip'
        archiveFile: '$(Build.ArtifactStagingDirectory)/csi-app.zip'
    
    - task: PublishBuildArtifacts@1
      inputs:
        pathToPublish: '$(Build.ArtifactStagingDirectory)'
        artifactName: 'csi-package'

- stage: Deploy
  displayName: 'Deploy to Azure'
  dependsOn: Build
  condition: and(succeeded(), eq(variables['Build.SourceBranch'], 'refs/heads/main'))
  jobs:
  - deployment: DeployToProduction
    displayName: 'Deploy to Production'
    environment: 'production'
    pool:
      vmImage: 'ubuntu-latest'
    strategy:
      runOnce:
        deploy:
          steps:
          - task: AzureWebApp@1
            inputs:
              azureSubscription: '$(azureSubscription)'
              appType: 'webAppLinux'
              appName: '$(webAppName)'
              package: '$(Pipeline.Workspace)/csi-package/csi-app.zip'
              deploymentMethod: 'zipDeploy'
          
          - task: AzureCLI@2
            displayName: 'Run Migrations'
            inputs:
              azureSubscription: '$(azureSubscription)'
              scriptType: 'bash'
              scriptLocation: 'inlineScript'
              inlineScript: |
                az webapp exec --resource-group $(resourceGroupName) \
                               --name $(webAppName) \
                               --exec "php artisan migrate --force"
```

### **6.3 Monitoramento e Observabilidade**

#### **6.3.1 Application Insights Integration**
```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Microsoft\ApplicationInsights\TelemetryClient;

class ApplicationInsightsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TelemetryClient::class, function () {
            return new TelemetryClient([
                'InstrumentationKey' => config('services.application_insights.key')
            ]);
        });
    }

    public function boot(): void
    {
        // Middleware para tracking automático
        $this->app['router']->pushMiddleware(TrackRequestMiddleware::class);
        
        // Event listeners para métricas customizadas
        Event::listen('kpi.calculated', function ($event) {
            app(TelemetryClient::class)->trackEvent('KPI Calculated', [
                'tenant_id' => $event->kpi->tenant_id,
                'kpi_id' => $event->kpi->id,
                'valor' => $event->valor
            ]);
        });
    }
}
```

---

## **7. Conclusão e Próximos Passos**

### **7.1 Resumo das Especificações**
Este documento estabelece os padrões técnicos fundamentais para o desenvolvimento do CSI, abrangendo:

- **Convenções de código** para Laravel e Vue.js
- **Padrões de segurança** com foco em LGPD e OWASP
- **Estratégias de performance** e otimização
- **Exemplos práticos** de implementação
- **Pipelines de DevOps** para Azure

### **7.2 Checklist de Implementação**

#### **Fase 1 - Setup Inicial**
- [ ] Configurar ambiente de desenvolvimento
- [ ] Implementar autenticação multi-tenant
- [ ] Configurar pipeline de CI/CD
- [ ] Implementar logs de auditoria básicos

#### **Fase 2 - Módulos Core**
- [ ] Implementar gestão de KPIs
- [ ] Desenvolver sistema de integrações
- [ ] Criar dashboard responsivo
- [ ] Implementar cache strategy

#### **Fase 3 - Qualidade e Performance**
- [ ] Implementar testes automatizados (80%+ cobertura)
- [ ] Configurar monitoramento APM
- [ ] Otimizar queries de database
- [ ] Implementar políticas de segurança

#### **Fase 4 - Produção**
- [ ] Deploy em ambiente de produção
- [ ] Configurar backup e disaster recovery
- [ ] Documentar APIs
- [ ] Treinamento da equipe

### **7.3 Métricas de Qualidade**
| Métrica | Target | Ferramenta |
|---------|--------|------------|
| Cobertura de Testes | ≥ 80% | PHPUnit + Jest |
| Performance (Response Time) | < 500ms | Application Insights |
| Disponibilidade | ≥ 99.5% | Azure Monitor |
| Segurança | 0 vulnerabilidades críticas | OWASP ZAP |
| Code Quality | A+ | SonarCloud |

### **7.4 Manutenção e Evolução**
- **Revisão trimestral** dos padrões e convenções
- **Atualização semestral** das dependências
- **Análise anual** da arquitetura e refatorações necessárias
- **Treinamento contínuo** da equipe em novas tecnologias

---

**Documento atualizado em:** 16/07/2025  
**Próxima revisão:** 16/10/2025  
**Responsável:** Luan Felipe Tavares  
**Versão:** 1.0
