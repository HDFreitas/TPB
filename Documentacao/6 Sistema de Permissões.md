# 🔐 Sistema de Permissões - Documentação Técnica

## 📋 **Informações do Documento**
- **Projeto:** CSI - Customer Success Intelligence
- **Versão:** 1.0
- **Data:** 29/09/2025
- **Autor(es):** Luan Felipe Tavares

## 📑 **Documentos Relacionados**
- [Documentação de Requisitos do CSI v1.0](./1%20Documentação%20de%20Requisitos%20do%20CSI%20v1.0.md)
- [Documento de Arquitetura de Software](./2%20Documento%20de%20Arquitetura%20de%20Software.md)
- [Especificações Técnicas](./4%20Especificações%20Técnicas.md)
- [Documentação da Estrutura](./5%20Documentação%20da%20Estrutura.md)

## 📋 Visão Geral

Este documento apresenta a documentação completa do sistema de permissões implementado na plataforma, baseado no **Spatie Permission** com suporte a **multi-tenancy**. O sistema oferece controle granular de acesso através de permissões específicas por ação e módulo.

---

## 🎯 Características Principais

- ✅ **Permissões Granulares:** Controle específico por ação (visualizar, criar, editar, excluir)
- ✅ **Multi-tenancy:** Isolamento de permissões por tenant
- ✅ **Baseado no Spatie Permission:** Biblioteca robusta e testada
- ✅ **Middlewares Automáticos:** Verificação transparente de permissões
- ✅ **Flexibilidade Total:** Permissões diretas ao usuário ou via roles

---

## 📊 Estrutura do Sistema

### **🗄️ Tabelas do Banco de Dados**

O sistema utiliza as tabelas padrão do Spatie Permission:

| Tabela | Descrição | Campos Principais |
|--------|-----------|-------------------|
| `permissions` | Armazena todas as permissões do sistema | `name`, `guard_name`, `team_id` |
| `roles` | Armazena os perfis/roles | `name`, `guard_name`, `team_id` |
| `model_has_permissions` | Permissões diretas dos usuários | `permission_id`, `model_id`, `team_id` |
| `model_has_roles` | Roles atribuídas aos usuários | `role_id`, `model_id`, `team_id` |
| `role_has_permissions` | Permissões das roles | `permission_id`, `role_id` |

### **🏗️ Arquitetura Multi-tenant**

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Tenant A      │    │   Tenant B      │    │   Tenant C      │
│                 │    │                 │    │                 │
│ Users ──────────┼────┼─ Permissions ───┼────┼─ Roles          │
│ Roles           │    │ (Isolated)      │    │ (Isolated)      │
│ Permissions     │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

---

## 🔑 Permissões Implementadas

### **📋 Estrutura de Permissões por Módulo**

| Módulo | Permissões Disponíveis |
|--------|------------------------|
| **Tenants** | `tenants.visualizar`, `tenants.criar`, `tenants.editar`, `tenants.excluir` |
| **Usuários** | `usuarios.visualizar`, `usuarios.criar`, `usuarios.editar`, `usuarios.excluir` |
| **Perfis** | `perfis.visualizar`, `perfis.criar`, `perfis.editar`, `perfis.excluir`, `perfis.gerenciar_hub` |
| **Permissões** | `permissoes.visualizar`, `permissoes.atribuir` |
| **Clientes** | `clientes.visualizar`, `clientes.criar`, `clientes.editar`, `clientes.excluir` |

### **👥 Distribuição por Roles**

#### **🔓 Role HUB (Super Administrador)**
```
✅ TODAS as permissões do sistema
├── Tenants (visualizar, criar, editar, excluir)
├── Usuários (visualizar, criar, editar, excluir)
├── Perfis (visualizar, criar, editar, excluir, gerenciar_hub)
├── Permissões (visualizar, atribuir)
└── Clientes (visualizar, criar, editar, excluir)
```

#### **👤 Role Administrador**
```
✅ Permissões limitadas (sem acesso a tenants)
├── ❌ Tenants (sem acesso)
├── ✅ Usuários (visualizar, criar, editar, excluir)
├── ✅ Perfis (visualizar, criar, editar, excluir)
├── ✅ Permissões (visualizar, atribuir)
└── ✅ Clientes (visualizar, criar, editar, excluir)
```

---

## ⚙️ Configuração Técnica

### **🔧 Configuração do Spatie Permission**

**Arquivo:** `backend/config/permission.php`

```php
return [
    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],
    
    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],
    
    // Suporte a multi-tenancy
    'teams' => true,
    'use_teams' => true,
];
```

### **🛡️ Middlewares Configurados**

**Arquivo:** `backend/bootstrap/app.php`

