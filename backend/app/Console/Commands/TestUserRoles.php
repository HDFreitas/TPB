<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Plataforma\Models\User;

class TestUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:user-roles {userId=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test user roles and permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('userId');
        
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("Usuário com ID {$userId} não encontrado.");
            return 1;
        }

        $this->info("=== TESTE DE ROLES DO USUÁRIO ===");
        $this->info("ID: {$user->id}");
        $this->info("Nome: {$user->name}");
        $this->info("Email: {$user->email}");
        $this->info("Tenant ID: {$user->tenant_id}");
        
        // Definir contexto de team
        setPermissionsTeamId($user->tenant_id);
        
        $this->info("\n--- ROLES ---");
        $roles = $user->getRoleNames();
        if ($roles->count() > 0) {
            foreach ($roles as $role) {
                $this->info("✅ {$role}");
            }
        } else {
            $this->warn("❌ Nenhuma role encontrada");
        }
        
        $this->info("\n--- PERMISSÕES ---");
        $permissions = $user->getAllPermissions();
        if ($permissions->count() > 0) {
            foreach ($permissions as $permission) {
                $this->info("✅ {$permission->name}");
            }
        } else {
            $this->warn("❌ Nenhuma permissão encontrada");
        }
        
        $this->info("\n--- VERIFICAÇÃO DIRETA NO BANCO ---");
        $directRoles = \DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', 'App\\Modules\\Plataforma\\Models\\User')
            ->where('model_has_roles.tenant_id', $user->tenant_id)
            ->select('roles.name', 'roles.tenant_id')
            ->get();
            
        if ($directRoles->count() > 0) {
            foreach ($directRoles as $role) {
                $this->info("🔍 Role no banco: {$role->name} (tenant: {$role->tenant_id})");
            }
        } else {
            $this->warn("🔍 Nenhuma role encontrada no banco");
        }
        
        return 0;
    }
}
