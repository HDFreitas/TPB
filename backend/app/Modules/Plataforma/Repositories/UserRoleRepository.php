<?php

namespace App\Modules\Plataforma\Repositories;

use App\Repositories\BaseRepository;
use App\Modules\Plataforma\Interfaces\UserRoleRepositoryInterface;
use App\Modules\Plataforma\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRoleRepository extends BaseRepository implements UserRoleRepositoryInterface
{
    /**
     * Model class for repo.
     * 
     * @var string
     */
    protected $modelClass = User::class;

    /**
     * Encontra um usuário pelo ID
     *
     * @param int $userId
     * @return User|null
     */
    public function findUserById(int $userId): ?User
    {
        return User::with(['roles', 'permissions'])->find($userId);
    }

    /**
     * Sincroniza roles de um usuário (substitui todas as roles existentes)
     *
     * @param User $user
     * @param array $roles
     * @return void
     */
    public function syncRolesToUser(User $user, array $roles): void
    {
        $user->syncRoles($roles);
    }

    /**
     * Atribui roles a um usuário (mantém as existentes)
     * Verifica se o usuário já tem a role antes de adicionar
     *
     * @param User $user
     * @param array $roles
     * @return void
     */
    public function assignRolesToUser(User $user, array $roles): void
    {
        $rolesToAssign = [];
        
        foreach ($roles as $role) {
            // Verifica se o usuário já tem esta role
            if (!$user->hasRole($role)) {
                $rolesToAssign[] = $role;
            }
        }
        
        // Só atribui as roles que o usuário ainda não tem
        if (!empty($rolesToAssign)) {
            $user->assignRole($rolesToAssign);
        }
    }

    /**
     * Remove roles específicas de um usuário
     *
     * @param User $user
     * @param array $roles
     * @return void
     */
    public function removeRolesFromUser(User $user, array $roles): void
    {
        foreach ($roles as $role) {
            $user->removeRole($role);
        }
    }

    /**
     * Verifica se um usuário tem uma role específica
     *
     * @param User $user
     * @param string $role
     * @return bool
     */
    public function userHasRole(User $user, string $role): bool
    {
        return $user->hasRole($role);
    }

    /**
     * Verifica se um usuário tem alguma das roles especificadas
     *
     * @param User $user
     * @param array $roles
     * @return bool
     */
    public function userHasAnyRole(User $user, array $roles): bool
    {
        return $user->hasAnyRole($roles);
    }

    /**
     * Verifica se um usuário tem todas as roles especificadas
     *
     * @param User $user
     * @param array $roles
     * @return bool
     */
    public function userHasAllRoles(User $user, array $roles): bool
    {
        return $user->hasAllRoles($roles);
    }

    /**
     * Obtém todos os usuários com suas roles por tenant
     *
     * @param int $tenantId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsersWithRolesByTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return User::with(['roles', 'permissions'])
            ->where('tenant_id', $tenantId)
            ->paginate($perPage);
    }

    /**
     * Obtém todas as roles disponíveis
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllRoles()
    {
        return Role::all();
    }

    /**
     * Encontra uma role pelo nome
     *
     * @param string $roleName
     * @return Role|null
     */
    public function findRoleByName(string $roleName): ?Role
    {
        return Role::findByName($roleName);
    }

    /**
     * Obtém usuários que têm uma role específica
     *
     * @param string $roleName
     * @param int $tenantId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsersByRole(string $roleName, int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return User::role($roleName)
            ->where('tenant_id', $tenantId)
            ->with(['roles', 'permissions'])
            ->paginate($perPage);
    }

    /**
     * Obtém usuários que têm uma permissão específica (direta ou via role)
     *
     * @param string $permissionName
     * @param int $tenantId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsersByPermission(string $permissionName, int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return User::permission($permissionName)
            ->where('tenant_id', $tenantId)
            ->with(['roles', 'permissions'])
            ->paginate($perPage);
    }
}
