<?php

namespace App\Modules\Plataforma\Interfaces;

use App\Interfaces\BaseInterface;
use App\Modules\Plataforma\Models\User;

interface UserRepositoryInterface extends BaseInterface
{
    /**
     * Find a user by email address.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail($email);

    /**
     * Get users by tenant ID with pagination.
     *
     * @param int $tenantId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByTenantId($tenantId, $perPage = 15);
}