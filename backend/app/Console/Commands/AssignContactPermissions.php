<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Modules\Plataforma\Models\Tenant;

class AssignContactPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:assign-contacts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atribui permissões de contato às roles HUB e Administrador';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Atribuindo permissões de contato...');

        // Definir permissões para cada role
        $rolePermissions = [
            'HUB' => [
                'cliente.visualizar',
                'cliente.inserir', 
                'cliente.editar',
                'cliente.excluir',
                'usuario.visualizar',
                'usuario.inserir',
                'usuario.editar', 
                'usuario.excluir',
                'contatos.visualizar',
                'contatos.inserir',
                'contatos.editar',
                'contatos.excluir'
            ],
            'Administrador' => [
                'cliente.visualizar',
                'cliente.inserir',
                'cliente.editar', 
                'cliente.excluir',
                'contatos.visualizar',
                'contatos.inserir',
                'contatos.editar',
                'contatos.excluir'
            ]
        ];

        // Para cada tenant, atribuir permissões às roles
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $this->info("📋 Processando tenant: {$tenant->nome} (ID: {$tenant->id})");
            
            // Definir contexto de team
            setPermissionsTeamId($tenant->id);
            
            foreach ($rolePermissions as $roleName => $permissions) {
                // Buscar a role
                $role = Role::where('name', $roleName)
                    ->where('tenant_id', $tenant->id)
                    ->first();
                
                if (!$role) {
                    $this->warn("❌ Role '{$roleName}' não encontrada para o tenant {$tenant->id}");
                    continue;
                }
                
                $this->info("👤 Processando role: {$roleName}");
                
                // Buscar e atribuir permissões
                foreach ($permissions as $permissionName) {
                    // Buscar permissão
                    $permission = Permission::where('name', $permissionName)
                        ->where('guard_name', 'api')
                        ->first();
                    
                    if (!$permission) {
                        $this->warn("⚠️  Permissão '{$permissionName}' não encontrada");
                        continue;
                    }
                    
                    // Verificar se a permissão já está atribuída
                    if (!$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                        $this->line("✅ Permissão '{$permissionName}' atribuída à role '{$roleName}'");
                    } else {
                        $this->line("ℹ️  Permissão '{$permissionName}' já atribuída à role '{$roleName}'");
                    }
                }
            }
        }
        
        $this->info('🎉 Permissões de contato atribuídas com sucesso!');
        return 0;
    }
}