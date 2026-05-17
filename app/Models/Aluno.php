<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Aluno extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'turma_id',
        'period',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'turma_id', 'period', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }

    public function esportes(): BelongsToMany
    {
        return $this->belongsToMany(Esporte::class, 'aluno_esporte');
    }

    public function presencas(): HasMany
    {
        return $this->hasMany(Presenca::class);
    }
}
