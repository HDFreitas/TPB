<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Plataforma\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TestSpatiePermissions extends Command
{
    protected $signature = 'test:spatie-permissions {email}';
    protected $description = 'Test Spatie permissions with multi-tenancy';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found");
            return;
        }
        
        $this->info("User: {$user->email}");
        $this->info("Tenant ID: {$user->tenant_id}");
        
        // Set the team ID
        setPermissionsTeamId($user->tenant_id);
        
        $this->info("Set permissions team ID to: {$user->tenant_id}");
        
        // Check roles
        $this->info("Roles: " . $user->getRoleNames()->implode(', '));
        $this->info("Permissions: " . $user->getAllPermissions()->pluck('name')->implode(', '));
        
        // Check specific contact permissions
        $contactPermissions = ['contatos.visualizar', 'contatos.inserir', 'contatos.editar', 'contatos.excluir'];
        
        $this->info("\nContact permissions:");
        foreach ($contactPermissions as $permission) {
            $hasPermission = $user->hasPermissionTo($permission, 'api');
            $this->info("  {$permission}: " . ($hasPermission ? 'YES' : 'NO'));
        }
        
        // Check if the role has permissions
        $role = Role::where('name', 'HUB')
            ->where('tenant_id', $user->tenant_id)
            ->first();
            
        if ($role) {
            $this->info("\nRole HUB permissions: " . $role->getAllPermissions()->pluck('name')->implode(', '));
        } else {
            $this->error("Role HUB not found for tenant {$user->tenant_id}");
        }
    }
}
