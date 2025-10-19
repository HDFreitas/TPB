import type { RouteRecordRaw } from 'vue-router';

// Router do módulo CSI
const CsiRoutes: RouteRecordRaw[] = [
  {
    path: '/csi',
    component: () => import('@/layouts/full/FullLayout.vue'),
    meta: {
      requiresAuth: false
    },
    children: [
      {
        path: 'clientes',
        name: 'CsiClientes',
        component: () => import('@/modules/csi/views/clientes/Clientes.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'clientes.visualizar'
        }
      },
      {
        path: 'clientes/novo',
        name: 'NovoCliente',
        component: () => import('@/modules/csi/views/clientes/shared/ClienteForm.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'clientes.criar'
        }
      },
      {
        path: 'clientes/visualizar/:id',
        name: 'cliente-visualizar',
        component: () => import('@/modules/csi/views/clientes/shared/ClienteForm.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'clientes.visualizar'
        }
      },
      {
        path: 'clientes/editar/:id',
        name: 'EditarCliente',
        component: () => import('@/modules/csi/views/clientes/shared/ClienteForm.vue'),
        meta: {
          requiresAuth: false,
          requiresPermission: 'clientes.editar'
        }
      },
      {
        path: 'interacoes',
        name: 'CsiInteracoes',
        component: () => import('@/modules/csi/views/interacoes/Interacoes.vue'),
        meta: {
          requiresAuth: true,
          requiresPermission: 'interacoes.visualizar'
        }
      },
      {
        path: 'interacoes/nova',
        name: 'NovaInteracao',
        component: () => import('@/modules/csi/views/interacoes/shared/InteracaoForm.vue'),
        meta: {
          requiresAuth: true,
          requiresPermission: 'interacoes.criar'
        }
      },
      {
        path: 'interacoes/editar/:id',
        name: 'EditarInteracao',
        component: () => import('@/modules/csi/views/interacoes/shared/InteracaoForm.vue'),
        meta: {
          requiresAuth: true,
          requiresPermission: 'interacoes.editar'
        }
      },
        {
          path: 'conectores',
          name: 'CsiConectores',
          component: () => import('@/modules/csi/views/conectores/Conectores.vue'),
          meta: {
            requiresAuth: true,
            requiresPermission: 'conectores.visualizar'
          }
        },
        {
          path: 'conectores/editar/:id',
          name: 'EditarConector',
          component: () => import('@/modules/csi/views/conectores/shared/ConectorForm.vue'),
          meta: {
            requiresAuth: true,
            requiresPermission: 'conectores.editar'
          }
        },
        {
          path: 'tipos-interacao',
          name: 'CsiTiposInteracao',
          component: () => import('@/modules/csi/views/tiposInteracao/TiposInteracao.vue'),
          meta: {
            requiresAuth: true,
            requiresPermission: 'tipos_interacao.visualizar'
          }
        },
        {
          path: 'tipos-interacao/novo',
          name: 'NovoTipoInteracao',
          component: () => import('@/modules/csi/views/tiposInteracao/shared/TipoInteracaoForm.vue'),
          meta: {
            requiresAuth: true,
            requiresPermission: 'tipos_interacao.criar'
          }
        },
        {
          path: 'tipos-interacao/visualizar/:id',
          name: 'VisualizarTipoInteracao',
          component: () => import('@/modules/csi/views/tiposInteracao/shared/TipoInteracaoForm.vue'),
          meta: {
            requiresAuth: true,
            requiresPermission: 'tipos_interacao.visualizar'
          }
        },
        {
          path: 'tipos-interacao/editar/:id',
          name: 'EditarTipoInteracao',
          component: () => import('@/modules/csi/views/tiposInteracao/shared/TipoInteracaoForm.vue'),
          meta: {
            requiresAuth: true,
            requiresPermission: 'tipos_interacao.editar'
          }
        }
    ]
  }
];

export default CsiRoutes;
