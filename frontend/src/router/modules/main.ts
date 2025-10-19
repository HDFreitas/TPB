const MainRoutes = {
    path: '/',
    component: () => import('@/layouts/full/FullLayout.vue'),
    meta: {
        requiresAuth: false
    },
    children: [
        {
            name: 'Logs',
            path: '/logs',
            component: () => import('@/views/utils/Logs.vue'),
            meta: {
                requiresAuth: false
            },
        },
        {
            name: 'LogDashboard',
            path: '/logs/dashboard',
            component: () => import('@/views/utils/LogDashboard.vue'),
            meta: {
                requiresAuth: false
            },
        },
        {
            name: 'LogDetail',
            path: '/logs/:id',
            component: () => import('@/views/utils/LogDetail.vue'),
            meta: {
                requiresAuth: false
            },
        },
    ]
};

export default MainRoutes;
