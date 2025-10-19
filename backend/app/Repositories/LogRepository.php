<?php

namespace App\Repositories;

use App\Models\Log;
use App\Interfaces\LogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LogRepository implements LogRepositoryInterface
{
    public function getAll($perPage = 15, $tenantId)
    {
        $query = Log::query();
        
        // Sempre filtrar por tenant (segurança)
        $query->forTenant($tenantId);
        
        return $query->with(['user', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById($id, $tenantId)
    {
        $query = Log::query();
        
        // Sempre filtrar por tenant (segurança)
        $query->forTenant($tenantId);
        
        return $query->with(['user', 'tenant'])->find($id);
    }

    public function create(array $data)
    {
        return Log::create($data);
    }

    public function delete($id)
    {
        $log = Log::findOrFail($id);
        return $log->delete();
    }

    public function search(array $filters, $perPage = 15)
    {
        $query = Log::query();
        
        // Filtro por tenant (obrigatório)
        if (isset($filters['tenant_id'])) {
            $query->forTenant($filters['tenant_id']);
        }
        
        // Filtro por usuário
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        // Filtro por ação/operação (busca por operação: CREATE, UPDATE, DELETE, etc)
        if (isset($filters['action'])) {
            $query->where('action', 'like', $filters['action'] . '_%');
        }
        
        // Filtro por tipo de log
        if (isset($filters['log_type'])) {
            $query->ofType($filters['log_type']);
        }
        
        // Filtro por status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        // Filtro por IP
        if (isset($filters['ip_address'])) {
            $query->where('ip_address', $filters['ip_address']);
        }
        
        // Filtro por período - data inicial
        if (isset($filters['created_at_from'])) {
            $query->where('created_at', '>=', $filters['created_at_from']);
        }
        
        // Filtro por período - data final
        if (isset($filters['created_at_to'])) {
            $query->where('created_at', '<=', $filters['created_at_to']);
        }
        
        // Filtro por conteúdo
        if (isset($filters['content'])) {
            $query->where('content', 'like', '%' . $filters['content'] . '%');
        }
        
        return $query->with(['user', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getErrors($tenantId, $perPage = 15)
    {
        return Log::forTenant($tenantId)
            ->errors()
            ->with(['user', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getByType($tenantId, $type, $perPage = 15)
    {
        return Log::forTenant($tenantId)
            ->ofType($type)
            ->with(['user', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getStats($tenantId)
    {
        $query = Log::query();
        
        // Sempre filtrar por tenant (segurança)
        $query->forTenant($tenantId);
        
        return [
            'total' => $query->count(),
            'errors' => $query->errors()->count(),
            'warnings' => $query->ofType('warning')->count(),
            'info' => $query->ofType('info')->count(),
            'active' => $query->active()->count(),
        ];
    }

    public function getByPeriod($tenantId, $days = 7)
    {
        return Log::forTenant($tenantId)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, log_type, COUNT(*) as count')
            ->groupBy('date', 'log_type')
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('date')
            ->map(function ($dayLogs) {
                return $dayLogs->pluck('count', 'log_type');
            });
    }

    public function getTopActions($tenantId, $limit = 10)
    {
        return Log::forTenant($tenantId)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->pluck('count', 'action');
    }

    public function getByUser($tenantId, $userId, $perPage = 15)
    {
        return Log::forTenant($tenantId)
            ->where('user_id', $userId)
            ->with(['user', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Retorna lista de operações únicas disponíveis nos logs (create, update, delete, etc)
     */
    public function getUniqueActions($tenantId)
    {
        // Busca todas as ações e extrai apenas a primeira parte (operação)
        $actions = Log::forTenant($tenantId)
            ->select('action')
            ->distinct()
            ->pluck('action');
        
        // Extrai as operações únicas (primeira parte antes do underscore)
        $operations = $actions->map(function($action) {
            return explode('_', $action)[0];
        })->unique()->sort()->values();
        
        return $operations;
    }
} 