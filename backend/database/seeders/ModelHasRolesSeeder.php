<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Modules\Plataforma\Models\User;
use Spatie\Permission\Models\Role;

class ModelHasRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dados para inserir na tabela model_has_roles
        $modelHasRoles = [
            [
                'role_id' => null, // Será preenchido dinamicamente
                'model_type' => 'App\\Modules\\Plataforma\\Models\\User',
                'model_id' => 1, // ID do usuário admin@hub.com
                'role_name' => 'HUB' // Corrigido: HUB ao invés de Administrador
            ],
            // Você pode adicionar mais associações aqui
            // [
            //     'role_id' => null,
            //     'model_type' => 'App\\Modules\\Plataforma\\Models\\User', 
            //     'model_id' => 2,
            //     'role_name' => 'Editor'
            // ],
        ];

        foreach ($modelHasRoles as $modelRole) {
            // Buscar o usuário
            $user = User::find($modelRole['model_id']);
            
            if (!$user) {
                $this->command->warn("Usuário com ID {$modelRole['model_id']} não encontrado. Pulando...");
                continue;
            }

            // Buscar a role pelo nome e tenant do usuário
            $role = Role::where('name', $modelRole['role_name'])
                ->where('tenant_id', $user->tenant_id)
                ->first();

            if (!$role) {
                $this->command->warn("Role '{$modelRole['role_name']}' não encontrada para o tenant {$user->tenant_id}. Pulando...");
                continue;
            }

            // Verificar se a associação já existe
            $exists = DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->where('model_type', $modelRole['model_type'])
                ->where('model_id', $modelRole['model_id'])
                ->where('tenant_id', $user->tenant_id)
                ->exists();

            if ($exists) {
                $this->command->info("Associação já existe: Usuário {$user->name} (ID: {$modelRole['model_id']}) já possui a role '{$modelRole['role_name']}'.");
                continue;
            }

            // Inserir na tabela model_has_roles
            DB::table('model_has_roles')->insert([
                'role_id' => $role->id,
                'model_type' => $modelRole['model_type'],
                'model_id' => $modelRole['model_id'],
                'tenant_id' => $user->tenant_id, // Incluir tenant_id
            ]);

            $this->command->info("✅ Role '{$modelRole['role_name']}' atribuída ao usuário {$user->name} (ID: {$modelRole['model_id']}).");
        }

        $this->command->info("🎉 ModelHasRolesSeeder executado com sucesso!");
    }
}
