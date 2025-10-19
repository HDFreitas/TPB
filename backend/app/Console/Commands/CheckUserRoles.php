<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Plataforma\Models\User;

class CheckUserRoles extends Command
{
    protected $signature = 'user:check-roles {email}';
    protected $description = 'Check user roles and permissions';

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
        
        // Check if user has any roles in the database
        $userRoles = \DB::table('model_has_roles')
            ->where('model_id', $user->id)
            ->where('model_type', get_class($user))
            ->get();
            
        $this->info("Database roles count: " . $userRoles->count());
        foreach ($userRoles as $role) {
            $this->info("  Role ID: {$role->role_id}, Tenant ID: {$role->tenant_id}");
        }
    }
}