```php
$middleware->alias([
    'jwt' => JwtAuthMiddleware::class,
    'admin' => CheckAdminRole::class,
    'set.permission.team' => SetPermissionTeamMiddleware::class,
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
]);
```

### **👤 Modelo User**

**Arquivo:** `backend/app/Modules/Plataforma/Models/User.php`

```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use HasRoles;

    /**
     * Contexto multi-tenant para permissões
     */
    public function getPermissionTeamId(): ?int
    {
        return $this->tenant_id;
    }

    /**
     * Claims JWT com permissões
     */
    public function getJWTCustomClaims()
    {
        setPermissionsTeamId($this->tenant_id);
        
        return [
            'tenant_id' => $this->tenant_id,
            'roles' => $this->getRoleNames(),
            'permissions' => $this->getAllPermissions()->pluck('name'),
        ];
    }
}
```

---

## 🚀 Como Usar o Sistema

### **1. 🛡️ Protegendo Controllers**

#### **Exemplo: TenantsController**

```php
class TenantsController extends Controller
{
    public function __construct()
    {
        // Definir contexto do tenant
        $this->middleware('set.permission.team');
        
        // Permissões específicas por método
        $this->middleware('permission:tenants.visualizar')->only(['index', 'show']);
        $this->middleware('permission:tenants.criar')->only(['store']);
        $this->middleware('permission:tenants.editar')->only(['update']);
        $this->middleware('permission:tenants.excluir')->only(['destroy']);
    }
}
```

#### **Exemplo: Verificação Manual**

```php
public function someMethod()
{
    // Verificar permissão específica
    if (!auth()->user()->can('usuarios.editar')) {
        return response()->json(['message' => 'Sem permissão'], 403);
    }
    
    // Verificar múltiplas permissões
    if (auth()->user()->hasAnyPermission(['usuarios.criar', 'usuarios.editar'])) {
        // Usuário pode criar OU editar
    }
    
    // Verificar role
    if (auth()->user()->hasRole('HUB')) {
        // Usuário é HUB
    }
}
```

### **2. 🎯 Protegendo Rotas**

```php
// Rota com permissão específica
Route::get('/users', [UserController::class, 'index'])
    ->middleware(['jwt', 'permission:usuarios.visualizar']);

// Rota com role específica
Route::get('/admin', [AdminController::class, 'dashboard'])
    ->middleware(['jwt', 'role:HUB']);

// Rota com role OU permissão
Route::get('/reports', [ReportController::class, 'index'])
    ->middleware(['jwt', 'role_or_permission:HUB|relatorios.visualizar']);
```

### **3. 🔧 Gerenciando Permissões Programaticamente**

#### **Atribuir Permissões a Usuários**

```php
$user = User::find(1);

// Dar permissão específica
$user->givePermissionTo('usuarios.criar');

// Dar múltiplas permissões
$user->givePermissionTo(['usuarios.criar', 'usuarios.editar']);

// Remover permissão
$user->revokePermissionTo('usuarios.excluir');

// Sincronizar permissões (remove todas e adiciona as especificadas)
$user->syncPermissions(['usuarios.visualizar', 'usuarios.criar']);
```

#### **Atribuir Roles a Usuários**

```php
$user = User::find(1);

// Atribuir role
$user->assignRole('Administrador');

// Atribuir múltiplas roles
$user->assignRole(['Administrador', 'Editor']);

// Remover role
$user->removeRole('Editor');

// Sincronizar roles
$user->syncRoles(['Administrador']);
```

#### **Gerenciar Permissões de Roles**

```php
$role = Role::findByName('Administrador');

// Dar permissões à role
$role->givePermissionTo(['usuarios.visualizar', 'usuarios.criar']);

// Remover permissão da role
$role->revokePermissionTo('usuarios.excluir');

// Sincronizar permissões da role
$role->syncPermissions([
    'usuarios.visualizar',
    'usuarios.criar', 
    'usuarios.editar'
]);
```

### **4. 🎨 Frontend - Verificação de Permissões**

#### **Vue.js/TypeScript**

```typescript
// Store de autenticação
interface User {
  id: number;
  name: string;
  permissions: string[];
  roles: string[];
}

// Composable para permissões
export function usePermissions() {
  const authStore = useAuthStore();
  
  const hasPermission = (permission: string): boolean => {
    return authStore.user?.permissions?.includes(permission) ?? false;
  };
  
  const hasRole = (role: string): boolean => {
    return authStore.user?.roles?.includes(role) ?? false;
  };
  
  const hasAnyPermission = (permissions: string[]): boolean => {
    return permissions.some(permission => hasPermission(permission));
  };
  
  return { hasPermission, hasRole, hasAnyPermission };
}

// Uso em componentes
const { hasPermission } = usePermissions();

const canEditUsers = computed(() => hasPermission('usuarios.editar'));
const canViewTenants = computed(() => hasPermission('tenants.visualizar'));
```

