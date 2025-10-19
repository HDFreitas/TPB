<?php
namespace App\Modules\Csi\Services\Imports\Erp;

use App\Helpers\WsdlHelper;
use App\Services\LogService;
use App\Modules\Csi\Models\TipoInteracao;
use App\Modules\Csi\Models\Interacao;
use Exception;
use Illuminate\Support\Facades\DB;

class ErpIntegrationService
{
    private LogService $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    public function processarTipoInteracao(TipoInteracao $tipoInteracao): array
    {
        $resultado = [
            'sucesso' => 0,
            'erro' => 0,
            'erros_detalhes' => []
        ];

        if (empty($tipoInteracao->porta)) {
            throw new Exception("Porta do webservice não configurada");
        }

        $this->logService->createJobLog([
            'action' => 'INTEGRATION_ERP_START',
            'description' => "Iniciando importação - Tipo: {$tipoInteracao->nome}",
            'log_type' => 'info',
            'content' => [
                'tipo_interacao_id' => $tipoInteracao->id,
                'porta' => $tipoInteracao->porta,
                'tenant_id' => $tipoInteracao->tenant_id
            ]
        ]);

        try {
            $conector = $tipoInteracao->conector;
            
            if (!$conector) {
                throw new Exception("Conector não encontrado");
            }

            // Monta URL seguindo o padrão do ConectorService
            $porta = $conector->porta ?? '';
            $url = rtrim($conector->url, '/') . ($porta ? ":{$porta}" : '');
            $rota = $conector->rota ?? '';
            
            if ($rota) {
                $url .= '/' . ltrim($rota, '/');
            }

            $wsdlUrl = $url;

            $this->logService->createJobLog([
                'action' => 'INTEGRATION_ERP_URL_BUILT',
                'description' => 'URL WSDL construída',
                'log_type' => 'info',
                'content' => [
                    'tipo_interacao_id' => $tipoInteracao->id,
                    'wsdl_url' => $wsdlUrl,
                    'service_name' => $tipoInteracao->porta,
                    'tenant_id' => $tipoInteracao->tenant_id
                ]
            ]);

            $soapClient = new WsdlHelper(
                $conector->usuario,
                $conector->senha,
                30,
                config('app.debug')
            );

            $serviceName = $tipoInteracao->porta;
            $parameters = [];

            // Executa webservice
            $dados = $soapClient->callService(
                $wsdlUrl,
                $serviceName,
                $parameters
            );

            // Log da resposta completa para debug
            $this->logService->createJobLog([
                'action' => 'INTEGRATION_ERP_RESPONSE',
                'description' => 'Resposta do ERP recebida',
                'log_type' => 'info',
                'content' => [
                    'tipo_interacao_id' => $tipoInteracao->id,
                    'dados' => $dados,
                    'tenant_id' => $tipoInteracao->tenant_id
                ]
            ]);

            // Processa a grid SaiTabResCon
            if (isset($dados['saiTabResCon'])) {
                $registros = $dados['saiTabResCon'];
                
                // Se retornar um único registro, ele pode vir como array associativo
                // Se retornar múltiplos, vem como array de arrays
                // Normaliza para sempre processar como array de arrays
                if (isset($registros['saiIntCodCli'])) {
                    // É um único registro
                    $registros = [$registros];
                }

                if (!is_array($registros) || empty($registros)) {
                    $this->logService->createJobLog([
                        'action' => 'INTEGRATION_ERP_NO_DATA',
                        'description' => 'Grid saiTabResCon vazia',
                        'log_type' => 'info',
                        'content' => [
                            'tipo_interacao_id' => $tipoInteracao->id,
                            'tenant_id' => $tipoInteracao->tenant_id
                        ]
                    ]);
                    
                    return $resultado;
                }

                // Processa cada registro da grid
                foreach ($registros as $registro) {
                    try {
                        // Valida se o registro tem os campos obrigatórios
                        if (empty($registro['saiIntCodCli']) || empty($registro['saiStrTitItr'])) {
                            throw new Exception("Registro sem código de cliente ou título");
                        }

                        $this->criarInteracao($tipoInteracao, $registro);
                        $resultado['sucesso']++;
                        
                    } catch (Exception $e) {
                        $resultado['erro']++;
                        $resultado['erros_detalhes'][] = [
                            'registro' => $registro,
                            'erro' => $e->getMessage()
                        ];

                        $this->logService->createJobLog([
                            'action' => 'INTEGRATION_ERP_RECORD_ERROR',
                            'description' => 'Erro ao processar registro, verifique os logs para mais detalhes',
                            'log_type' => 'error',
                            'content' => [
                                'tipo_interacao_id' => $tipoInteracao->id,
                                'registro' => $registro,
                                'erro' => $e->getMessage(),
                                'tenant_id' => $tipoInteracao->tenant_id
                            ]
                        ]);
                    }
                }
            } else {
                $this->logService->createJobLog([
                    'action' => 'INTEGRATION_ERP_NO_GRID',
                    'description' => 'Grid saiTabResCon não encontrada na resposta',
                    'log_type' => 'info',
                    'content' => [
                        'tipo_interacao_id' => $tipoInteracao->id,
                        'resposta_completa' => $dados,
                        'tenant_id' => $tipoInteracao->tenant_id
                    ]
                ]);
            }

            $this->logService->createJobLog([
                'action' => 'INTEGRATION_ERP_COMPLETED',
                'description' => 'Importação concluída',
                'log_type' => 'info',
                'content' => [
                    'tipo_interacao_id' => $tipoInteracao->id,
                    'sucesso' => $resultado['sucesso'],
                    'erro' => $resultado['erro'],
                    'total' => $resultado['sucesso'] + $resultado['erro'],
                    'tenant_id' => $tipoInteracao->tenant_id
                ]
            ]);

        } catch (Exception $e) {
            $this->logService->createJobLog([
                'action' => 'INTEGRATION_ERP_FAILED',
                'description' => 'Falha na importação, verifique os logs para mais detalhes',
                'log_type' => 'error',
                'content' => [
                    'tipo_interacao_id' => $tipoInteracao->id,
                    'porta' => $tipoInteracao->porta ?? 'não definida',
                    'erro' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'tenant_id' => $tipoInteracao->tenant_id
                ]
            ]);

            throw $e;
        }

        return $resultado;
    }

