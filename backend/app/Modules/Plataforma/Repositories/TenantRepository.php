<?php

namespace App\Modules\Plataforma\Repositories;

use App\Repositories\BaseRepository;
use App\Modules\Plataforma\Models\Tenant;
use App\Modules\Plataforma\Interfaces\TenantRepositoryInterface;

class TenantRepository extends BaseRepository implements TenantRepositoryInterface
{
    /**
     * Model class for repo.
     * 
     * @var string
     */
    protected $modelClass = Tenant::class;

    // Herda todos os métodos da BaseRepository:
    // - getAll($take = 15, $paginate = true)
    // - findById($id, $fail = true)
    // - create(array $data)
    // - update($id, array $data)
    // - delete($id)
    // - findOrFail($id)
    // - findByField($field, $value, $fail = false)
    // - e muitos outros...
    
    // Adicione aqui apenas métodos específicos do Tenant se necessário
} 