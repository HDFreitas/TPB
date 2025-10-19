<?php

namespace App\Services;

use App\Modules\Plataforma\Interfaces\UserRoleRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserRoleService
{
    protected $userRoleRepository;

    public function __construct(UserRoleRepositoryInterface $userRoleRepository)
    {
        $this->userRoleRepository = $userRoleRepository;
    }

    /**
     * Atribui roles a um usuário
     *
     * @param int $userId
     * @param array $roles
     * @return array
     * @throws \Exception
     */
    public function assignRolesToUser(int $userId, array $roles): array
    {
        try {
            DB::beginTransaction();

            // Definir o contexto de team baseado no usuário autenticado
            $authenticatedUser = auth()->user();
            setPermissionsTeamId($authenticatedUser->tenant_id);

            $user = $this->userRoleRepository->findUserById($userId);
            
            if (!$user) {
                throw new ModelNotFoundException('Usuário não encontrado.');
            }

            // Verifica se o usuário pertence ao mesmo tenant do usuário autenticado
            if ($user->tenant_id !== $authenticatedUser->tenant_id) {
                throw new \Exception('Você não tem permissão para modificar este usuário.');
            }

            // Verificar quais roles o usuário já tem
            $existingRoles = $user->getRoleNames()->toArray();
            $newRoles = array_diff($roles, $existingRoles);
            $alreadyHasRoles = array_intersect($roles, $existingRoles);

            // Atribui as roles ao usuário (adiciona sem remover as existentes)
            $this->userRoleRepository->assignRolesToUser($user, $roles);

            // Log da operação
            Log::info('Roles atribuídas ao usuário', [
                'user_id' => $userId,
                'requested_roles' => $roles,
                'new_roles' => $newRoles,
                'already_had_roles' => $alreadyHasRoles,
                'assigned_by' => auth()->user()->id
            ]);

            DB::commit();

            $message = 'Operação concluída.';
            if (!empty($newRoles)) {
                $message .= ' Roles adicionadas: ' . implode(', ', $newRoles) . '.';
            }
            if (!empty($alreadyHasRoles)) {
                $message .= ' Roles já existentes: ' . implode(', ', $alreadyHasRoles) . '.';
            }

            return [
                'user' => $user->fresh(['roles']),
                'requested_roles' => $roles,
                'new_roles' => $newRoles,
                'already_had_roles' => $alreadyHasRoles,
                'message' => $message
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atribuir roles ao usuário', [
                'user_id' => $userId,
                'roles' => $roles,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Remove roles de um usuário
     *
     * @param int $userId
     * @param array $roles
     * @return array
     * @throws \Exception
     */
    public function removeRolesFromUser(int $userId, array $roles): array
    {
        try {
            DB::beginTransaction();

            // Definir o contexto de team baseado no usuário autenticado
            $authenticatedUser = auth()->user();
            setPermissionsTeamId($authenticatedUser->tenant_id);

            $user = $this->userRoleRepository->findUserById($userId);
            
            if (!$user) {
                throw new ModelNotFoundException('Usuário não encontrado.');
            }

            // Verifica se o usuário pertence ao mesmo tenant do usuário autenticado
            if ($user->tenant_id !== $authenticatedUser->tenant_id) {
                throw new \Exception('Você não tem permissão para modificar este usuário.');
            }

            // Remove as roles do usuário
            $this->userRoleRepository->removeRolesFromUser($user, $roles);

            // Log da operação
            Log::info('Roles removidas do usuário', [
                'user_id' => $userId,
                'roles' => $roles,
                'removed_by' => auth()->user()->id
            ]);

            DB::commit();

            return [
                'user' => $user->fresh(['roles']),
                'removed_roles' => $roles,
                'message' => 'Roles removidas com sucesso do usuário.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao remover roles do usuário', [
                'user_id' => $userId,
                'roles' => $roles,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtém todas as roles de um usuário
     *
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public function getUserRoles(int $userId): array
    {
        // Primeiro, definir o contexto de team baseado no usuário autenticado
        $authenticatedUser = auth()->user();
        setPermissionsTeamId($authenticatedUser->tenant_id);
        
        $user = $this->userRoleRepository->findUserById($userId);
        
        if (!$user) {
            throw new ModelNotFoundException('Usuário não encontrado.');
        }

        // Verifica se o usuário pertence ao mesmo tenant do usuário autenticado
        if ($user->tenant_id !== $authenticatedUser->tenant_id) {
            throw new \Exception('Você não tem permissão para visualizar este usuário.');
        }
        
        // Recarregar o usuário com o contexto de team correto
        $user = $user->fresh(['roles', 'permissions']);
        
        return [
            'user_id' => $userId,
            'roles' => $user->getRoleNames()->toArray(),
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray()
        ];
    }

    /**
     * Lista todos os usuários com suas roles
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUsersWithRoles(int $perPage = 15)
    {
        $tenantId = auth()->user()->tenant_id;
        return $this->userRoleRepository->getUsersWithRolesByTenant($tenantId, $perPage);
    }
}
