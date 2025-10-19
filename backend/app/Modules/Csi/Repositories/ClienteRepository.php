<?php

namespace App\Modules\Csi\Repositories;

use App\Repositories\BaseRepository;
use App\Modules\Csi\Models\Cliente;
use App\Modules\Csi\Interfaces\ClienteRepositoryInterface;

class ClienteRepository extends BaseRepository implements ClienteRepositoryInterface
{
    /**
     * Model class for repo.
     * 
     * @var string
     */
    protected $modelClass = Cliente::class;

    /**
     * Buscar clientes por tenant
     */
    public function findByTenant($tenantId, $perPage = 15)
    {
        return Cliente::where('tenant_id', $tenantId)
            ->orderBy('razao_social')
            ->paginate($perPage);
    }

    /**
     * Buscar cliente por CNPJ/CPF
     */
    public function findByCnpjCpf($cnpjCpf)
    {
        return Cliente::where('cnpj_cpf', $cnpjCpf)->first();
    }

    /**
     * Buscar clientes com filtros
     */
    public function search(array $where, $take = 15, $paginate = true)
    {
        $query = Cliente::query();
        
        if (isset($where['tenant_id'])) {
            $query->where('tenant_id', $where['tenant_id']);
        }
        
        if (isset($where['razao_social'])) {
            $query->where('razao_social', 'like', '%' . $where['razao_social'] . '%');
        }
        
        if (isset($where['nome_fantasia'])) {
            $query->where('nome_fantasia', 'like', '%' . $where['nome_fantasia'] . '%');
        }
        
        if (isset($where['cnpj_cpf'])) {
            $query->where('cnpj_cpf', 'like', '%' . $where['cnpj_cpf'] . '%');
        }

        if (isset($where['codigo'])) {
            $query->where('codigo', 'like', '%' . $where['codigo'] . '%');
        }

        if (isset($where['codigo_senior'])) {
            $query->where('codigo_senior', 'like', '%' . $where['codigo_senior'] . '%');
        }
        
        if (isset($where['cidade'])) {
            $query->where('cidade', 'like', '%' . $where['cidade'] . '%');
        }
        
        if (isset($where['estado'])) {
            $query->where('estado', $where['estado']);
        }
        
        if (isset($where['status'])) {
            $query->where('status', $where['status']);
        }
        
        $query->orderBy('razao_social');
        
        return $paginate ? $query->paginate($take) : $query->take($take)->get();
    }
}