#### **Proteção de Rotas no Frontend**

```typescript
// router/index.ts
const routes = [
  {
    path: '/tenants',
    component: TenantsView,
    meta: {
      requiresAuth: true,
      requiresPermission: 'tenants.visualizar'
    }
  },
  {
    path: '/users',
    component: UsersView,
    meta: {
      requiresAuth: true,
      requiresRole: 'Administrador'
    }
  }
];

// Guard de navegação
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  
  if (to.meta.requiresPermission) {
    if (!authStore.hasPermission(to.meta.requiresPermission)) {
      return next('/unauthorized');
    }
  }
  
  if (to.meta.requiresRole) {
    if (!authStore.hasRole(to.meta.requiresRole)) {
      return next('/unauthorized');
    }
  }
  
  next();
});
```

---

## 🔄 Middlewares Detalhados

### **1. 🏢 SetPermissionTeamMiddleware**

**Função:** Define o contexto do tenant para as permissões

```php
class SetPermissionTeamMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if ($user && $user->tenant_id) {
            setPermissionsTeamId($user->tenant_id);
        }
        return $next($request);
    }
}
```

### **2. 🔐 JwtAuthMiddleware**

**Função:** Autentica usuário e define contexto de permissões

```php
class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = JWTAuth::setToken($token)->toUser();
        
        // Definir contexto do tenant
        if ($user && $user->tenant_id) {
            setPermissionsTeamId($user->tenant_id);
        }
        
        return $next($request);
    }
}
```

### **3. 👑 CheckAdminRole**

**Função:** Verifica se usuário tem role administrativa

```php
class CheckAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        setPermissionsTeamId($user->tenant_id);
        $user = $user->fresh(['roles', 'permissions']);
        
        if (!$user->hasAnyRole(['Administrador', 'HUB'])) {
            return response()->json([
                'message' => 'Acesso negado. Apenas Administrador ou HUB.'
            ], 403);
        }
        
        return $next($request);
    }
}
```

---

## 🗃️ Seeders e Inicialização

### **1. 🌱 PermissionSeeder**

**Arquivo:** `backend/database/seeders/PermissionSeeder.php`

```php
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // TENANTS
            ['name' => 'tenants.visualizar', 'guard_name' => 'api'],
            ['name' => 'tenants.criar', 'guard_name' => 'api'],
            ['name' => 'tenants.editar', 'guard_name' => 'api'],
            ['name' => 'tenants.excluir', 'guard_name' => 'api'],
            
            // USUÁRIOS
            ['name' => 'usuarios.visualizar', 'guard_name' => 'api'],
            ['name' => 'usuarios.criar', 'guard_name' => 'api'],
            ['name' => 'usuarios.editar', 'guard_name' => 'api'],
            ['name' => 'usuarios.excluir', 'guard_name' => 'api'],
            
            // PERFIS
            ['name' => 'perfis.visualizar', 'guard_name' => 'api'],
            ['name' => 'perfis.criar', 'guard_name' => 'api'],
            ['name' => 'perfis.editar', 'guard_name' => 'api'],
            ['name' => 'perfis.excluir', 'guard_name' => 'api'],
            ['name' => 'perfis.gerenciar_hub', 'guard_name' => 'api'],
            
            // PERMISSÕES
            ['name' => 'permissoes.visualizar', 'guard_name' => 'api'],
            ['name' => 'permissoes.atribuir', 'guard_name' => 'api'],
            
            // CLIENTES
            ['name' => 'clientes.visualizar', 'guard_name' => 'api'],
            ['name' => 'clientes.criar', 'guard_name' => 'api'],
            ['name' => 'clientes.editar', 'guard_name' => 'api'],
            ['name' => 'clientes.excluir', 'guard_name' => 'api'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }
    }
}
```

### **2. 🎭 RolePermissionSeeder**

**Arquivo:** `backend/database/seeders/RolePermissionSeeder.php`

