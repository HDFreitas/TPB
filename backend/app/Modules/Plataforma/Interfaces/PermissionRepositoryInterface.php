<?php

namespace App\Modules\Plataforma\Interfaces;

use App\Interfaces\BaseInterface;
use Spatie\Permission\Models\Permission;

interface PermissionRepositoryInterface extends BaseInterface
{
    /**
     * Find a permission by name.
     *
     * @param string $name
     * @return Permission|null
     */
    public function findByName($name);
}
