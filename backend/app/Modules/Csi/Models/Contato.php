<?php

namespace App\Modules\Csi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Plataforma\Models\User;
use App\Modules\Plataforma\Models\Tenant;
use App\Modules\Csi\Models\Cliente;

class Contato extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'cliente_id',
        'codigo',
        'nome',
        'email',
        'cargo',
        'telefone',
        'tipo_perfil',
        'promotor',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'promotor' => 'boolean',
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
     * Relacionamento com Cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relacionamento com usuário que criou o contato
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relacionamento com usuário que atualizou o contato
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
