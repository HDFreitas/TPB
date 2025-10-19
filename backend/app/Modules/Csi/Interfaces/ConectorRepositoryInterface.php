<?php

namespace App\Modules\Csi\Interfaces;

use App\Interfaces\BaseInterface;

interface ConectorRepositoryInterface extends BaseInterface
{
    // Herda todos os métodos da BaseInterface
    // Métodos específicos para Conectores
    
    /**
     * Buscar conectores por tenant
     */
    public function findByTenant($tenantId, $perPage = 15);
    
    /**
     * Buscar conector por código
     */
    public function findByCodigo($codigo);
    
    /**
     * Buscar conector por código e tenant
     */
    public function findByCodigoAndTenant($codigo, $tenantId);
    
    /**
     * Buscar conectores com filtros
     */
    public function search(array $where, $take = 15, $paginate = true);
    
    /**
     * Buscar apenas conectores ativos
     */
    public function findAtivos($tenantId = null, $perPage = 15);
}

