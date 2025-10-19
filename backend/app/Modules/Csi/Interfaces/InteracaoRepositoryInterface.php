<?php

namespace App\Modules\Csi\Interfaces;

use App\Interfaces\BaseInterface;

interface InteracaoRepositoryInterface extends BaseInterface
{
    // Herda todos os métodos da BaseInterface
    // Métodos específicos para Interações
    public function findByCliente($clienteId, $perPage = 15);
    public function findByTenant($tenantId, $perPage = 15);
    public function search(array $where, $take = 15, $paginate = true);
}
