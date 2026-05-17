<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Jogo extends Model
{
    use LogsActivity;

    protected $fillable = [
        'categoria_id',
        'esporte_id',
        'time1_id',
        'time2_id',
        'data',
        'hora',
        'local',
        'placar_time1',
        'placar_time2',
        'vencedor_id',
        'cancelado',
    ];

    protected $casts = [
        'data' => 'date',
        'cancelado' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['categoria_id', 'esporte_id', 'time1_id', 'time2_id', 'data', 'hora', 'local', 'placar_time1', 'placar_time2', 'vencedor_id', 'cancelado'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function esporte(): BelongsTo
    {
        return $this->belongsTo(Esporte::class);
    }

    public function time1(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'time1_id');
    }

    public function time2(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'time2_id');
    }

    public function vencedor(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'vencedor_id');
    }

    public function resultadosIndividuais(): HasMany
    {
        return $this->hasMany(ResultadoIndividual::class);
    }

    public function presencas(): HasMany
    {
        return $this->hasMany(Presenca::class);
    }
}