    public function criarInteracao(TipoInteracao $tipoInteracao, array $registro): void
    {
        DB::transaction(function () use ($tipoInteracao, $registro) {
            // Busca o cliente pelo código ERP
            $clienteId = $this->buscarClienteId($registro['saiIntCodCli']);
            if (!$clienteId) {
                throw new Exception("Cliente com código ERP '{$registro['saiIntCodCli']}' não encontrado");
            }

            // Converte data do formato Senior (se necessário)
            $dataInteracao = $this->converterDataSenior($registro['saiDatDatItr']);

            // Critério de unicidade: tenant_id, tipo_interacao_id, cliente_id, data_interacao, chave
            $chave = $this->gerarChave($tipoInteracao, $registro);
            $unique = [
                'tenant_id' => $tipoInteracao->tenant_id,
                'tipo_interacao_id' => $tipoInteracao->id,
                'cliente_id' => $clienteId,
                'data_interacao' => $dataInteracao,
                'chave' => $chave
            ];

            $dados = [
                'titulo' => $registro['saiStrTitItr'],
                'descricao' => $registro['saiStrDesItr'] ?? null,
                'valor' => $registro['saiDecVlrItr'] ?? null,
                'origem' => 'ERP',
                'dados_complementares' => $registro
            ];

            // Log detalhado para diagnóstico de duplicidade
            \Log::info('[INTERACAO][IMPORT] Tentando criar/atualizar interação', [
                'unique' => $unique,
                'dados' => $dados,
                'registro' => $registro,
                'origem' => $dados['origem'],
            ]);

            $interacao = Interacao::updateOrCreate($unique, $dados);

            $this->logService->createJobLog([
                'action' => 'create_or_update',
                'description' => 'Criação/atualização de interação via ERP',
                'log_type' => 'info',
                'content' => [
                    'tipo_interacao_id' => $tipoInteracao->id,
                    'cliente_id' => $clienteId,
                    'codigo_erp_cliente' => $registro['saiIntCodCli'],
                    'origem' => 'ERP',
                    'tenant_id' => $tipoInteracao->tenant_id,
                    'interacao_id' => $interacao->id,
                    'chave' => $chave
                ]
            ]);
        });
    }

