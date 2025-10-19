<?php

namespace Database\Seeders;

use App\Modules\Plataforma\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(TenantSeeder::class);
        
        // Busca o primeiro tenant para usar nos seeders
        $tenant = \App\Modules\Plataforma\Models\Tenant::first();
        
        User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'HUB Administrador',
            'email' => 'admin@sancon.com.br',
            'usuario' => 'admin',
            'dominio' => 'sancon'
        ]);
        
        $this->call(PerfilSeeder::class);
        $this->call(ClienteSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(RolePermissionSeeder::class); // Novo seeder
        $this->call(ModelHasRolesSeeder::class);
        $this->call(ConectorSeeder::class);
        $this->call(TipoInteracaoSeeder::class);
        $this->call(InteracaoSeeder::class);
    }
}
