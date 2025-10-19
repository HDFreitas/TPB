<?php

namespace App\Modules\Csi\Interfaces;

interface ContatoRepositoryInterface
{
    public function getAll($perPage = 15);
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function search(array $filters, $perPage = 15);
    public function findByCliente($clienteId, $perPage = 15);
    public function findByTenant($tenantId, $perPage = 15);
}
