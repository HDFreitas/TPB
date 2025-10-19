<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Csi\Models\Conector;
use App\Modules\Csi\Models\TipoInteracao;

class TipoInteracaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Para cada tenant, criar tipos de interação vinculados ao conector ERP
        $tenants = \App\Modules\Plataforma\Models\Tenant::all();
        
        if ($tenants->isEmpty()) {
            $this->command->warn('Nenhum tenant encontrado. Execute o TenantSeeder primeiro.');
            return;
        }
        
        foreach ($tenants as $tenant) {
            // Buscar conector ERP do tenant
            $conectorErp = Conector::where('tenant_id', $tenant->id)
                ->where('codigo', '1-ERP')
                ->first();
            
            if (!$conectorErp) {
                $this->command->warn("Conector ERP não encontrado para tenant {$tenant->nome}. Execute o ConectorSeeder primeiro.");
                continue;
            }

            // Tipos de interação padrão para ERP
            $tiposInteracao = [
                [
                    'nome' => 'Faturamento',
                    'porta' => 'faturamento',
                    'rota' => '/sapiens_Synccom_senior_g5_co_mcm_int_interacaoclientesp',
                    'observacoes' => 'Interações de faturamento do ERP'
                ],
                [
                    'nome' => 'Orçamentos',
                    'porta' => 'orcamentos',
                    'rota' => '/sapiens_Synccom_senior_g5_co_mcm_int_orcamentoclientesp',
                    'observacoes' => 'Interações de orçamentos do ERP'
                ],
                [
                    'nome' => 'Pedidos',
                    'porta' => 'pedidos',
                    'rota' => '/sapiens_Synccom_senior_g5_co_mcm_int_pedidosclientesp',
                    'observacoes' => 'Interações de pedidos do ERP'
                ]
            ];

            foreach ($tiposInteracao as $tipo) {
                TipoInteracao::create(array_merge($tipo, [
                    'tenant_id' => $tenant->id,
                    'conector_id' => $conectorErp->id,
                    'status' => true
                ]));
            }

            $this->command->info("Tipos de interação criados para tenant {$tenant->nome}");
        }
    }
}