import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router';
import { useAuthStore } from '@/modules/plataforma/stores/auth'; 

/**
 * Navigation Guard para controle de autenticação.
 * A invalidação de token durante o uso é melhor tratada por interceptors de API (401).
 */

export const authGuard = (
    to: RouteLocationNormalized,
    _from: RouteLocationNormalized,
    next: NavigationGuardNext
) => {
    const authStore = useAuthStore();
    const isAuthenticated = authStore.isAuthenticated; 
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    const requiresRole = to.matched.find(record => record.meta.requiresRole)?.meta.requiresRole;
    const requiresPermission = to.matched.find(record => record.meta.requiresPermission)?.meta.requiresPermission;
    const isAuthRoute = to.path.startsWith('/auth');


    if (requiresAuth) {
        if (isAuthenticated) {
            // Verificar permissão se necessário (prioridade sobre role)
            if (requiresPermission) {
                const userPermissions = authStore.user?.permissions || [];
                const hasRequiredPermission = userPermissions.includes(requiresPermission as string);
                
                if (!hasRequiredPermission) {
                    next({ 
                        name: 'Dashboard',
                        query: { 
                            error: 'Acesso negado. Você não tem permissão para acessar esta página.' 
                        }
                    });
                    return;
                }
            }
            // Verificar role se necessário (fallback para compatibilidade)
            else if (requiresRole) {
                const userRoles = authStore.user?.roles || [];
                const hasRequiredRole = userRoles.includes(requiresRole as string);
                
                if (!hasRequiredRole) {
                    next({ 
                        name: 'Dashboard',
                        query: { 
                            error: 'Acesso negado. Você não tem permissão para acessar esta página.' 
                        }
                    });
                    return;
                }
            }
            next(); 
        } else {
            next({
                name: 'Login', 
                query: { redirect: to.fullPath }
            });
        }
    }
    
    else if (isAuthRoute) {
        if (isAuthenticated) {
            next({ name: 'Dashboard' }); 
        } else {
            next();
        }
    }
    
    else {
        // Verificar permissão/role mesmo em rotas que não requerem auth explicitamente
        if (isAuthenticated) {
            if (requiresPermission) {
                const userPermissions = authStore.user?.permissions || [];
                const hasRequiredPermission = userPermissions.includes(requiresPermission as string);
                
                if (!hasRequiredPermission) {
                    next({ 
                        name: 'Dashboard',
                        query: { 
                            error: 'Acesso negado. Você não tem permissão para acessar esta página.' 
                        }
                    });
                    return;
                }
            } else if (requiresRole) {
                const userRoles = authStore.user?.roles || [];
                const hasRequiredRole = userRoles.includes(requiresRole as string);
                
                if (!hasRequiredRole) {
                    next({ 
                        name: 'Dashboard',
                        query: { 
                            error: 'Acesso negado. Você não tem permissão para acessar esta página.' 
                        }
                    });
                    return;
                }
            }
        }
        next(); 
    }
};
