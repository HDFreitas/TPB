<?php

namespace App\Modules\Csi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Plataforma\Models\User;
use App\Modules\Plataforma\Models\Tenant;
use App\Modules\Csi\Models\TipoInteracao;

class Interacao extends Model
{
    use HasFactory;

    protected $table = 'interacoes';

    protected $fillable = [
        'tenant_id',
        'cliente_id',
        'tipo_interacao_id',
        'tipo_interacao_nome',
        'chave',
        'origem',
        'titulo',
        'descricao',
        'data_interacao',
        'valor',
        'user_id',
    ];
    /**
     * Relacionamento com TipoInteracao
     */
    public function tipoInteracao(): BelongsTo
    {
        return $this->belongsTo(TipoInteracao::class, 'tipo_interacao_id');
    }

    protected $casts = [
        'data_interacao' => 'datetime',
        'valor' => 'decimal:2',
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
     * Relacionamento com User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
