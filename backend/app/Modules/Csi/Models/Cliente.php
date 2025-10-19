<?php

namespace App\Modules\Csi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Plataforma\Models\User;
use App\Modules\Plataforma\Models\Tenant;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'razao_social', 
        'nome_fantasia', 
        'codigo_ramo', 
        'cidade', 
        'estado',
        'cnpj_cpf',
        'codigo_senior',
        'status',
        'cliente_referencia',
        'tipo_perfil',
        'classificacao',
        'email',
        'telefone',
        'celular',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cep',
        'observacoes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'status' => 'boolean',
        'cliente_referencia' => 'boolean',
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
     * Relacionamento com Interações
     */
    public function interacoes(): HasMany
    {
        return $this->hasMany(Interacao::class);
    }

    /**
     * Relacionamento com Contatos
     */
    public function contatos(): HasMany
    {
        return $this->hasMany(Contato::class);
    }

    /**
     * Relacionamento com usuário que criou o cliente
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relacionamento com usuário que atualizou o cliente
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
