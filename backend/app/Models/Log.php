<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Log extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'action',
        'description',
        'ip_address',
        'log_type',
        'status',
        'content',
    ];

    /**
     *  Conversões de tipos
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Plataforma\Models\User::class);
    }

    /**
     * Relacionamento com Tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Plataforma\Models\Tenant::class);
    }

    /**
     * Scope para filtrar por tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope para filtrar por tipo de log
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('log_type', $type);
    }

    /**
     * Scope para logs ativos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para logs de erro
     */
    public function scopeErrors($query)
    {
        return $query->where('log_type', 'error');
    }

    /**
     * Accessor para formatação do tipo de log
     */
    public function getFormattedTypeAttribute(): string
    {
        return match($this->log_type) {
            'info' => 'Informativo',
            'error' => 'Erro',
            'warning' => 'Aviso',
            'debug' => 'Debug',
            default => 'Desconhecido'
        };
    }

    /**
     * Accessor para formatação do status
     */
    public function getFormattedStatusAttribute(): string
    {
        return match($this->status) {
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'processed' => 'Processado',
            default => 'Desconhecido'
        };
    }
}