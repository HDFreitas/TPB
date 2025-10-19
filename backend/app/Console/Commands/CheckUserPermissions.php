<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Plataforma\Models\User;
use Spatie\Permission\Models\Permission;

class CheckUserPermissions extends Command
{
    protected $signature = 'user:check-permissions {email}';
    protected $description = 'Check user permissions';

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
        $this->info("Roles: " . $user->getRoleNames()->implode(', '));
        $this->info("Permissions: " . $user->getAllPermissions()->pluck('name')->implode(', '));
        
        // Check specific contact permissions
        $contactPermissions = ['contatos.visualizar', 'contatos.inserir', 'contatos.editar', 'contatos.excluir'];
        
        $this->info("\nContact permissions:");
        foreach ($contactPermissions as $permission) {
            $hasPermission = $user->hasPermissionTo($permission, 'api');
            $this->info("  {$permission}: " . ($hasPermission ? 'YES' : 'NO'));
        }
    }
}
