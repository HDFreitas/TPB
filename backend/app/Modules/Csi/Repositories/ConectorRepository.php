<?php

namespace App\Modules\Csi\Repositories;

use App\Repositories\BaseRepository;
use App\Modules\Csi\Models\Conector;
use App\Modules\Csi\Interfaces\ConectorRepositoryInterface;

class ConectorRepository extends BaseRepository implements ConectorRepositoryInterface
{
    /**
     * Model class for repo.
     * 
     * @var string
     */
    protected $modelClass = Conector::class;

    /**
     * Buscar conectores por tenant
     */
    public function findByTenant($tenantId, $perPage = 15)
    {
        return Conector::where('tenant_id', $tenantId)
            ->orderBy('nome')
            ->paginate($perPage);
    }

    /**
     * Buscar conector por código
     */
    public function findByCodigo($codigo)
    {
        return Conector::where('codigo', $codigo)->first();
    }

    /**
     * Buscar conector por código e tenant
     */
    public function findByCodigoAndTenant($codigo, $tenantId)
    {
        return Conector::where('codigo', $codigo)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Buscar apenas conectores ativos
     */
    public function findAtivos($tenantId = null, $perPage = 15)
    {
        $query = Conector::where('status', true);
        
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        
        return $query->orderBy('nome')->paginate($perPage);
    }

    /**
     * Buscar conectores com filtros
     */
    public function search(array $where, $take = 15, $paginate = true)
    {
        $query = Conector::query();
        
        if (isset($where['tenant_id'])) {
            $query->where('tenant_id', $where['tenant_id']);
        }
        
        if (isset($where['codigo'])) {
            $query->where('codigo', 'like', '%' . $where['codigo'] . '%');
        }
        
        if (isset($where['nome'])) {
            $query->where('nome', 'like', '%' . $where['nome'] . '%');
        }
        
        if (isset($where['url'])) {
            $query->where('url', 'like', '%' . $where['url'] . '%');
        }
        
        if (isset($where['status'])) {
            $query->where('status', $where['status']);
        }
        
        $query->orderBy('nome');
        
        return $paginate ? $query->paginate($take) : $query->take($take)->get();
    }
}

