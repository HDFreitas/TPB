const AuthRoutes = {
    path: '/auth',
    component: () => import('@/layouts/blank/BlankLayout.vue'),
    meta: {
        requiresAuth: false
    },
    children: [
        {
            name: 'Login',
            path: 'login',
            component: () => import('@/modules/plataforma/views/auth/BoxedLogin.vue'),
            meta: {
                requiresAuth: false
            },
        }
    ]
};

export default AuthRoutes;
