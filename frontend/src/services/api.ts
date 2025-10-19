import axios from 'axios';
import router from '@/router';
import { useAuthStore } from '@/modules/plataforma/stores/auth';

const apiClient = axios.create({
    baseURL: import.meta.env.VITE_API_URL,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    },
    withCredentials: true,
});

// Request interceptor - cookies are sent automatically with withCredentials: true
apiClient.interceptors.request.use(
    (config) => {
        // Cookies are automatically included with withCredentials: true
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

apiClient.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        if (error.response && error.response.status === 401) {

            const authStore = useAuthStore();
            authStore.logout().then(() => {
                router.push('/auth/login');
            });

            return Promise.reject(error);

        }
        return Promise.reject(error);
    }
);

export default apiClient;