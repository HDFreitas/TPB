<?php

namespace App\Modules\Csi\Jobs;

use App\Modules\Csi\Models\TipoInteracao;
use App\Modules\Csi\Services\Imports\Erp\ErpIntegrationService;
use App\Services\LogService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportErpInterationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Timeout do job (em segundos)
     */
    public int $timeout = 600; // 10 minutos

    /**
     * Número máximo de tentativas
     */
    public int $tries = 3;

    /**
     * Tempo de espera entre tentativas (em segundos)
     */
    public int $backoff = 300; // 5 minutos

    /**
     * Tipo de interação a ser processado
     */
    private int $tipoInteracaoId;

    /**
     * Construtor do job
     */
    public function __construct(int $tipoInteracaoId)
    {
        $this->tipoInteracaoId = $tipoInteracaoId;
        // Definir fila baseada na prioridade do tipo de interação
        $tipo = \App\Modules\Csi\Models\TipoInteracao::find($tipoInteracaoId);
        if ($tipo && $tipo->prioridade === 'alta') {
            $this->onQueue('erp-high');
        } else {
            $this->onQueue('erp-default');
        }
    }

    /**
     * Executa o job
     */
    public function handle(
        ErpIntegrationService $erpService,
        LogService $logService
    ): void {
        $tipoInteracao = TipoInteracao::find($this->tipoInteracaoId);
        $tipoInteracaoId = $tipoInteracao ? $tipoInteracao->id : $this->tipoInteracaoId;
        $tenantId = $tipoInteracao ? $tipoInteracao->tenant_id : null;

        $logService->setJob($this);
        $logService->logUserAction(
            'JOB_ERP_STARTED',
            "Job iniciado para tipo de interação: {$tipoInteracao->nome}",
            [
                'job_id' => $this->job?->getJobId() ?? null,
                'tipo_interacao_id' => $tipoInteracaoId,
                'tenant_id' => $tenantId
            ]
        );
        try {
            $resultado = $erpService->processarTipoInteracao($tipoInteracao);
            $logService->logUserAction(
                'JOB_ERP_COMPLETED',
                "Job concluído com sucesso",
                [
                    'job_id' => $this->job?->getJobId() ?? null,
                    'tipo_interacao_id' => $tipoInteracaoId,
                    'resultado' => $resultado,
                    'tenant_id' => $tenantId
                ]
            );
        } catch (Exception $e) {
            $logService->logError(
                'JOB_ERP_FAILED',
                "Falha na execução do job (tentativa {$this->attempts()}/{$this->tries})",
                [
                    'job_id' => $this->job?->getJobId() ?? null,
                    'tipo_interacao_id' => $tipoInteracaoId,
                    'erro' => $e->getMessage(),
                    'attempt' => $this->attempts(),
                    'tenant_id' => $tenantId
                ]
            );
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff * $this->attempts());
                return;
            }
            $this->fail($e);
        }
    }

    /**
     * Trata falha definitiva do job
     */
    public function failed(Exception $exception): void
    {
        $tipoInteracao = TipoInteracao::find($this->tipoInteracaoId);
        $tenantId = $tipoInteracao?->tenant_id;
        \Log::error('[JOB] [TRACE] failed() chamado', [
            'tipo_interacao_id' => $tipoInteracao?->id,
            'tenant_id' => $tenantId,
            'exception' => $exception->getMessage()
        ]);
        app(LogService::class)->logError(
            'JOB_ERP_FAILED_PERMANENTLY',
            "Job falhou definitivamente após {$this->tries} tentativas",
            [
                'job_id' => $this->job?->getJobId() ?? null,
                'tipo_interacao_id' => $tipoInteracao?->id,
                'tipo_interacao_nome' => $tipoInteracao?->nome,
                'erro' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'tenant_id' => $tenantId
            ]
        );
    }

    /**
     * Determina a fila com base na prioridade
     */
    // Se quiser determinar fila, pode fazer aqui, mas agora não é mais chamado no construtor

    /**
     * Tags para monitoramento (Horizon/Telescope)
     */
    public function tags(): array
    {
        $tipoInteracao = TipoInteracao::find($this->tipoInteracaoId);
        return [
            'erp-integration',
            $tipoInteracao ? "tenant:{$tipoInteracao->tenant_id}" : 'tenant:unknown',
            $tipoInteracao ? "tipo:{$tipoInteracao->id}" : 'tipo:unknown',
            'module:csi'
        ];
    }
}