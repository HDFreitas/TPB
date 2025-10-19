<?php

namespace App\Modules\Plataforma\Repositories;

use App\Repositories\BaseRepository;
use App\Modules\Plataforma\Interfaces\PermissionRepositoryInterface;
use Spatie\Permission\Models\Permission;

class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{
    /**
     * Model class for repo.
     *
     * @var string
     */
    protected $modelClass = Permission::class;

    /**
     * Find a permission by name.
     *
     * @param string $name
     * @return Permission|null
     */
    public function findByName($name)
    {
        return $this->findByField('name', $name);
    }
}
