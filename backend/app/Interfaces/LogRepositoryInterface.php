<?php

namespace App\Interfaces;

interface LogRepositoryInterface
{
    /**
     * Lista todos os logs com paginação e filtro por tenant
     */
    public function getAll($perPage = 15, $tenantId);

    /**
     * Busca um log por ID com filtro por tenant
     */
    public function findById($id, $tenantId);

    /**
     * Cria um novo log
     */
    public function create(array $data);

    /**
     * Remove um log
     */
    public function delete($id);

    /**
     * Busca logs com filtros avançados
     */
    public function search(array $filters, $perPage = 15);

    /**
     * Busca logs de erro
     */
    public function getErrors($tenantId, $perPage = 15);

    /**
     * Busca logs por tipo
     */
    public function getByType($tenantId, $type, $perPage = 15);

    /**
     * Estatísticas gerais
     */
    public function getStats($tenantId);

    /**
     * Logs por período
     */
    public function getByPeriod($tenantId, $days = 7);

    /**
     * Top ações mais frequentes
     */
    public function getTopActions($tenantId, $limit = 10);

    /**
     * Logs por usuário
     */
    public function getByUser($tenantId, $userId, $perPage = 15);

    /**
     * Retorna lista de operações únicas disponíveis nos logs (CREATE, UPDATE, DELETE, etc)
     */
    public function getUniqueActions($tenantId);
}