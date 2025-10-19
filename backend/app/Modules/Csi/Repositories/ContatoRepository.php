<?php

namespace App\Modules\Csi\Repositories;

use App\Repositories\BaseRepository;
use App\Modules\Csi\Models\Contato;
use App\Modules\Csi\Interfaces\ContatoRepositoryInterface;

class ContatoRepository extends BaseRepository implements ContatoRepositoryInterface
{
    /**
     * Model class for repo.
     * 
     * @var string
     */
    protected $modelClass = Contato::class;

    /**
     * Buscar contatos por cliente
     */
    public function findByCliente($clienteId, $perPage = 15)
    {
        return Contato::where('cliente_id', $clienteId)
            ->with(['cliente', 'tenant'])
            ->orderBy('nome')
            ->paginate($perPage);
    }

    /**
     * Buscar contatos por tenant
     */
    public function findByTenant($tenantId, $perPage = 15)
    {
        return Contato::where('tenant_id', $tenantId)
            ->with(['cliente', 'tenant'])
            ->orderBy('nome')
            ->paginate($perPage);
    }

    /**
     * Buscar contatos com filtros
     */
    public function search(array $where, $take = 15, $paginate = true)
    {
        $query = Contato::query()->with(['cliente', 'tenant']);
        
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                list($field, $condition, $val) = $value;
                $query->where($field, $condition, $val);
            } else {
                if ($field === 'nome' || $field === 'email' || $field === 'cargo') {
                    $query->where($field, 'like', '%' . $value . '%');
                } else {
                    $query->where($field, '=', $value);
                }
            }
        }
        
        $query->orderBy('nome');
        
        return $this->doQuery($query, $take, $paginate);
    }
}
