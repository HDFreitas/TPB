<?php

namespace App\Modules\Csi\Services;

use App\Modules\Csi\Interfaces\TipoInteracaoRepositoryInterface;
use App\Modules\Csi\Models\TipoInteracao;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class TipoInteracaoService
{
    protected TipoInteracaoRepositoryInterface $repository;
    protected \App\Services\LogService $logService;

        public function __construct(TipoInteracaoRepositoryInterface $repository, \App\Services\LogService $logService)
        {
            $this->repository = $repository;
            $this->logService = $logService;
        }

    /**
     * Listar todos os tipos de interação
     */
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->findAll($perPage);
    }

    /**
     * Buscar tipo de interação por ID
     */
    public function show(int $id): ?TipoInteracao
    {
        return $this->repository->findById($id);
    }

    /**
     * Criar novo tipo de interação
     */
    public function store(array $data): TipoInteracao
    {
        // Validar se o nome já existe para o tenant
        if ($this->repository->existsByNomeAndTenant($data['nome'], $data['tenant_id'])) {
            throw ValidationException::withMessages([
                'nome' => ['Já existe um tipo de interação com este nome para o tenant selecionado.']
            ]);
        }

        // Validar campos específicos baseados no conector
        $this->validateConectorFields($data);

        $tipoInteracao = $this->repository->create($data);
        $this->logService->logCrud('create', 'TipoInteracao', $tipoInteracao->id, [
            'data' => $data
        ]);
        return $tipoInteracao;
    }

    /**
     * Atualizar tipo de interação
     */
    public function update(int $id, array $data): ?TipoInteracao
    {
        $tipoInteracao = $this->repository->findById($id);
        
        if (!$tipoInteracao) {
            return null;
        }

        // Validar se o nome já existe para o tenant (excluindo o atual)
        if (isset($data['nome']) && 
            $this->repository->existsByNomeAndTenant($data['nome'], $data['tenant_id'] ?? $tipoInteracao->tenant_id, $id)) {
            throw ValidationException::withMessages([
                'nome' => ['Já existe um tipo de interação com este nome para o tenant selecionado.']
            ]);
        }

        // Validar campos específicos baseados no conector
        $this->validateConectorFields($data);

        $tipoInteracao = $this->repository->update($id, $data);
        if ($tipoInteracao) {
            $this->logService->logCrud('update', 'TipoInteracao', $id, [
                'data' => $data
            ]);
        }
        return $tipoInteracao;
    }

    /**
     * Deletar tipo de interação
     */
    public function destroy(int $id): bool
    {
        $deleted = $this->repository->delete($id);
        if ($deleted) {
            $this->logService->logCrud('delete', 'TipoInteracao', $id);
        }
        return $deleted;
    }

    /**
     * Buscar tipos de interação por tenant
     */
    public function byTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->findByTenant($tenantId, $perPage);
    }

    /**
     * Buscar tipos de interação ativos
     */
    public function ativos(int $tenantId = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->findAtivos($tenantId, $perPage);
    }

    /**
     * Buscar tipos de interação com filtros
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->search($filters, $perPage);
    }

    /**
     * Buscar tipos de interação por conector
     */
    public function byConector(int $conectorId): Collection
    {
        return $this->repository->findByConector($conectorId);
    }

    /**
     * Importar dados do conector (funcionalidade futura)
     */
    public function importFromConector(int $tipoInteracaoId): array
    {
        $tipoInteracao = $this->repository->findById($tipoInteracaoId);
        
        if (!$tipoInteracao || !$tipoInteracao->conector) {
            throw ValidationException::withMessages([
                'conector' => ['Tipo de interação deve ter um conector configurado para importação.']
            ]);
        }

        // TODO: Implementar lógica de importação baseada no tipo de conector
        // Por enquanto, retorna um resultado simulado
        $result = [
            'success' => true,
            'message' => 'Importação simulada realizada com sucesso.',
            'imported_count' => 0
        ];
        $this->logService->logCrud('import', 'TipoInteracao', $tipoInteracaoId, [
            'result' => $result
        ]);
        return $result;
    }

    /**
     * Validar campos específicos baseados no conector
     */
    private function validateConectorFields(array $data): void
    {
        if (!isset($data['conector_id']) || !$data['conector_id']) {
            return; // Conector é opcional
        }

        // Buscar informações do conector para validação
        $conector = app(\App\Modules\Csi\Repositories\ConectorRepository::class)->findById($data['conector_id']);
        
        if (!$conector) {
            throw ValidationException::withMessages([
                'conector_id' => ['Conector selecionado não encontrado.']
            ]);
        }

        // Validações específicas por tipo de conector
        if ($conector->isErp()) {
            // Para ERP, porta é obrigatória
            if (empty($data['porta'])) {
                throw ValidationException::withMessages([
                    'porta' => ['Porta é obrigatória para conectores ERP.']
                ]);
            }
        } elseif ($conector->isMovidesk()) {
            // Para Movidesk, rota é obrigatória
            if (empty($data['rota'])) {
                throw ValidationException::withMessages([
                    'rota' => ['Rota é obrigatória para conectores Movidesk.']
                ]);
            }
        }
    }
}
