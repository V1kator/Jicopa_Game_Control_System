<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvaliacaoNota extends Model
{
    protected $table = 'avaliacao_notas';

    protected $fillable = [
        'turma_id',
        'categoria_id',
        'jurado_num',
        'nota',
    ];

    protected $casts = [
        'jurado_num' => 'integer',
        'nota' => 'decimal:2',
    ];

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
}
