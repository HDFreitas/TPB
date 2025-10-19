<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Plataforma\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TestPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test user permissions and roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing permissions...');
        
        // Buscar um usuário
        $user = User::first();
        
        if (!$user) {
            $this->error('No users found in database');
            return;
        }
        
        $this->info("User: {$user->email}");
        $this->info("Tenant ID: {$user->tenant_id}");
        
        // Definir contexto de team
        setPermissionsTeamId($user->tenant_id);
        
        // Verificar roles
        $roles = $user->getRoleNames();
        $this->info("Roles: " . $roles->implode(', '));
        
        // Verificar permissões
        $permissions = $user->getAllPermissions();
        $this->info("Permissions: " . $permissions->pluck('name')->implode(', '));
        
        // Testar permissões específicas
        $testPermissions = [
            'perfis.visualizar',
            'usuarios.visualizar',
            'tenants.visualizar'
        ];
        
        foreach ($testPermissions as $permission) {
            $hasPermission = $user->can($permission);
            $status = $hasPermission ? '✓' : '✗';
            $this->line("{$status} {$permission}");
        }
        
        // Verificar se há perfis no banco
        $perfisCount = Role::where('tenant_id', $user->tenant_id)->count();
        $this->info("Perfis in tenant {$user->tenant_id}: {$perfisCount}");
        
        // Verificar se há usuários no banco
        $usersCount = User::where('tenant_id', $user->tenant_id)->count();
        $this->info("Users in tenant {$user->tenant_id}: {$usersCount}");
    }
}