<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Turma extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'period',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'period', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'categoria_turma');
    }

    public function alunos(): HasMany
    {
        return $this->hasMany(Aluno::class);
    }

    public function jogosComoTime1(): HasMany
    {
        return $this->hasMany(Jogo::class, 'time1_id');
    }

    public function jogosComoTime2(): HasMany
    {
        return $this->hasMany(Jogo::class, 'time2_id');
    }

    public function penalidades(): HasMany
    {
        return $this->hasMany(Penalidade::class);
    }

    public function avaliacaoNotas(): HasMany
    {
        return $this->hasMany(AvaliacaoNota::class);
    }
}
