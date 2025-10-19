<?php

namespace App\Modules\Plataforma\Interfaces;

use App\Interfaces\BaseInterface;
use App\Modules\Plataforma\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRoleRepositoryInterface extends BaseInterface
{
    /**
     * Encontra um usuário pelo ID
     *
     * @param int $userId
     * @return User|null
     */
    public function findUserById(int $userId): ?User;

    /**
     * Sincroniza roles de um usuário (substitui todas as roles existentes)
     *
     * @param User $user
     * @param array $roles
     * @return void
     */
    public function syncRolesToUser(User $user, array $roles): void;

    /**
     * Atribui roles a um usuário (mantém as existentes)
     *
     * @param User $user
     * @param array $roles
     * @return void
     */
    public function assignRolesToUser(User $user, array $roles): void;

    /**
     * Remove roles específicas de um usuário
     *
     * @param User $user
     * @param array $roles
     * @return void
     */
    public function removeRolesFromUser(User $user, array $roles): void;

    /**
     * Verifica se um usuário tem uma role específica
     *
     * @param User $user
     * @param string $role
     * @return bool
     */
    public function userHasRole(User $user, string $role): bool;

    /**
     * Verifica se um usuário tem alguma das roles especificadas
     *
     * @param User $user
     * @param array $roles
     * @return bool
     */
    public function userHasAnyRole(User $user, array $roles): bool;

    /**
     * Verifica se um usuário tem todas as roles especificadas
     *
     * @param User $user
     * @param array $roles
     * @return bool
     */
    public function userHasAllRoles(User $user, array $roles): bool;

    /**
     * Obtém todos os usuários com suas roles por tenant
     *
     * @param int $tenantId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsersWithRolesByTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Obtém todas as roles disponíveis
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllRoles();

    /**
     * Encontra uma role pelo nome
     *
     * @param string $roleName
     * @return \Spatie\Permission\Models\Role|null
     */
    public function findRoleByName(string $roleName);

    /**
     * Obtém usuários que têm uma role específica
     *
     * @param string $roleName
     * @param int $tenantId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsersByRole(string $roleName, int $tenantId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Obtém usuários que têm uma permissão específica (direta ou via role)
     *
     * @param string $permissionName
     * @param int $tenantId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsersByPermission(string $permissionName, int $tenantId, int $perPage = 15): LengthAwarePaginator;
}
