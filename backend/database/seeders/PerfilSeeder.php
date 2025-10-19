<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\plataforma\Models\Tenant;
use Spatie\Permission\Models\Role;

class PerfilSeeder extends Seeder
{
    public function run()
    {
        // Criar perfil Administrador em todos os tenants
        foreach (Tenant::all() as $tenant) {
            Role::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Administrador',
                    'guard_name' => 'api',
                ],
                [
                    'description' => 'Perfil de administração do tenant',
                ]
            );
        }

        // Criar perfil HUB apenas no tenant master (ID = 1 - Sancon)
        $tenantMaster = Tenant::find(1);
        if ($tenantMaster) {
            Role::firstOrCreate(
                [
                    'tenant_id' => $tenantMaster->id,
                    'name' => 'HUB',
                    'guard_name' => 'api',
                ],
                [
                    'description' => 'Perfil global de administração do sistema - Criação de tenants',
                ]
            );
        }
    }
}