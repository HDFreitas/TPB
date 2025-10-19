<?php

namespace App\Modules\Csi\Interfaces;

use App\Modules\Csi\Models\TipoInteracao;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TipoInteracaoRepositoryInterface
{
    /**
     * Buscar todos os tipos de interação paginados
     */
    public function findAll(int $perPage = 15): LengthAwarePaginator;

    /**
     * Buscar tipo de interação por ID
     */
    public function findById(int $id): ?TipoInteracao;

    /**
     * Criar novo tipo de interação
     */
    public function create(array $data): TipoInteracao;

    /**
     * Atualizar tipo de interação
     */
    public function update(int $id, array $data): ?TipoInteracao;

    /**
     * Deletar tipo de interação
     */
    public function delete(int $id): bool;

    /**
     * Buscar tipos de interação por tenant
     */
    public function findByTenant(int $tenantId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Buscar tipos de interação ativos
     */
    public function findAtivos(int $tenantId = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Buscar tipos de interação por filtros
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Buscar tipos de interação por conector
     */
    public function findByConector(int $conectorId): Collection;

    /**
     * Verificar se nome já existe para o tenant
     */
    public function existsByNomeAndTenant(string $nome, int $tenantId, int $excludeId = null): bool;
}
