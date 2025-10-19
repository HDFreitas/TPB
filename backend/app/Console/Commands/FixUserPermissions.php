<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Plataforma\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FixUserPermissions extends Command
{
    protected $signature = 'fix:user-permissions {email}';
    protected $description = 'Fix user permissions by ensuring they have the correct role and permissions';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("🔍 Buscando usuário: {$email}");
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuário {$email} não encontrado!");
            return 1;
        }
        
        $this->info("✅ Usuário encontrado: {$user->name} (Tenant ID: {$user->tenant_id})");
        
        // Definir contexto do tenant
        setPermissionsTeamId($user->tenant_id);
        
        // Verificar roles atuais
        $currentRoles = $user->getRoleNames();
        $this->info("📋 Roles atuais: " . $currentRoles->implode(', '));
        
        // Buscar role HUB
        $hubRole = Role::where('name', 'HUB')->where('tenant_id', $user->tenant_id)->first();
        
        if (!$hubRole) {
            $this->error("❌ Role HUB não encontrada para o tenant {$user->tenant_id}!");
            return 1;
        }
        
        $this->info("✅ Role HUB encontrada: {$hubRole->name}");
        
        // Verificar permissões da role HUB
        $hubPermissions = $hubRole->permissions->pluck('name');
        $this->info("🔐 Permissões da role HUB: " . $hubPermissions->implode(', '));
        
        // Atribuir role HUB se necessário
        if (!$user->hasRole('HUB')) {
            $this->info("⚠️ Usuário não tem a role HUB. Atribuindo...");
            $user->assignRole($hubRole);
            $this->info("✅ Role HUB atribuída!");
        } else {
            $this->info("✅ Usuário já tem a role HUB");
        }
        
        // Recarregar usuário
        $user = $user->fresh();
        setPermissionsTeamId($user->tenant_id);
        
        // Verificar resultado final
        $finalRoles = $user->getRoleNames();
        $finalPermissions = $user->getAllPermissions()->pluck('name');
        
        $this->info("\n🎯 RESULTADO FINAL:");
        $this->info("📋 Roles: " . $finalRoles->implode(', '));
        $this->info("🔐 Permissões: " . $finalPermissions->implode(', '));
        
        // Testar permissões específicas
        $requiredPermissions = ['tenants.visualizar', 'usuarios.visualizar', 'perfis.visualizar'];
        $this->info("\n🔍 Testando permissões específicas:");
        
        foreach ($requiredPermissions as $permission) {
            $hasPermission = $user->can($permission);
            $status = $hasPermission ? "✅" : "❌";
            $this->info("{$status} {$permission}: " . ($hasPermission ? "SIM" : "NÃO"));
        }
        
        $this->info("\n✅ Correção concluída!");
        return 0;
    }
}
