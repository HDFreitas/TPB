<?php

namespace App\Modules\Csi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Plataforma\Models\Tenant;

class TipoInteracao extends Model
{
    use HasFactory;

    protected $table = 'tipos_interacao';

    protected $fillable = [
        'tenant_id',
        'nome',
        'conector_id',
        'porta',
        'rota',
        'status',
        'observacoes'
    ];

    protected $casts = [
        'status' => 'boolean',
        'porta' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relacionamento com Tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relacionamento com Conector
     */
    public function conector(): BelongsTo
    {
        return $this->belongsTo(Conector::class);
    }

    /**
     * Scope para buscar por tenant
     */
    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope para buscar apenas ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope para buscar por nome
     */
    public function scopeByNome($query, $nome)
    {
        return $query->where('nome', 'like', '%' . $nome . '%');
    }

    /**
     * Scope para buscar por conector
     */
    public function scopeByConector($query, $conectorId)
    {
        return $query->where('conector_id', $conectorId);
    }

    /**
     * Scope para buscar tipos que possuem conector ERP (codigo = '1-ERP')
     */
    public function scopeComConectorErp($query)
    {
        return $query->whereHas('conector', function ($q) {
            $q->where('codigo', '1-ERP');
        });
    }
}
