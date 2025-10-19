<?php
namespace App\Modules\Plataforma\Repositories;

use App\Repositories\BaseRepository;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Modules\Plataforma\Interfaces\PerfilRepositoryInterface;
use App\Modules\Plataforma\Models\User;
use Illuminate\Support\Facades\DB;

class PerfilRepository extends BaseRepository implements PerfilRepositoryInterface
{
    /**
     * Model class for repo.
     * 
     * @var string
     */
    protected $modelClass = Role::class;

    public function getAll($take = 15, $paginate = true, $tenantId = null, $canSeeHubRole = false, $filters = [])
    {
        $query = Role::query();

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        
        // Se o usuário não tem role HUB, esconder o perfil HUB
        if (!$canSeeHubRole) {
            $query->where('name', '!=', 'HUB');
        }

        // Filtros de pesquisa
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'ilike', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'ilike', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['status']) && $filters['status'] !== null) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('name');

        if ($paginate) {
            return $query->paginate($take);
        }
        
        if ($take === false) {
            return $query->get();
        }
        
        return $query->take($take)->get();
    }

    public function create(array $data)
    {
        try {
            // Lógica específica para criação de perfis/roles
            return Role::create([
                'name'        => $data['name'],
                'description' => $data['description'] ?? '', // String vazia ao invés de null
                'tenant_id'   => $data['tenant_id'],
                'guard_name'  => 'api', // Obrigatório para Spatie Permission
            ]);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            throw new \Exception("Já existe um perfil com o nome '{$data['name']}'. Por favor, escolha outro nome.");
        }
    }

    public function update($id, array $data)
    {
        try {
            // Lógica específica para atualização de perfis/roles
            $role = Role::findOrFail($id);
            if (isset($data['name']))        $role->name = $data['name'];
            if (isset($data['description'])) $role->description = $data['description'] ?? '';
            if (isset($data['tenant_id']))   $role->tenant_id = $data['tenant_id'];
            if (isset($data['status']))      $role->status = $data['status'];
            $role->save();
            return $role;
        } catch (\Illuminate\Database\QueryException $e) {
            // Verificar se é erro de constraint unique (nome duplicado)
            if ($e->getCode() === '23505' || strpos($e->getMessage(), 'unique') !== false) {
                $roleName = $data['name'] ?? 'este nome';
                throw new \Exception("Já existe um perfil com o nome '{$roleName}'. Por favor, escolha outro nome.");
            }
            throw $e;
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            $roleName = $data['name'] ?? 'este nome';
            throw new \Exception("Já existe um perfil com o nome '{$roleName}'. Por favor, escolha outro nome.");
        }
    }

    /**
     * Obter usuários associados a um perfil
     *
     * @param int $perfilId
     * @param int $tenantId
     * @return array
     */
    public function getUsersWithPerfil($perfilId, $tenantId)
    {
        $role = Role::findOrFail($perfilId);
        
        return User::whereHas('roles', function($query) use ($perfilId, $tenantId) {
            $query->where('roles.id', $perfilId)
                  ->where('roles.tenant_id', $tenantId);
        })
        ->where('tenant_id', $tenantId)
        ->select('id', 'name', 'email', 'usuario', 'status')
        ->get();
    }

    /**
     * Obter usuários disponíveis para associar a um perfil
     *
     * @param int $perfilId
     * @param int $tenantId
     * @return array
     */
    public function getAvailableUsers($perfilId, $tenantId)
    {
        return User::whereDoesntHave('roles', function($query) use ($perfilId, $tenantId) {
            $query->where('roles.id', $perfilId)
                  ->where('roles.tenant_id', $tenantId);
        })
        ->where('tenant_id', $tenantId)
        ->where('status', true)
        ->select('id', 'name', 'email', 'usuario')
        ->get();
    }

    /**
     * Obter dados completos de usuários para um perfil (associados + disponíveis)
     *
     * @param int $perfilId
     * @param int $tenantId
     * @return array
     */
    public function getPerfilUsersData($perfilId, $tenantId)
    {
        return [
            'associated' => $this->getUsersWithPerfil($perfilId, $tenantId),
            'available' => $this->getAvailableUsers($perfilId, $tenantId)
        ];
    }

    /**
     * Associar usuários a um perfil
     *
     * @param int $perfilId
     * @param array $userIds
     * @param int $tenantId
     * @return bool
     */
    public function associateUsers($perfilId, $userIds, $tenantId)
    {
        $role = Role::where('id', $perfilId)
                   ->where('tenant_id', $tenantId)
                   ->firstOrFail();

        $users = User::whereIn('id', $userIds)
                    ->where('tenant_id', $tenantId)
                    ->get();

        foreach ($users as $user) {
            if (!$user->hasRole($role->name)) {
                $user->assignRole($role->name);
            }
        }

        return true;
    }

    /**
     * Remover usuário de um perfil
     *
     * @param int $perfilId
     * @param int $userId
     * @param int $tenantId
     * @return bool
     */
    public function removeUserFromPerfil($perfilId, $userId, $tenantId)
    {
        $role = Role::where('id', $perfilId)
                   ->where('tenant_id', $tenantId)
                   ->firstOrFail();

        $user = User::where('id', $userId)
                   ->where('tenant_id', $tenantId)
                   ->firstOrFail();

        if ($user->hasRole($role->name)) {
            $user->removeRole($role->name);
        }

        return true;
    }

    /**
     * Obter permissões de um perfil
     *
     * @param int $perfilId
     * @param int $tenantId
     * @return array
     */
    public function getPerfilPermissions($perfilId, $tenantId)
    {
        $role = Role::where('id', $perfilId)
                   ->where('tenant_id', $tenantId)
                   ->with('permissions')
                   ->firstOrFail();

        // Permissões especiais que não devem aparecer na interface
        $hiddenPermissions = ['perfis.gerenciar_hub'];

        $allPermissions = Permission::where('guard_name', 'api')
                                   ->whereNotIn('name', $hiddenPermissions)
                                   ->get();
        
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return [
            'assigned' => $role->permissions
                ->whereNotIn('name', $hiddenPermissions)
                ->map(function($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'display_name' => $this->getPermissionDisplayName($permission->name)
                    ];
                })->values(),
            'available' => $allPermissions->whereNotIn('id', $rolePermissions)->map(function($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'display_name' => $this->getPermissionDisplayName($permission->name)
                ];
            })->values()
        ];
    }

    /**
     * Sincronizar permissões de um perfil
     *
     * @param int $perfilId
     * @param array $permissionIds
     * @param int $tenantId
     * @return bool
     */
    public function syncPerfilPermissions($perfilId, $permissionIds, $tenantId)
    {
        $role = Role::where('id', $perfilId)
                   ->where('tenant_id', $tenantId)
                   ->firstOrFail();

        $permissions = Permission::whereIn('id', $permissionIds)->get();
        $role->syncPermissions($permissions);

        return true;
    }

    /**
     * Obter nome amigável para exibição da permissão
     *
     * @param string $permissionName
     * @return string
     */
    private function getPermissionDisplayName($permissionName)
    {
        $displayNames = [
            // Tenants
            'tenants.visualizar' => 'Tenants - Visualizar',
            'tenants.criar' => 'Tenants - Criar',
            'tenants.editar' => 'Tenants - Editar',
            'tenants.excluir' => 'Tenants - Excluir',
            
            // Usuários
            'usuarios.visualizar' => 'Usuários - Visualizar',
            'usuarios.criar' => 'Usuários - Criar',
            'usuarios.editar' => 'Usuários - Editar',
            'usuarios.excluir' => 'Usuários - Excluir',
            
            // Perfis
            'perfis.visualizar' => 'Perfis - Visualizar',
            'perfis.criar' => 'Perfis - Criar',
            'perfis.editar' => 'Perfis - Editar',
            'perfis.excluir' => 'Perfis - Excluir',
            // 'perfis.gerenciar_hub' - Permissão interna, não aparece na interface
            
            // Permissões
            'permissoes.visualizar' => 'Permissões - Visualizar',
            'permissoes.atribuir' => 'Permissões - Atribuir',
            
            // Clientes
            'clientes.visualizar' => 'Clientes - Visualizar',
            'clientes.criar' => 'Clientes - Criar',
            'clientes.editar' => 'Clientes - Editar',
            'clientes.excluir' => 'Clientes - Excluir',

            // Contatos
            'contatos.visualizar' => 'Contatos - Visualizar',
            'contatos.criar' => 'Contatos - Criar',
            'contatos.editar' => 'Contatos - Editar',
            'contatos.excluir' => 'Contatos - Excluir',

            // Interações
            'interacoes.visualizar' => 'Interações - Visualizar',
            'interacoes.criar' => 'Interações - Criar',
            'interacoes.editar' => 'Interações - Editar',
            'interacoes.excluir' => 'Interações - Excluir',

            // Conectores
            'conectores.visualizar' => 'conectores - Visualizar',
            'conectores.criar' => 'Conectores - Criar',
            'conectores.editar' => 'Conectores - Editar',
            'conectores.excluir' => 'Conectores - Excluir',
            'conectores.testar' => 'Conectores - Testar',

            // Logs
            'logs.view' => 'Logs - Visualizar',
            'logs.create' => 'Logs - Criar',
            'logs.delete' => 'Logs - Excluir',
            'logs.dashboard' => 'Logs - Dashboard',
            'logs.view.all' => 'Logs - Visualizar Todos',

            //Tipos de Interação
            'tipos_interacao.visualizar' => 'Tipos de Interação - Visualizar',
            'tipos_interacao.criar' => 'Tipos de Interação - Criar',
            'tipos_interacao.editar' => 'Tipos de Interação - Editar',
            'tipos_interacao.excluir' => 'Tipos de Interação - Excluir',


        ];

        return $displayNames[$permissionName] ?? $permissionName;
    }
}