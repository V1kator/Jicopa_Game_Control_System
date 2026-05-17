<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Turma;

class Presenca extends Model
{
    protected $fillable = [
        'jogo_id',
        'aluno_id',
        'presente',
        'is_substituto',
        'substituto_de_time_id',
    ];

    protected $casts = [
        'presente' => 'boolean',
        'is_substituto' => 'boolean',
        'substituto_de_time_id' => 'integer',
    ];

    public function jogo(): BelongsTo
    {
        return $this->belongsTo(Jogo::class);
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function substitutoDeTime(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'substituto_de_time_id');
    }
}
