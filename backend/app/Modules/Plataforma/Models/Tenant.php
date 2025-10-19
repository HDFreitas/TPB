<?php

namespace App\Modules\Plataforma\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Plataforma\Models\User;
use App\Modules\Plataforma\Models\Perfil;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'status',
        'dominio',
        'descricao'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relacionamento com Users
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relacionamento com Perfis
     */
    public function perfis(): HasMany
    {
        return $this->hasMany(Perfil::class);
    }
} 