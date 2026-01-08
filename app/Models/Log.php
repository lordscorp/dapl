<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';

    // Ajuste conforme os campos que você criou na migration
    protected $fillable = [
        'acao',
        'detalhes',
        'rf',
        'nome',
    ];

    // Se quiser casts (opcional)
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
