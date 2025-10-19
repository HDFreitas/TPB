import type { RouteRecordRaw } from 'vue-router';

// Router do módulo Plataforma
const PlataformaRoutes: RouteRecordRaw[] = [
  {
    path: '/plataforma',
    component: () => import('@/layouts/full/FullLayout.vue'),
    meta: {
      requiresAuth: false
    },
    children: [
      {
        path: 'usuarios',
        name: 'Usuarios',
        component: () => import('@/modules/plataforma/views/usuarios/Usuarios.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'usuarios.visualizar'
        }
      },
      {
        path: 'usuarios/novo',
        name: 'NovoUsuario',
        component: () => import('@/modules/plataforma/views/usuarios/shared/UserForm.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'usuarios.criar'
        }
      },
      {
        path: 'usuarios/visualizar/:id',
        name: 'usuario-visualizar',
        component: () => import('@/modules/plataforma/views/usuarios/shared/UserForm.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'usuarios.visualizar'
        }
      },
      {
        path: 'usuarios/editar/:id',
        name: 'EditarUsuario',
        component: () => import('@/modules/plataforma/views/usuarios/shared/UserForm.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'usuarios.editar'
        }
      },
      {
        path: 'perfis',
        name: 'Perfis',
        component: () => import('@/modules/plataforma/views/perfis/Perfis.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'perfis.visualizar'
        }
      },
      {
        path: 'perfis/novo',
        name: 'NovoPerfil',
        component: () => import('@/modules/plataforma/views/perfis/shared/PerfilForm.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'perfis.criar'
        }
      },
      {
        path: 'perfis/visualizar/:id',
        name: 'perfil-visualizar',
        component: () => import('@/modules/plataforma/views/perfis/shared/PerfilForm.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'perfis.visualizar'
        }
      },
      {
        path: 'perfis/editar/:id',
        name: 'EditarPerfil',
        component: () => import('@/modules/plataforma/views/perfis/shared/PerfilForm.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'perfis.editar'
        }
      },
      {
        path: 'tenants',
        name: 'Tenants',
        component: () => import('@/modules/plataforma/views/tenants/TenantsIndex.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'tenants.visualizar'
        }
      },
      {
        path: 'tenants/novo',
        name: 'NovoTenant',
        component: () => import('@/modules/plataforma/views/tenants/shared/TenantForm.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'tenants.criar'
        }
      },
      {
        path: 'tenants/visualizar/:id',
        name: 'tenant-visualizar',
        component: () => import('@/modules/plataforma/views/tenants/shared/TenantForm.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'tenants.visualizar'
        }
      },
      {
        path: 'tenants/editar/:id',
        name: 'EditarTenant',
        component: () => import('@/modules/plataforma/views/tenants/shared/TenantForm.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'tenants.editar'
        }
      }
    ]
  }
];

export default PlataformaRoutes;
