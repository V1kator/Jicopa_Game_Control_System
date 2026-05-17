<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvaliacaoConfig extends Model
{
    protected $table = 'avaliacao_config';

    protected $fillable = [
        'num_jurados',
        'nota_min',
        'nota_max',
        'pontos_bonus_melhor',
    ];

    protected $casts = [
        'num_jurados' => 'integer',
        'nota_min' => 'decimal:2',
        'nota_max' => 'decimal:2',
        'pontos_bonus_melhor' => 'integer',
    ];
}
