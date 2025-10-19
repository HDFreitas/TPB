<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // ==================== TENANTS ====================
            [
                'name' => 'tenants.visualizar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'tenants.criar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'tenants.editar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'tenants.excluir',
                'guard_name' => 'api'
            ],

            // ==================== USUÁRIOS ====================
            [
                'name' => 'usuarios.visualizar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'usuarios.criar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'usuarios.editar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'usuarios.excluir',
                'guard_name' => 'api'
            ],

            // ==================== PERFIS/ROLES ====================
            [
                'name' => 'perfis.visualizar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'perfis.criar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'perfis.editar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'perfis.excluir',
                'guard_name' => 'api'
            ],
            [
                'name' => 'perfis.gerenciar_hub',
                'guard_name' => 'api'
            ],

            // ==================== PERMISSÕES ====================
            [
                'name' => 'permissoes.visualizar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'permissoes.atribuir',
                'guard_name' => 'api'
            ],

            // ==================== CLIENTES (mantendo as existentes) ====================
            [
                'name' => 'clientes.visualizar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'clientes.criar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'clientes.editar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'clientes.excluir',
                'guard_name' => 'api'
            ],
            [
                'name' => 'contatos.visualizar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'contatos.criar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'contatos.editar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'contatos.excluir',
                'guard_name' => 'api'
            ],

            // ==================== INTERAÇÕES ====================
            [
                'name' => 'interacoes.visualizar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'interacoes.criar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'interacoes.editar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'interacoes.excluir',
                'guard_name' => 'api'
            ],

            // ==================== CONECTORES ====================
            [
                'name' => 'conectores.visualizar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'conectores.criar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'conectores.editar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'conectores.excluir',
                'guard_name' => 'api'
            ],
            [
                'name' => 'conectores.testar',
                'guard_name' => 'api'
            ],

            // ==================== LOGS ====================
            [
                'name' => 'logs.view',
                'guard_name' => 'api'
            ],
            [
                'name' => 'logs.create',
                'guard_name' => 'api'
            ],
            [
                'name' => 'logs.delete',
                'guard_name' => 'api'
            ],
            [
                'name' => 'logs.dashboard',
                'guard_name' => 'api'
            ],
            [
                'name' => 'logs.view.all',
                'guard_name' => 'api'
            ],

            // ==================== TIPOS DE INTERAÇÃO ====================
            [
                'name' => 'tipos_interacao.visualizar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'tipos_interacao.criar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'tipos_interacao.editar',
                'guard_name' => 'api'
            ],
            [
                'name' => 'tipos_interacao.excluir',
                'guard_name' => 'api'
            ],
            [
                'name' => 'tipos_interacao.importar',
                'guard_name' => 'api'
            ]
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                [
                    'name' => $permissionData['name'],
                    'guard_name' => $permissionData['guard_name']
                ]
            );
        }
    }
}
