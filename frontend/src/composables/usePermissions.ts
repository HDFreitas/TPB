import { computed } from 'vue';
import { useAuthStore } from '@/modules/plataforma/stores/auth';

/**
 * Composable para verificação de permissões do usuário
 */
export function usePermissions() {
  const authStore = useAuthStore();

  /**
   * Verifica se o usuário tem uma permissão específica
   * @param permission - Nome da permissão a ser verificada
   * @returns boolean - true se o usuário tem a permissão
   */
  const hasPermission = (permission: string): boolean => {
    const userPermissions = authStore.user?.permissions || [];
    return userPermissions.includes(permission);
  };

  /**
   * Verifica se o usuário tem uma role específica
   * @param role - Nome da role a ser verificada
   * @returns boolean - true se o usuário tem a role
   */
  const hasRole = (role: string): boolean => {
    const userRoles = authStore.user?.roles || [];
    return userRoles.includes(role);
  };

  /**
   * Verifica se o usuário tem pelo menos uma das permissões fornecidas
   * @param permissions - Array de permissões
   * @returns boolean - true se o usuário tem pelo menos uma permissão
   */
  const hasAnyPermission = (permissions: string[]): boolean => {
    return permissions.some(permission => hasPermission(permission));
  };

  /**
   * Verifica se o usuário tem todas as permissões fornecidas
   * @param permissions - Array de permissões
   * @returns boolean - true se o usuário tem todas as permissões
   */
  const hasAllPermissions = (permissions: string[]): boolean => {
    return permissions.every(permission => hasPermission(permission));
  };

  /**
   * Computed para verificar permissões específicas de perfis
   */
  const perfilPermissions = computed(() => ({
    canView: hasPermission('perfis.visualizar'),
    canCreate: hasPermission('perfis.criar'),
    canEdit: hasPermission('perfis.editar'),
    canDelete: hasPermission('perfis.excluir'),
    canManageHub: hasPermission('perfis.gerenciar_hub'),
  }));

  /**
   * Computed para verificar permissões específicas de usuários
   */
  const userPermissions = computed(() => {
    const permissions = {
      canView: hasPermission('usuarios.visualizar'),
      canCreate: hasPermission('usuarios.criar'),
      canEdit: hasPermission('usuarios.editar'),
      canDelete: hasPermission('usuarios.excluir'),
    };
    
    
    return permissions;
  });

  /**
   * Computed para verificar permissões específicas de clientes
   */
  const clientePermissions = computed(() => ({
    canView: hasPermission('clientes.visualizar'),
    canCreate: hasPermission('clientes.criar'),
    canEdit: hasPermission('clientes.editar'),
    canDelete: hasPermission('clientes.excluir'),
  }));

  /**
   * Computed para verificar permissões específicas de tenants
   */
  const tenantPermissions = computed(() => ({
    canView: hasPermission('tenants.visualizar'),
    canCreate: hasPermission('tenants.criar'),
    canEdit: hasPermission('tenants.editar'),
    canDelete: hasPermission('tenants.excluir'),
  }));

  /**
   * Computed para verificar permissões específicas de tipos de interação
   */
  const tipoInteracaoPermissions = computed(() => ({
    canView: hasPermission('tipos_interacao.visualizar'),
    canCreate: hasPermission('tipos_interacao.criar'),
    canEdit: hasPermission('tipos_interacao.editar'),
    canDelete: hasPermission('tipos_interacao.excluir'),
    canImport: hasPermission('tipos_interacao.importar'),
  }));

  return {
    hasPermission,
    hasRole,
    hasAnyPermission,
    hasAllPermissions,
    perfilPermissions,
    userPermissions,
    clientePermissions,
    tenantPermissions,
    tipoInteracaoPermissions,
  };
}