```php
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $rolePermissions = [
            'HUB' => [
                'tenants.visualizar', 'tenants.criar', 'tenants.editar', 'tenants.excluir',
                'usuarios.visualizar', 'usuarios.criar', 'usuarios.editar', 'usuarios.excluir',
                'perfis.visualizar', 'perfis.criar', 'perfis.editar', 'perfis.excluir', 'perfis.gerenciar_hub',
                'permissoes.visualizar', 'permissoes.atribuir',
                'clientes.visualizar', 'clientes.criar', 'clientes.editar', 'clientes.excluir',
            ],
            'Administrador' => [
                'usuarios.visualizar', 'usuarios.criar', 'usuarios.editar', 'usuarios.excluir',
                'perfis.visualizar', 'perfis.criar', 'perfis.editar', 'perfis.excluir',
                'permissoes.visualizar', 'permissoes.atribuir',
                'clientes.visualizar', 'clientes.criar', 'clientes.editar', 'clientes.excluir',
            ]
        ];

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            setPermissionsTeamId($tenant->id);
            
            foreach ($rolePermissions as $roleName => $permissions) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $role->syncPermissions($permissions);
                }
            }
        }
    }
}
```

---

## 🔍 Debugging e Troubleshooting

### **1. 🐛 Middleware de Debug**

```php
class DebugPermissionMiddleware
{
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = Auth::user();
        
        \Log::info("User: {$user->email}");
        \Log::info("Required permission: {$permission}");
        \Log::info("User tenant: {$user->tenant_id}");
        \Log::info("Current team context: " . getPermissionsTeamId());
        \Log::info("User permissions: " . $user->getAllPermissions()->pluck('name')->implode(', '));
        \Log::info("Has permission: " . ($user->can($permission) ? 'YES' : 'NO'));

        if (!$user->can($permission)) {
            throw UnauthorizedException::forPermissions([$permission]);
        }

        return $next($request);
    }
}
```

### **2. 🔧 Comandos Úteis**

```bash
# Limpar cache de permissões
php artisan permission:cache-reset

# Recriar permissões
php artisan db:seed --class=PermissionSeeder

# Recriar associações role-permission
php artisan db:seed --class=RolePermissionSeeder

# Verificar permissões de um usuário
php artisan tinker
>>> $user = User::find(1);
>>> $user->getAllPermissions()->pluck('name');
>>> $user->getRoleNames();
```

### **3. 🚨 Problemas Comuns**

#### **Permissão Negada Inesperadamente**

```php
// Verificar se o contexto do team está correto
dd(getPermissionsTeamId()); // Deve retornar o tenant_id do usuário

// Verificar permissões do usuário
dd(auth()->user()->getAllPermissions()->pluck('name'));

// Verificar se a permissão existe
dd(Permission::where('name', 'tenants.visualizar')->exists());
```

#### **Contexto Multi-tenant Incorreto**

```php
// Sempre definir o contexto antes de verificar permissões
setPermissionsTeamId($user->tenant_id);

// Recarregar usuário após definir contexto
$user = $user->fresh(['roles', 'permissions']);
```

---

## 📈 Boas Práticas

### **1. 🎯 Nomenclatura de Permissões**

```
Padrão: {modulo}.{acao}

✅ Correto:
- usuarios.visualizar
- tenants.criar
- perfis.editar

❌ Evitar:
- view_users
- create-tenant
- editProfile
```

### **2. 🔒 Princípio do Menor Privilégio**

```php
// ✅ Dar apenas as permissões necessárias
$user->givePermissionTo(['usuarios.visualizar', 'usuarios.editar']);

// ❌ Evitar dar permissões excessivas
$user->assignRole('HUB'); // Só se realmente precisar de acesso total
```

### **3. 🏗️ Organização de Middlewares**

```php
// ✅ Usar middlewares específicos
$this->middleware('permission:usuarios.criar')->only(['store']);

// ✅ Agrupar middlewares relacionados
Route::middleware(['jwt', 'set.permission.team'])->group(function () {
    Route::apiResource('users', UserController::class);
});
```

### **4. 🧪 Testes de Permissões**

```php
public function test_user_can_view_users_with_permission()
{
    $user = User::factory()->create();
    $user->givePermissionTo('usuarios.visualizar');
    
    $this->actingAs($user)
         ->get('/api/users')
         ->assertStatus(200);
}

public function test_user_cannot_view_users_without_permission()
{
    $user = User::factory()->create();
    
    $this->actingAs($user)
         ->get('/api/users')
         ->assertStatus(403);
}
```

---

## 📚 Referências

- **Spatie Permission:** https://spatie.be/docs/laravel-permission/
- **Laravel Authorization:** https://laravel.com/docs/authorization
- **JWT Authentication:** https://jwt-auth.readthedocs.io/

---

## 📞 Suporte

Para dúvidas ou problemas com o sistema de permissões:

1. Verificar logs em `storage/logs/laravel.log`
2. Usar o middleware de debug para investigar
3. Consultar esta documentação
4. Verificar a documentação oficial do Spatie Permission

---

**📅 Última atualização:** Setembro 2025  
**👨‍💻 Versão:** 1.0  
**🔧 Laravel:** 12.x  
**📦 Spatie Permission:** 6.x
