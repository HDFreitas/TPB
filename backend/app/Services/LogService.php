<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Support\Facades\Log as LaravelLog;
use App\Repositories\LogRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;

class LogService{
    protected $logRepository;

    protected $job = null;

    public function setJob($job)
    {
        $this->job = $job;
    }

    public function __construct(LogRepository $logRepository){
        $this->logRepository = $logRepository;
    }

    /**
     *  Criar um log automaticamente
     */
    public function createLog(array $data): Log {

        $logData = [
            'user_id' => $data['user_id'] ?? Auth::id(),
            'tenant_id' => $data['tenant_id'] ?? Auth::user()->tenant_id,
            'action' => $data['action'],
            'description' => $data['description'],
            'ip_address' => $data['ip_address'] ?? Request::ip(),
            'log_type' => $data['log_type'] ?? 'info',
            'status' => $data['status'] ?? 'active',
            'content' => $data['content'] ?? null,
        ];

        return $this->logRepository->create($logData);
    }

    /** 
     * Log de ação do usuário
     */
    public function logUserAction(string $action, string $description = null, array $content = []): Log{
        // Verifica se o job foi definido na instância
        $jobId = $this->job ?? null;

        // Adiciona o job_id ao conteúdo do log se existir
        if ($jobId) {
            $content['job_id'] = method_exists($jobId, 'getJobId') ? $jobId->getJobId() : 'N/A';
        } elseif (isset($content['tenant_id'])) {
            // Mantém o tenant_id se estiver no conteúdo
            $content['tenant_id'] = $content['tenant_id'];
        }

        $logData = [
            'action' => $action,
            'description' => $description,
            'log_type' => 'info',
            'content' => json_encode($content),
        ];

        // Se houver tenant_id no content, repasse para o log
        if (isset($content['tenant_id'])) {
            $logData['tenant_id'] = $content['tenant_id'];
        }

        return $this->createLog($logData);
    }

    /** 
     * Log de erro
     */
    public function logError(string $action, string $description, array $content = []): Log {
        return $this->createLog([
            'action' => $action,
            'description' => $description,
            'log_type' => 'error',
            'content' => json_encode($content),
        ]);
    }
    
    /**
     * Log de autenticação
     */
    public function logAuth(string $action, bool $success = true, string $description = null): Log {
        return $this->createLog([
            'action' => $action,
            'description' => $description ?? ($success ? 'Login realizado com sucesso' : 'Falha no login'),
            'log_type' => $success ? 'info' : 'error',
            'content' => json_encode(['success' => $success]),
        ]);
    }

    /**
     * Log de CRUD operações
     */
    public function logCrud(string $operation, string $model, int $modelId, array $content = []): Log {
        $action = strtoupper($operation) . '_' . strtoupper($model);
        $description = "Operação {$operation} realizada em {$model}";

        if($modelId){
            $description .= " com ID {$modelId}";
        }

        return $this->createLog([
            'action' => $action,
            'description' => $description,
            'log_type' => 'info',
            'content' => json_encode(array_merge([
                'operation' => $operation,
                'model' => $model,
                'model_id' => $modelId,
            ], $content)),
        ]);
    }

    /**
     * Dashboard - Estatísticas gerais
     */
    public function getDashboardStats(int $tenantId): array {
        $now = Carbon::now();
        $last24h = $now->copy()->subDay();

        // Query base para filtrar por tenant
        $baseQuery = Log::query()->forTenant($tenantId);

        // Total de logs
        $totalLogs = (clone $baseQuery)->count();
        
        // Logs nas últimas 24h
        $logsLast24h = (clone $baseQuery)->where('created_at', '>=', $last24h)->count();
        
        // Logs de erro
        $errorLogs = (clone $baseQuery)->errors()->count();
        
        // Logs de erro nas últimas 24h
        $errorLogsLast24h = (clone $baseQuery)->errors()->where('created_at', '>=', $last24h)->count();
        
        // Logs por tipo
        $logsByType = (clone $baseQuery)
            ->selectRaw('log_type, COUNT(*) as count')
            ->groupBy('log_type')
            ->pluck('count', 'log_type')
            ->toArray();
        
        // Logs por ação (top 10)
        $logsByAction = (clone $baseQuery)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->pluck('count', 'action')
            ->toArray();

        return [
            'total_logs' => $totalLogs,
            'logs_last_24h' => $logsLast24h,
            'error_logs' => $errorLogs,
            'error_logs_last_24h' => $errorLogsLast24h,
            'logs_by_type' => $logsByType,
            'logs_by_action' => $logsByAction,
        ];
     }

