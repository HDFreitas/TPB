<?php

namespace App\Modules\Csi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Plataforma\Models\Tenant;

class Conector extends Model
{
    use HasFactory;

    protected $table = 'conectores';

    protected $fillable = [
        'tenant_id',
        'codigo',
        'nome',
        'url',
        'status',
        'usuario',
        'senha',
        'token',
        'configuracao_adicional',
        'observacoes'
    ];

    protected $casts = [
        'status' => 'boolean',
        'configuracao_adicional' => 'array',
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
     * Scope para buscar por código
     */
    public function scopeByCodigo($query, $codigo)
    {
        return $query->where('codigo', $codigo);
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
     * Verificar se é conector ERP
     */
    public function isErp(): bool
    {
        return $this->codigo === '1-ERP';
    }

    /**
     * Verificar se é conector Movidesk
     */
    public function isMovidesk(): bool
    {
        return $this->codigo === '2-Movidesk';
    }

    /**
     * Verificar se é conector CRM Eleve
     */
    public function isCrmEleve(): bool
    {
        return $this->codigo === '3-CRM Eleve';
    }

    /**
     * Retorna a senha descriptografada se armazenada criptografada
     */
    public function getSenhaDecryptedAttribute(): ?string
    {
        if (empty($this->senha)) return null;
        try {
            return decrypt($this->senha);
        } catch (\Exception $e) {
            // Se não estiver criptografada, retorna o valor original
            return $this->senha;
        }
    }
}

