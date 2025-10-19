<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Modules\Plataforma\Models\Tenant;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Definir as permissões para cada perfil
        $rolePermissions = [
            'HUB' => [
                // HUB tem acesso total a tudo
                'tenants.visualizar',
                'tenants.criar',
                'tenants.editar',
                'tenants.excluir',
                'usuarios.visualizar',
                'usuarios.criar',
                'usuarios.editar',
                'usuarios.excluir',
                'perfis.visualizar',
                'perfis.criar',
                'perfis.editar',
                'perfis.excluir',
                'perfis.gerenciar_hub',
                'permissoes.visualizar',
                'permissoes.atribuir',
                'clientes.visualizar',
                'clientes.criar',
                'clientes.editar',
                'clientes.excluir',
                'contatos.visualizar',
                'contatos.criar',
                'contatos.editar',
                'contatos.excluir',
                'interacoes.visualizar',
                'interacoes.criar',
                'interacoes.editar',
                'interacoes.excluir',
                'conectores.visualizar',
                'conectores.criar',
                'conectores.editar',
                'conectores.excluir',
                'conectores.testar',
                'logs.view',
                'logs.create',
                'logs.delete',
                'logs.dashboard',
                'logs.view.all',
                'conectores.testar',
                'tipos_interacao.visualizar',
                'tipos_interacao.criar',
                'tipos_interacao.editar',
                'tipos_interacao.excluir',
                'tipos_interacao.importar'
            ],
            'Administrador' => [
                // Administrador tem acesso completo exceto tenants e gerenciamento HUB
                'usuarios.visualizar',
                'usuarios.criar',
                'usuarios.editar',
                'usuarios.excluir',
                'perfis.visualizar',
                'perfis.criar',
                'perfis.editar',
                'perfis.excluir',
                'permissoes.visualizar',
                'permissoes.atribuir',
                'clientes.visualizar',
                'clientes.criar',
                'clientes.editar',
                'clientes.excluir',
                'contatos.visualizar',
                'contatos.criar',
                'contatos.editar',
                'contatos.excluir',
                'conectores.visualizar',
                'conectores.criar',
                'conectores.editar',
                'conectores.excluir',
                'conectores.testar',
                'logs.view',
                'logs.create',
                'logs.delete',
                'logs.dashboard',
                'tipos_interacao.visualizar',
                'tipos_interacao.criar',
                'tipos_interacao.editar',
                'tipos_interacao.excluir',
                'tipos_interacao.importar'
            ]
        ];

        // Buscar todos os tenants
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            foreach ($rolePermissions as $roleName => $permissions) {
                // Buscar a role específica do tenant
                $role = Role::where('name', $roleName)
                    ->where('tenant_id', $tenant->id)
                    ->first();

                if ($role) {
                    foreach ($permissions as $permissionName) {
                        // Buscar a permissão
                        $permission = Permission::where('name', $permissionName)
                            ->where('guard_name', 'api')
                            ->first();

                        if ($permission) {
                            // Atribuir a permissão à role se ainda não tiver
                            if (!$role->hasPermissionTo($permission)) {
                                $role->givePermissionTo($permission);
                            }
                        }
                    }
                }
            }
        }

        $this->command->info('Permissões atribuídas aos perfis com sucesso!');
    }
}
