<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Penalidade extends Model
{
    use LogsActivity;

    protected $fillable = [
        'tipo',
        'turma_id',
        'aluno_id',
        'motivo',
        'pontos',
        'registrado_por',
    ];

    protected $casts = [
        'pontos' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['tipo', 'turma_id', 'aluno_id', 'motivo', 'pontos'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
