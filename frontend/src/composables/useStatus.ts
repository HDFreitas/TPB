// Import computed removido - não utilizado

/**
 * Composable para padronizar o uso de status em formulários e listas
 */
export function useStatus() {
  /**
   * Converte status string para boolean
   */
  const statusToBoolean = (status: string | boolean): boolean => {
    if (typeof status === 'boolean') return status;
    return status === 'ativo' || status === 'active' || status === '1' || status === 'true';
  };

  /**
   * Converte boolean para status string
   */
  const booleanToStatus = (value: boolean): string => {
    return value ? 'ativo' : 'inativo';
  };

  /**
   * Converte boolean para status string em inglês
   */
  const booleanToActiveStatus = (value: boolean): string => {
    return value ? 'active' : 'inactive';
  };

  /**
   * Retorna a classe CSS apropriada para o status
   */
  const getStatusClass = (status: boolean | string): string => {
    const isActive = typeof status === 'boolean' ? status : statusToBoolean(status);
    return isActive ? 'status-chip--active' : 'status-chip--inactive';
  };

  /**
   * Retorna o texto do status formatado
   */
  const getStatusText = (status: boolean | string, activeText = 'Ativo', inactiveText = 'Inativo'): string => {
    const isActive = typeof status === 'boolean' ? status : statusToBoolean(status);
    return isActive ? activeText : inactiveText;
  };

  /**
   * Retorna a cor do status para componentes Vuetify
   */
  const getStatusColor = (status: boolean | string): string => {
    const isActive = typeof status === 'boolean' ? status : statusToBoolean(status);
    return isActive ? 'success' : 'error';
  };

  /**
   * Retorna o ícone apropriado para o status
   */
  const getStatusIcon = (status: boolean | string): string => {
    const isActive = typeof status === 'boolean' ? status : statusToBoolean(status);
    return isActive ? 'mdi-check-circle' : 'mdi-close-circle';
  };

  return {
    statusToBoolean,
    booleanToStatus,
    booleanToActiveStatus,
    getStatusClass,
    getStatusText,
    getStatusColor,
    getStatusIcon
  };
}
