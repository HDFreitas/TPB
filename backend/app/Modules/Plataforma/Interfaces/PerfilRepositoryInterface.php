<?php

namespace App\Modules\Plataforma\Interfaces;

use App\Interfaces\BaseInterface;

interface PerfilRepositoryInterface extends BaseInterface
{
    // Herda todos os métodos da BaseInterface
    // Adicione aqui apenas métodos específicos do Perfil se necessário
    
    /**
     * Obter dados completos de usuários para um perfil (associados + disponíveis)
     *
     * @param int $perfilId
     * @param int $tenantId
     * @return array
     */
    public function getPerfilUsersData($perfilId, $tenantId);
    
    /**
     * Associar usuários a um perfil
     *
     * @param int $perfilId
     * @param array $userIds
     * @param int $tenantId
     * @return bool
     */
    public function associateUsers($perfilId, $userIds, $tenantId);
    
    /**
     * Remover usuário de um perfil
     *
     * @param int $perfilId
     * @param int $userId
     * @param int $tenantId
     * @return bool
     */
    public function removeUserFromPerfil($perfilId, $userId, $tenantId);
    
    /**
     * Obter permissões de um perfil
     *
     * @param int $perfilId
     * @param int $tenantId
     * @return array
     */
    public function getPerfilPermissions($perfilId, $tenantId);
    
    /**
     * Sincronizar permissões de um perfil
     *
     * @param int $perfilId
     * @param array $permissionIds
     * @param int $tenantId
     * @return bool
     */
    public function syncPerfilPermissions($perfilId, $permissionIds, $tenantId);
}