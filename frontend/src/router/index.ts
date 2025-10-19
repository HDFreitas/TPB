import { createWebHistory, createRouter } from 'vue-router'
import MainRoutes from './modules/main'
import AuthRoutes from './modules/auth'
import PlataformaRoutes from '@/modules/plataforma/router'
import CsiRoutes from '@/modules/csi/router'
import { authGuard } from './guard'

const routes = [
    MainRoutes,
    AuthRoutes,
    ...PlataformaRoutes,
    ...CsiRoutes,
]

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
})

router.beforeEach((to: any, from: any, next: any) => {
    try {
        authGuard(to, from, next);
    } catch (error) {
        console.error('Error during navigation:', error);
        next('/auth/login');
    }
});

router.onError((error: Error) => {
    console.error('Error during navigation:', error);
    router.push('/auth/login');
});

export default router