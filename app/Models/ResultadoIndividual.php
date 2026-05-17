<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultadoIndividual extends Model
{
    protected $table = 'resultado_individual';

    protected $fillable = [
        'jogo_id',
        'aluno_id',
        'posicao',
    ];

    public function jogo(): BelongsTo
    {
        return $this->belongsTo(Jogo::class);
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }
}
