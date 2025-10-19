<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Csi\Models\Interacao;
use App\Modules\Csi\Models\Cliente;
use App\Modules\Csi\Models\TipoInteracao;
use App\Modules\Plataforma\Models\User;
use App\Modules\Plataforma\Models\Tenant;

class InteracaoSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = Tenant::value('id');
        if (!$tenantId) {
            return;
        }

        $userId = User::where('tenant_id', $tenantId)->value('id');
        $cliente = Cliente::where('tenant_id', $tenantId)->first();
        
        if (!$cliente) {
            return;
        }

        // Buscar tipos de interação por nome/slug
        $tipoOuvidoria = TipoInteracao::where('tenant_id', $tenantId)
            ->where('nome', 'Ouvidoria Senior') // ou slug/codigo
            ->value('id');
            
        $tipoAcionamento = TipoInteracao::where('tenant_id', $tenantId)
            ->where('nome', 'Acionamento Gestão')
            ->value('id');
            
        $tipoStatusReport = TipoInteracao::where('tenant_id', $tenantId)
            ->where('nome', 'Status Report')
            ->value('id');

        $samples = [
            [
                'tipo' => $tipoOuvidoria,
                'origem' => 'Ouvidoria Senior',
                'titulo' => 'Ouvidoria - atendimento demorado',
                'descricao' => 'Cliente relatou demora no atendimento pelo suporte.',
                'chave' => 'OUV-1001',
                'valor' => null,
            ],
            [
                'tipo' => $tipoAcionamento,
                'origem' => 'Acionamento Gestão',
                'titulo' => 'Gestão acionada - criticidade alta',
                'descricao' => 'Acompanhamento de incidente crítico com impacto financeiro.',
                'chave' => 'ACG-2001',
                'valor' => 1500.00,
            ],
            [
                'tipo' => $tipoStatusReport,
                'origem' => 'Status Report',
                'titulo' => 'Status semanal do projeto X',
                'descricao' => 'Apresentado status e próximos passos.',
                'chave' => 'SR-3001',
                'valor' => null,
            ],
        ];

        foreach ($samples as $s) {
            // Pular se tipo não existir
            if (!$s['tipo']) {
                continue;
            }
            
            Interacao::firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'cliente_id' => $cliente->id,
                    'origem' => $s['origem'],
                    'chave' => $s['chave'],
                ],
                [
                    'tipo' => $s['tipo'],
                    'data_interacao' => now()->subDays(rand(0, 10)),
                    'titulo' => $s['titulo'],
                    'descricao' => $s['descricao'],
                    'valor' => $s['valor'],
                    'user_id' => $userId,
                ]
            );
        }
    }
}