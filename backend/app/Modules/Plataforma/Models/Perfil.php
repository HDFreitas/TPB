<?php

namespace App\Modules\Plataforma\Models;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    protected $fillable = [
        'tenant_id',
        'nome',
        'descricao',
    ];

    protected $table = 'perfis';

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
} 