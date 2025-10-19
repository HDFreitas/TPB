<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Csi\Models\Cliente;
use App\Modules\plataforma\Models\Tenant;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Busca o primeiro tenant disponível
        $tenant = Tenant::first();
        if (!$tenant) {
            $this->command->error('Nenhum tenant encontrado. Execute o TenantSeeder primeiro.');
            return;
        }

        $clientes = [
            [
                'tenant_id' => $tenant->id,
                'razao_social' => 'Empresa ABC Ltda',
                'nome_fantasia' => 'ABC Ltda',
                'cnpj_cpf' => '12.345.678/0001-90',
                'codigo_erp' => '123124',
                'codigo_senior' => 'EMP001',
                'codigo_ramo' => '123',
                'email' => 'contato@empresaabc.com',
                'telefone' => '(11) 1234-5678',
                'celular' => '(11) 98765-4321',
                'endereco' => 'Rua das Empresas, 123',
                'numero' => '123',
                'complemento' => 'Sala 101',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01000-000',
                'status' => true,
                'cliente_referencia' => true,
                'tipo_perfil' => 'Relacional',
                'classificacao' => 'Promotor',
                'observacoes' => 'Cliente preferencial, atendimento prioritário'
            ],
            [
                'tenant_id' => $tenant->id,
                'razao_social' => 'João da Silva',
                'nome_fantasia' => 'João Silva',
                'cnpj_cpf' => '123.456.789-01',
                'codigo_erp' => '122314',
                'codigo_senior' => 'CLI001',
                'codigo_ramo' => '456',
                'email' => 'joao@email.com',
                'telefone' => '(11) 2345-6789',
                'celular' => '(11) 99876-5432',
                'endereco' => 'Avenida dos Clientes, 456',
                'numero' => '456',
                'bairro' => 'Vila Nova',
                'cidade' => 'Rio de Janeiro',
                'estado' => 'RJ',
                'cep' => '20000-000',
                'status' => true,
                'cliente_referencia' => false,
                'tipo_perfil' => 'Transacional',
                'classificacao' => 'Neutro',
                'observacoes' => 'Cliente pessoa física, contrato de serviços'
            ],
            [
                'tenant_id' => $tenant->id,
                'razao_social' => 'Tecnologia XYZ S.A.',
                'nome_fantasia' => 'Tech XYZ',
                'cnpj_cpf' => '98.765.432/0001-10',
                'codigo_erp' => '436346',
                'codigo_senior' => 'TECH001',
                'codigo_ramo' => '789',
                'email' => 'contato@techxyz.com',
                'telefone' => '(11) 3456-7890',
                'celular' => '(11) 91234-5678',
                'endereco' => 'Alameda da Tecnologia, 789',
                'numero' => '789',
                'complemento' => 'Andar 5',
                'bairro' => 'Alphaville',
                'cidade' => 'Barueri',
                'estado' => 'SP',
                'cep' => '06454-000',
                'status' => false,
                'cliente_referencia' => false,
                'tipo_perfil' => 'Relacional',
                'classificacao' => 'Detrator',
                'observacoes' => 'Cliente suspenso temporariamente por inadimplência'
            ]
        ];

        foreach ($clientes as $clienteData) {
            Cliente::create($clienteData);
        }
    }
}
