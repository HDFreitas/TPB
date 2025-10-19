<?php

namespace App\Modules\Plataforma\Services;

use App\Modules\Plataforma\Interfaces\TenantRepositoryInterface;
use App\Modules\Plataforma\Interfaces\PerfilRepositoryInterface;
use App\Modules\Plataforma\Interfaces\UserRepositoryInterface;
use App\Modules\Plataforma\Events\TenantStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TenantService
{
    protected $tenantRepository;
    protected $perfilRepository;
    protected $userRepository;

    public function __construct(
        TenantRepositoryInterface $tenantRepository,
        PerfilRepositoryInterface $perfilRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->tenantRepository = $tenantRepository;
        $this->perfilRepository = $perfilRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Cria um tenant com perfis padrão e usuário administrador
     *
     * @param array $tenantData
     * @param array|null $adminUserData
     * @return mixed
     */
    public function createTenantWithDefaults(array $tenantData, array $adminUserData = null)
    {
        return DB::transaction(function () use ($tenantData, $adminUserData) {
            // 1. Criar o tenant
            $tenant = $this->tenantRepository->create($tenantData);

            // 2. Criar perfis padrão (HUB e Administrador)
            $this->createDefaultProfiles($tenant->id);
            
            // 3. Atribuir permissões aos perfis criados
            $this->assignPermissionsToRoles($tenant->id);

            // 3. Criar usuário administrador padrão automaticamente
            $defaultAdminData = [
                'name' => 'Administrador',
                'email' => 'admin@' . $tenant->dominio . '.com.br',
                'password' => 'S@ncon.123',
                'usuario' => 'admin',
                'dominio' => $tenant->dominio,
            ];
            
            // \Log::info("Criando usuário admin para tenant {$tenant->id} - {$tenant->nome}");
            $adminUser = $this->createAdminUser($tenant->id, $defaultAdminData);
            // \Log::info("Usuário admin criado: {$adminUser->email} (ID: {$adminUser->id})");
            
            // \Log::info("Atribuindo role Administrador ao usuário {$adminUser->email}");
            $this->assignAdminRole($adminUser, $tenant->id);

            return $tenant;
        });
    }

    /**
     * Cria os perfis padrão para o tenant
     *
     * @param int $tenantId
     * @return void
     */
    protected function createDefaultProfiles(int $tenantId)
    {
        // \Log::info("Criando perfis padrão para tenant {$tenantId}");
        
        $perfisFixos = [
            [
                'name' => 'Administrador',
                'description' => 'Perfil de administração do tenant',
                'tenant_id' => $tenantId,
            ],
            [
                'name' => 'Usuario',
                'description' => 'Perfil de usuário do tenant',
                'tenant_id' => $tenantId,
            ],
        ];

        foreach ($perfisFixos as $perfil) {
            // Usar Role diretamente para garantir compatibilidade com Spatie Permission
            $role = Role::firstOrCreate(
                [
                    'tenant_id' => $perfil['tenant_id'],
                    'name' => $perfil['name'],
                    'guard_name' => 'api', // Forçar uso do guard 'api'
                ],
                [
                    'description' => $perfil['description'],
                    'guard_name' => 'api', // Garantir que o guard seja 'api'
                ]
            );
            
            // \Log::info("Perfil criado/encontrado: {$role->name} (ID: {$role->id}) para tenant {$tenantId}");
        }
    }

    /**
     * Atribui permissões aos perfis do tenant
     *
     * @param int $tenantId
     * @return void
     */
    protected function assignPermissionsToRoles(int $tenantId)
    {
        // \Log::info("Atribuindo permissões aos perfis do tenant {$tenantId}");
        
        // Definir permissões por role
        $rolePermissions = [
            'HUB' => [
                'tenants.visualizar', 'tenants.criar', 'tenants.editar', 'tenants.excluir',
                'usuarios.visualizar', 'usuarios.criar', 'usuarios.editar', 'usuarios.excluir',
                'perfis.visualizar', 'perfis.criar', 'perfis.editar', 'perfis.excluir', 'perfis.gerenciar_hub',
                'permissoes.visualizar', 'permissoes.atribuir',
                'clientes.visualizar', 'clientes.criar', 'clientes.editar', 'clientes.excluir',
                'contatos.visualizar', 'contatos.criar', 'contatos.editar', 'contatos.excluir',
                'interacoes.visualizar', 'interacoes.criar', 'interacoes.editar', 'interacoes.excluir',
                'conectores.visualizar', 'conectores.criar', 'conectores.editar', 'conectores.excluir',
            ],
            'Administrador' => [
                'usuarios.visualizar', 'usuarios.criar', 'usuarios.editar', 'usuarios.excluir',
                'perfis.visualizar', 'perfis.criar', 'perfis.editar', 'perfis.excluir',
                'permissoes.visualizar', 'permissoes.atribuir',
                'clientes.visualizar', 'clientes.criar', 'clientes.editar', 'clientes.excluir',
                'contatos.visualizar', 'contatos.criar', 'contatos.editar', 'contatos.excluir',
                'interacoes.visualizar', 'interacoes.criar', 'interacoes.editar', 'interacoes.excluir',
                'conectores.visualizar', 'conectores.criar', 'conectores.editar', 'conectores.excluir',
            ]
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)
                ->where('tenant_id', $tenantId)
                ->where('guard_name', 'api') // Forçar busca por guard 'api'
                ->first();
            
            if ($role) {
                foreach ($permissions as $permissionName) {
                    $permission = Permission::where('name', $permissionName)->where('guard_name', 'api')->first();
                    
                    if ($permission && !$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                        // \Log::info("Permissão {$permissionName} atribuída à role {$roleName} do tenant {$tenantId}");
                    }
                }
                
                // \Log::info("Total de permissões da role {$roleName}: " . $role->permissions->count());
            } else {
                // \Log::error("Role {$roleName} não encontrada para tenant {$tenantId}");
            }
        }
    }

    /**
     * Cria o usuário administrador para o tenant
     *
     * @param int $tenantId
     * @param array $adminUserData
     * @return mixed
     */
    protected function createAdminUser(int $tenantId, array $adminUserData)
    {
        // Preparar dados do usuário
        $userData = [
            'tenant_id' => $tenantId,
            'name' => $adminUserData['name'],
            'email' => $adminUserData['email'],
            'password' => Hash::make($adminUserData['password']),
            'usuario' => $adminUserData['usuario'] ?? $this->generateUsername($adminUserData['name']),
            'dominio' => $adminUserData['dominio'] ?? 'admin.' . strtolower(str_replace(' ', '', $adminUserData['name'])),
        ];

        return $this->userRepository->create($userData);
    }

    /**
     * Atribui o perfil de Administrador ao usuário
     *
     * @param mixed $user
     * @param int $tenantId
     * @return void
     */
    protected function assignAdminRole($user, int $tenantId)
    {
        // Buscar o perfil Administrador do tenant
        $adminRole = Role::where('tenant_id', $tenantId)
            ->where('name', 'Administrador')
            ->where('guard_name', 'api') // Forçar busca por guard 'api'
            ->first();

        if (!$adminRole) {
            // \Log::error("Role Administrador não encontrada para tenant {$tenantId}");
            return;
        }

        // Verificar se a associação já existe
        $exists = DB::table('model_has_roles')
            ->where('role_id', $adminRole->id)
            ->where('model_type', get_class($user))
            ->where('model_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->exists();

        if (!$exists) {
            // Inserir diretamente na tabela model_has_roles com tenant_id
            DB::table('model_has_roles')->insert([
                'role_id' => $adminRole->id,
                'model_type' => get_class($user),
                'model_id' => $user->id,
                'tenant_id' => $tenantId,
            ]);
            
            // \Log::info("Role Administrador atribuída ao usuário {$user->email} do tenant {$tenantId}");
        } else {
            // \Log::info("Usuário {$user->email} já possui a role Administrador no tenant {$tenantId}");
        }
    }

    /**
     * Atualiza um tenant e dispara eventos se necessário
     *
     * @param int $tenantId
     * @param array $data
     * @return mixed
     */
    public function updateTenant(int $tenantId, array $data)
    {
        // Buscar o tenant atual para comparar o status
        $currentTenant = $this->tenantRepository->findById($tenantId);
        $oldStatus = $currentTenant->status;
        
        // Atualizar o tenant
        $updatedTenant = $this->tenantRepository->update($tenantId, $data);
        
        // Verificar se o status mudou e disparar evento
        if (isset($data['status']) && $oldStatus !== $data['status']) {
            event(new TenantStatusChanged($updatedTenant, $oldStatus, $data['status']));
        }
        
        return $updatedTenant;
    }

    /**
     * Gera um nome de usuário baseado no nome
     *
     * @param string $name
     * @return string
     */
    protected function generateUsername(string $name)
    {
        return strtolower(str_replace(' ', '.', $name));
    }
}
