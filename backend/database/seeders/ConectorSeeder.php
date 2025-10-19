<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Csi\Models\Conector;

class ConectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Conectores padrão para cada tenant
        $tenants = \App\Modules\Plataforma\Models\Tenant::all();
        
        if ($tenants->isEmpty()) {
            $this->command->warn('Nenhum tenant encontrado. Execute o TenantSeeder primeiro.');
            return;
        }
        
        foreach ($tenants as $tenant) {
            // Conector ERP
            Conector::create([
                'tenant_id' => $tenant->id,
                'codigo' => '1-ERP',
                'nome' => 'ERP Senior',
                'url' => 'http://example.com/g5-senior-services/',
                'status' => true,
                'usuario' => 'senior',
                'senha' => 'senior',
                'observacoes' => 'Conector para integração com ERP Senior'
            ]);

            // Conector Movidesk
            Conector::create([
                'tenant_id' => $tenant->id,
                'codigo' => '2-Movidesk',
                'nome' => 'Movidesk API',
                'url' => 'https://api.movidesk.com',
                'status' => true,
                'token' => 'token_movidesk_exemplo',
                'observacoes' => 'Conector para integração com Movidesk'
            ]);

            // Conector CRM Eleve
            Conector::create([
                'tenant_id' => $tenant->id,
                'codigo' => '3-CRM Eleve',
                'nome' => 'CRM Eleve',
                'url' => 'https://api.eleve.com',
                'status' => true,
                'token' => 'token_crm_eleve_exemplo',
                'observacoes' => 'Conector para integração com CRM Eleve'
            ]);
        }
    }
}

