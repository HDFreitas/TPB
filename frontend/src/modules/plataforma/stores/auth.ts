import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import apiClient from '@/services/api';

interface User {
    id?: number;
    username: string;
    tenant_id?: number;
    roles?: string[];
    permissions?: string[];
}

interface LoginCredentials {
    login: string;
    password: string;
}

interface ValidationErrors {
    [key: string]: string[];
}


export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null)
    const authToken = ref<string | null>(null)
    const loading = ref<boolean>(false)
    const error = ref<string | null>(null)
    const validationErrors = ref<ValidationErrors>({})

    const isAuthenticated = computed(() => !!user.value)
    const getToken = computed(() => authToken.value)
    const getUser = computed(() => user.value)
    const isLoading = computed(() => loading.value)
    const getError = computed(() => error.value)
    const getValidationErrors = computed(() => validationErrors.value)

    /**
     * Verifica se o usuário tem uma role específica
     */
    function hasRole(role: string): boolean {
        if (!user.value || !user.value.roles) return false;
        return user.value.roles.includes(role);
    }

    /**
     * Verifica se o usuário tem uma permissão específica (Spatie)
     */
    function hasPermission(permission: string): boolean {
        if (!user.value || !user.value.permissions) return false;
        return user.value.permissions.includes(permission);
    }

    /**
     * Verifica se o usuário tem permissão para acessar contatos
     * Usuários com permissão contatos.visualizar podem acessar
     */
    function canAccessContatos(): boolean {
        return hasPermission('contatos.visualizar');
    }

    /**
     * Verifica se o usuário pode ver campos de tenant e cliente
     * Apenas usuários HUB podem ver esses campos
     */
    function canSeeTenantFields(): boolean {
        return hasRole('HUB');
    }

    
    /**
     * Define o token de autenticação no estado.
     * @param {string} token - O token de autenticação.
     */
    function setToken(token: string) {
        authToken.value = token;
        // O token será armazenado nos cookies pelo backend
    }

    /**
     * Limpa o token do estado.
     */
    function clearToken() {
        authToken.value = null;
        // O token será removido dos cookies pelo backend
    }

    /**
     * Define os dados do usuário no estado.
     * @param {User} userData - Os dados do usuário.
     */
    function setUser(userData: User) {
        user.value = userData;
    }

    /**
     * Limpa os dados do usuário e erros do estado.
     */
    function clearAuthData() {
        user.value = null;
        error.value = null;
        validationErrors.value = {};
    }

    /**
     * Tenta realizar o login do usuário.
     * @param {LoginCredentials} credentials - As credenciais de login.
     */
    async function login(credentials: LoginCredentials): Promise<boolean> {
        loading.value = true;
        error.value = null;
        validationErrors.value = {};
        clearAuthData(); 

        try {
            
            const response = await apiClient.post<{ status: boolean; message: string; data: { token: string; user: User } }>('/auth/login', credentials);
            
            if (response.status != 200) {
                throw new Error('Falha ao fazer login.');
            }

            setToken(response.data.data.token);
            setUser(response.data.data.user);

            loading.value = false;
            return true; 

        } catch (e: any) {
            clearToken(); 
            
            // Verificar se são erros de validação
            if (e.response?.status === 422 && e.response?.data?.errors) {
                validationErrors.value = e.response.data.errors;
                error.value = 'Os dados fornecidos são inválidos.';
            } else if (e.response?.status === 401) {
                // Erro de credenciais inválidas
                error.value = e.response?.data?.message || 'Credenciais inválidas. Verifique os dados informados.';
            } else if (e.response?.status === 403) {
                // Erro de usuário ou tenant inativo
                error.value = e.response?.data?.message || 'Acesso negado. Usuário ou tenant pode estar inativo.';
            } else if (e.response?.status === 500) {
                // Erro interno do servidor
                error.value = 'Ops! Algo deu errado no servidor. Tente novamente em alguns instantes.';
            } else if (!e.response) {
                // Erro de conexão
                error.value = 'Erro de conexão. Verifique sua internet e tente novamente.';
            } else {
                // Outros erros
                error.value = e.response?.data?.message || 'Falha ao tentar fazer login.';
            }
            
            loading.value = false;
            throw e;
        }
    }

    /**
     * Realiza o logout do usuário.
     */
    async function logout(): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            
            
            if (authToken.value) { 
                await apiClient.post('/auth/logout');
            }
        } catch (err: any) {
            
            error.value = err.response?.data?.message || 'Erro ao comunicar com a API para logout.';
        } finally {
            
            clearToken();
            clearAuthData();
            loading.value = false;
        }
    }

    /**
     * Verifica se o token atual ainda é válido e busca os dados do usuário.
     * Útil para ser chamado na inicialização da aplicação.
     */
    async function checkAuth(): Promise<boolean> {
        loading.value = true;
        error.value = null;

        try {
            const response = await apiClient.get<{ status: boolean; message: string; data: { user: User } }>('auth/me'); 
            
            // Verificar se os dados estão na estrutura correta
            const userData = response.data.data?.user;
            
            if (userData) {
                setUser(userData); 
                loading.value = false;
                return true;
            } else {
                clearAuthData();
                error.value = 'Dados do usuário não encontrados na resposta da API.';
                loading.value = false;
                return false;
            }

        } catch (err: any) {
            
            clearToken();
            clearAuthData();
            error.value = err.response?.data?.message || 'Sessão inválida ou expirada.';
            loading.value = false;
            return false;
        }
    }
    
    // Sempre verificar autenticação na inicialização (usando cookies)
    checkAuth();
    
    return {
        user,
        authToken,
        loading,
        error,
        validationErrors,
        isAuthenticated,
        getToken,
        getUser,
        isLoading,
        getError,
        getValidationErrors,
        login,
        logout,
        checkAuth,
        hasRole,
        hasPermission,
        canAccessContatos,
        canSeeTenantFields,
    }
})