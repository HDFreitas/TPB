<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Plataforma\Models\User;
use Spatie\Permission\Models\Role;

class AssignUserRole extends Command
{
    protected $signature = 'user:assign-role {email} {role}';
    protected $description = 'Assign role to user';

    public function handle()
    {
        $email = $this->argument('email');
        $roleName = $this->argument('role');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found");
            return;
        }
        
        $role = Role::where('name', $roleName)
            ->where('tenant_id', $user->tenant_id)
            ->first();
            
        if (!$role) {
            $this->error("Role {$roleName} not found for tenant {$user->tenant_id}");
            return;
        }
        
        // Set the team ID before assigning role
        setPermissionsTeamId($user->tenant_id);
        
        $user->assignRole($role);
        
        $this->info("Role {$roleName} assigned to user {$email}");
        $this->info("User roles: " . $user->getRoleNames()->implode(', '));
    }
}
