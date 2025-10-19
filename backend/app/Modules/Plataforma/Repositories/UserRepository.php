<?php

namespace App\Modules\Plataforma\Repositories;

use App\Repositories\BaseRepository;
use App\Modules\Plataforma\Interfaces\UserRepositoryInterface;
use App\Modules\Plataforma\Models\User;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Model class for repo.
     * 
     * @var string
     */
    protected $modelClass = User::class;

    /**
     * Find a user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail($email)
    {
        return User::with('tenant')->where('email', $email)->first();
    }

    /**
     * Find a user by usuario and dominio.
     *
     * @param string $usuario
     * @param string $dominio
     * @return User|null
     */
    public function findByUsuarioAndDominio($usuario, $dominio)
    {
        return User::with('tenant')
            ->where('usuario', $usuario)
            ->where('dominio', $dominio)
            ->first();
    }

    /**
     * Get users by tenant ID with pagination.
     *
     * @param int $tenantId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByTenantId($tenantId, $perPage = 15)
    {
        return User::where('tenant_id', $tenantId)->paginate($perPage);
    }

    /**
     * Search users by tenant ID with filters and pagination.
     *
     * @param int $tenantId
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function searchByTenantId($tenantId, $filters, $perPage = 15)
    {
        $query = User::where('tenant_id', $tenantId);
        
        if (!empty($filters['name'])) {
            $query->where('name', 'ilike', '%' . $filters['name'] . '%');
        }
        
        if (!empty($filters['usuario'])) {
            $query->where('usuario', 'like', '%' . $filters['usuario'] . '%');
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        return $query->paginate($perPage);
    }
}