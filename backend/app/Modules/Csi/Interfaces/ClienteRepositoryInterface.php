<?php

namespace App\Modules\Csi\Interfaces;

use App\Interfaces\BaseInterface;

interface ClienteRepositoryInterface extends BaseInterface
{
    // Herda todos os métodos da BaseInterface
    // Métodos específicos para Clientes
    public function findByTenant($tenantId, $perPage = 15);
    public function search(array $where, $take = 15, $paginate = true);
    public function findByCnpjCpf($cnpjCpf);
}