    private function buscarClienteId(string $codigoExterno): ?int
    {
        $cliente = DB::table('clientes')
            ->where('codigo_erp', $codigoExterno)
            ->first();

        return $cliente?->id;
    }

    /**
     * Converte data do formato Senior para formato do banco
     */
    private function converterDataSenior($dataSenior): string
    {
        // Senior geralmente retorna no formato: dd/MM/yyyy ou yyyy-MM-dd
        // Ajuste conforme o formato real retornado
        
        if (empty($dataSenior)) {
            return now()->format('Y-m-d 00:00:00');
        }

        try {
            // Tenta diferentes formatos
            if (strpos($dataSenior, '/') !== false) {
                // Formato dd/MM/yyyy
                $date = \DateTime::createFromFormat('d/m/Y', $dataSenior);
            } else {
                // Formato yyyy-MM-dd
                $date = \DateTime::createFromFormat('Y-m-d', $dataSenior);
            }

            if ($date) {
                return $date->format('Y-m-d 00:00:00');
            }
        } catch (\Exception $e) {
            // Se falhar, usa data atual
        }

    return now()->format('Y-m-d 00:00:00');
    }

    /**
     * Gera chave única para a interação
     */
    private function gerarChave(TipoInteracao $tipoInteracao, array $registro): string
    {
    // Gera uma chave única baseada em campos de negócio
    $prefix = strtoupper(substr($tipoInteracao->nome, 0, 3));
    $codigoCliente = $registro['saiIntCodCli'];
    $titulo = isset($registro['saiStrTitItr']) ? mb_strtoupper($registro['saiStrTitItr']) : '';
    $data = isset($registro['saiDatDatItr']) ? $registro['saiDatDatItr'] : '';
    // Pode adicionar outros campos relevantes se necessário
    return md5("{$prefix}|{$codigoCliente}|{$titulo}|{$data}");
    }

    public function processarTodosTiposInteracao(int $tenantId): array
    {
        $this->logService->createJobLog([
            'action' => 'INTEGRATION_ERP_BATCH_START',
            'description' => 'Iniciando processamento em lote',
            'log_type' => 'info',
            'content' => [
                'tenant_id' => $tenantId
            ]
        ]);

        $tiposInteracao = TipoInteracao::query()
            ->where('tenant_id', $tenantId)
            ->ativos()
            ->comConectorErp()
            ->get();

        $resumo = [
            'total_tipos' => $tiposInteracao->count(),
            'tipos_processados' => 0,
            'tipos_com_erro' => 0,
            'total_registros_sucesso' => 0,
            'total_registros_erro' => 0,
            'detalhes' => []
        ];

        foreach ($tiposInteracao as $tipo) {
            try {
                $resultado = $this->processarTipoInteracao($tipo);
                
                $resumo['tipos_processados']++;
                $resumo['total_registros_sucesso'] += $resultado['sucesso'];
                $resumo['total_registros_erro'] += $resultado['erro'];
                
                $resumo['detalhes'][] = [
                    'tipo_id' => $tipo->id,
                    'tipo_nome' => $tipo->nome,
                    'status' => 'sucesso',
                    'registros_importados' => $resultado['sucesso'],
                    'registros_com_erro' => $resultado['erro']
                ];
            } catch (Exception $e) {
                $resumo['tipos_com_erro']++;
                
                $resumo['detalhes'][] = [
                    'tipo_id' => $tipo->id,
                    'tipo_nome' => $tipo->nome,
                    'status' => 'erro',
                    'erro' => $e->getMessage()
                ];
            }
        }

        $this->logService->createJobLog([
            'action' => 'INTEGRATION_ERP_BATCH_COMPLETED',
            'description' => 'Processamento em lote concluído',
            'log_type' => 'info',
            'content' => [
                'tenant_id' => $tenantId,
                'resumo' => $resumo
            ]
        ]);

        return $resumo;
    }
}