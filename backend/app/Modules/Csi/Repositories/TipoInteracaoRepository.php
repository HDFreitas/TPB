<?php

namespace App\Modules\Csi\Repositories;

use App\Modules\Csi\Interfaces\TipoInteracaoRepositoryInterface;
use App\Modules\Csi\Models\TipoInteracao;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TipoInteracaoRepository implements TipoInteracaoRepositoryInterface
{
    protected TipoInteracao $model;

    public function __construct(TipoInteracao $model)
    {
        $this->model = $model;
    }

    /**
     * Buscar todos os tipos de interação paginados
     */
    public function findAll(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['tenant', 'conector'])
            ->orderBy('nome')
            ->paginate($perPage);
    }

    /**
     * Buscar tipo de interação por ID
     */
    public function findById(int $id): ?TipoInteracao
    {
        return $this->model
            ->with(['tenant', 'conector'])
            ->find($id);
    }

    /**
     * Criar novo tipo de interação
     */
    public function create(array $data): TipoInteracao
    {
        // Critério de unicidade: tenant_id + nome
        $unique = [
            'tenant_id' => $data['tenant_id'],
            'nome' => $data['nome']
        ];
        $dados = $data;
        unset($dados['tenant_id'], $dados['nome']);
        return $this->model->updateOrCreate($unique, $dados);
    }

    /**
     * Atualizar tipo de interação
     */
    public function update(int $id, array $data): ?TipoInteracao
    {
        $tipoInteracao = $this->findById($id);
        
        if (!$tipoInteracao) {
            return null;
        }

        $tipoInteracao->update($data);
        return $tipoInteracao->fresh(['tenant', 'conector']);
    }

    /**
     * Deletar tipo de interação
     */
    public function delete(int $id): bool
    {
        $tipoInteracao = $this->findById($id);
        
        if (!$tipoInteracao) {
            return false;
        }

        return $tipoInteracao->delete();
    }

    /**
     * Buscar tipos de interação por tenant
     */
    public function findByTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['tenant', 'conector'])
            ->byTenant($tenantId)
            ->orderBy('nome')
            ->paginate($perPage);
    }

    /**
     * Buscar tipos de interação ativos
     */
    public function findAtivos(int $tenantId = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['tenant', 'conector'])
            ->ativos();

        if ($tenantId) {
            $query->byTenant($tenantId);
        }

        return $query->orderBy('nome')->paginate($perPage);
    }

    /**
     * Buscar tipos de interação por filtros
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['tenant', 'conector']);

        if (!empty($filters['tenant_id'])) {
            $query->byTenant($filters['tenant_id']);
        }

        if (!empty($filters['nome'])) {
            $query->byNome($filters['nome']);
        }

        if (!empty($filters['conector_id'])) {
            $query->byConector($filters['conector_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('nome')->paginate($perPage);
    }

    /**
     * Buscar tipos de interação por conector
     */
    public function findByConector(int $conectorId): Collection
    {
        return $this->model
            ->with(['tenant', 'conector'])
            ->byConector($conectorId)
            ->ativos()
            ->orderBy('nome')
            ->get();
    }

    /**
     * Verificar se nome já existe para o tenant
     */
    public function existsByNomeAndTenant(string $nome, int $tenantId, int $excludeId = null): bool
    {
        $query = $this->model
            ->where('nome', $nome)
            ->where('tenant_id', $tenantId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
