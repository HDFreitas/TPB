<?php

namespace App\Console\Commands;

use App\Modules\Csi\Jobs\ImportErpInterationsJob;
use App\Modules\Csi\Models\TipoInteracao;
use App\Services\LogService;
use App\Modules\Plataforma\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Console\Command;

class ProcessarInteracoesErpCommand extends Command
{
    /**
     * Assinatura do comando
     */
    protected $signature = 'csi:processar-erp
                            {--tenant= : ID do tenant específico}
                            {--tipo= : ID do tipo de interação específico}
                            {--sync : Executa sincronamente (sem fila)}
                            {--force : Força processamento mesmo que já tenha sido executado recentemente}';

    /**
     * Descrição do comando
     */
    protected $description = 'Processa importação de interações do ERP';

    /**
     * Construtor
     */
    public function __construct(
        private LogService $logService
    ) {
        parent::__construct();
        
    }

    /**
     * Executa o comando
     */
    public function handle(): int
    {
        // Define usuário de sistema para logs
        Auth::setUser(User::where('usuario', 'admin')->first());

        $this->info('🚀 Iniciando processamento de interações ERP...');

        // Busca tipos de interação a processar
        $tiposInteracao = $this->getTiposInteracao();

        if ($tiposInteracao->isEmpty()) {
            $this->warn('⚠️  Nenhum tipo de interação encontrado para processar.');
            return self::SUCCESS;
        }

        $this->info("📊 {$tiposInteracao->count()} tipo(s) de interação encontrado(s).");

        // Barra de progresso
        $bar = $this->output->createProgressBar($tiposInteracao->count());
        $bar->start();

        $processados = 0;
        $erros = 0;

        foreach ($tiposInteracao as $tipo) {
            try {
                if ($this->option('sync')) {
                    // Execução síncrona
                    $this->processarSync($tipo);
                } else {
                    // Despacha para fila
                    ImportErpInterationsJob::dispatch($tipo->id);
                }

                $processados++;
                $bar->advance();

            } catch (\Exception $e) {
                $erros++;
                $this->error("\n❌ Erro ao processar tipo '{$tipo->nome}': {$e->getMessage()}");
                
                $this->logService->logError(
                    'COMMAND_ERP_ERROR',
                    "Erro ao processar tipo de interação via comando",
                    [
                        'tipo_interacao_id' => $tipo->id,
                        'erro' => $e->getMessage(),
                        'tenant_id' => $tipo->tenant_id
                    ]
                );
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Resumo
        $this->info("✅ Processamento concluído!");
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Tipos processados', $processados],
                ['Erros', $erros],
                ['Taxa de sucesso', sprintf('%.2f%%', ($processados / $tiposInteracao->count()) * 100)]
            ]
        );

        // Log do comando
        $this->logService->logUserAction(
            'COMMAND_ERP_EXECUTED',
            'Comando de processamento ERP executado',
            [
                'tipos_processados' => $processados,
                'erros' => $erros,
                'sync' => $this->option('sync') ? 'sim' : 'não',
                'options' => $this->options()
            ]
        );

        return $erros > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Busca tipos de interação a processar
     */
    private function getTiposInteracao()
    {
        $query = TipoInteracao::query()
            ->ativos()
            ->comConectorErp()
            ->with('conector');

        // Filtro por tenant
        if ($tenantId = $this->option('tenant')) {
            $query->where('tenant_id', $tenantId);
        }

        // Filtro por tipo específico
        if ($tipoId = $this->option('tipo')) {
            $query->where('id', $tipoId);
        }

        // Se não forçar, evita reprocessamento recente (últimas 6 horas)
        if (!$this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('ultima_execucao')
                  ->orWhere('ultima_execucao', '<', now()->subHours(6));
            });
        }

        return $query->get();
    }

    /**
     * Processa tipo de interação sincronamente
     */
    private function processarSync(TipoInteracao $tipo): void
    {
        $this->warn("\n⚙️  Processando '{$tipo->nome}' (sync)...");
        
        $service = app(\App\Modules\Csi\Services\Imports\ERP\ErpIntegrationService::class);
        $resultado = $service->processarTipoInteracao($tipo);

        $this->info("   ✓ Sucesso: {$resultado['sucesso']} | Erros: {$resultado['erro']}");
    }
}