     /**
      * Dashboard - Logs por período
      */

      public function getLogsByPeriod(int $tenantId, int $days = 7): array{
        $query = Log::query();

        // Sempre filtrar por tenant (segurança)
        $query->forTenant($tenantId);

        $logs = $query->where('created_at', '>=', Carbon::now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date', 'log_type')
            ->orderBy('date', 'desc')
            ->get();

        return $logs->groupBy('date')->map(function($dayLogs) {
            return dayLogs->pluck('count', 'log_type');
        });
      }

      /**
       * Dashboard - Logs por hora (últimas 24h)
       * Ordem: 24h atrás (esquerda) → agora (direita)
       * Timezone: America/Sao_Paulo (Brasília)
       */
      public function getHourlyTrend(int $tenantId): array {
        // Garantir que estamos usando o timezone configurado
        $now = Carbon::now(config('app.timezone'));
        $last24h = $now->copy()->subDay();

        // Buscar logs das últimas 24h agrupados por hora
        // Converter para o timezone da aplicação (America/Sao_Paulo)
        $logs = Log::query()
            ->forTenant($tenantId)
            ->where('created_at', '>=', $last24h)
            ->selectRaw("TO_CHAR(created_at AT TIME ZONE 'UTC' AT TIME ZONE 'America/Sao_Paulo', 'YYYY-MM-DD HH24:00:00') as hour, COUNT(*) as count")
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->pluck('count', 'hour')
            ->toArray();

        // Criar array de 24 horas com valores zerados
        // Ordem: hora mais antiga (24h atrás) na posição 0 → hora mais recente (agora) na posição 23
        $hourlyData = [];
        $labels = [];
        
        for ($i = 23; $i >= 0; $i--) {
            $hourTime = $now->copy()->subHours($i);
            $hourKey = $hourTime->format('Y-m-d H:00:00');
            $hourLabel = $hourTime->format('H:00');
            
            $hourlyData[] = (int)($logs[$hourKey] ?? 0);
            $labels[] = $hourLabel;
        }

        return [
            'data' => $hourlyData,
            'labels' => $labels,
        ];
      }

        /**
         * Busca logs com filtros avançados
         */

        public function searchLogs(int $tenantId, int $perPage = 15, array $filters = []): array {
            return $this->logRepository->search($tenantId, $perPage, $filters);
        }

        /**
         * Cria log para os jobs do sistema
         */
        public function createJobLog(array $data): Log{

        // Tenta obter o tipo_interacao_id do $data ou do content
        $tipoInteracaoId = $data['tipo_interacao_id'] ?? null;
        $contentArr = null;
        if (!$tipoInteracaoId && isset($data['content'])) {
            // content pode ser array ou json
            if (is_array($data['content'])) {
                $contentArr = $data['content'];
            } else {
                $decoded = json_decode($data['content'], true);
                if (is_array($decoded)) {
                    $contentArr = $decoded;
                }
            }
            if ($contentArr && isset($contentArr['tipo_interacao_id'])) {
                $tipoInteracaoId = $contentArr['tipo_interacao_id'];
            }
        }

        // Busca o tenant_id do tipo de interação se não informado
        $tenantId = $data['tenant_id'] ?? null;
        if (!$tenantId && $tipoInteracaoId) {
            $tipoInteracao = \App\Modules\Csi\Models\TipoInteracao::find($tipoInteracaoId);
            $tenantId = $tipoInteracao?->tenant_id;
            if (!$tenantId) {
                \Log::warning('[LOGSERVICE][JOB] tenant_id não encontrado para tipoInteracaoId', [
                    'tipo_interacao_id' => $tipoInteracaoId,
                    'data' => $data
                ]);
            }
        }

        $content = $data['content'] ?? null;
        if (is_array($content)) {
            $content = json_encode($content);
        }
        $logData = [
            'user_id' => $data['user_id'] ?? null,
            'tenant_id' => $tenantId,
            'action' => $data['action'],
            'description' => $data['description'],
            'ip_address' => $data['ip_address'] ?? null,
            'log_type' => $data['log_type'] ?? 'info',
            'status' => $data['status'] ?? 'active',
            'content' => $content,
        ];

        return $this->logRepository->create($logData);
        }
}