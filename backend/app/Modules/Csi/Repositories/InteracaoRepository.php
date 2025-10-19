<?php

namespace App\Modules\Csi\Repositories;

use App\Repositories\BaseRepository;
use App\Modules\Csi\Models\Interacao;
use App\Modules\Csi\Interfaces\InteracaoRepositoryInterface;

class InteracaoRepository extends BaseRepository implements InteracaoRepositoryInterface
{
    /**
     * Model class for repo.
     * 
     * @var string
     */
    protected $modelClass = Interacao::class;

    /**
     * Buscar interações por cliente
     */
    public function findByCliente($clienteId, $perPage = 15)
    {
        return Interacao::with(['cliente', 'user', 'tipoInteracao'])
            ->where('cliente_id', $clienteId)
            ->orderBy('data_interacao', 'desc')
            ->paginate($perPage);
    }

    /**
     * Buscar interações por tenant
     */
    public function findByTenant($tenantId, $perPage = 15)
    {
        return Interacao::with(['cliente', 'user', 'tipoInteracao'])
            ->where('tenant_id', $tenantId)
            ->orderBy('data_interacao', 'desc')
            ->paginate($perPage);
    }

    /**
     * Sobrescreve getAll para incluir relações e ordenação padrão.
     */
    public function getAll($take = 15, $paginate = true)
    {
        $query = Interacao::with(['cliente', 'user', 'tipoInteracao'])->orderBy('data_interacao', 'desc');
        return $paginate ? $query->paginate($take) : $query->take($take)->get();
    }

    /**
     * Buscar interações com filtros
     */
    public function search(array $where, $take = 15, $paginate = true)
    {
        $query = Interacao::with(['cliente', 'user', 'tipoInteracao']);
        
        if (isset($where['tenant_id'])) {
            $query->where('tenant_id', $where['tenant_id']);
        }
        
        if (isset($where['cliente_id'])) {
            $query->where('cliente_id', $where['cliente_id']);
        }
        
        if (isset($where['user_id'])) {
            $query->where('user_id', $where['user_id']);
        }
        
        if (isset($where['tipo'])) {
            $query->where('tipo', $where['tipo']);
        }
        
        if (isset($where['origem'])) {
            $query->where('origem', $where['origem']);
        }
        
        if (isset($where['data_interacao_from'])) {
            $query->where('data_interacao', '>=', $where['data_interacao_from']);
        }
        
        if (isset($where['data_interacao_to'])) {
            $query->where('data_interacao', '<=', $where['data_interacao_to']);
        }
        
        if (isset($where['descricao'])) {
            $query->where('descricao', 'like', '%' . $where['descricao'] . '%');
        }

        if (isset($where['titulo'])) {
            $query->where('titulo', 'like', '%' . $where['titulo'] . '%');
        }

        if (isset($where['chave'])) {
            $query->where('chave', 'like', '%' . $where['chave'] . '%');
        }

        if (isset($where['valor_from'])) {
            $query->where('valor', '>=', $where['valor_from']);
        }

        if (isset($where['valor_to'])) {
            $query->where('valor', '<=', $where['valor_to']);
        }

        if (isset($where['cliente_nome'])) {
            $query->whereHas('cliente', function($q) use ($where) {
                $q->where('razao_social', 'like', '%' . $where['cliente_nome'] . '%');
            });
        }
        
        $query->orderBy('data_interacao', 'desc');
        
        return $paginate ? $query->paginate($take) : $query->take($take)->get();
    }
}
