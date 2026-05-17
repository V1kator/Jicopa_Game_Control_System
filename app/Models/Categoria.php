<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Categoria extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function turmas(): BelongsToMany
    {
        return $this->belongsToMany(Turma::class, 'categoria_turma');
    }

    public function esportes(): BelongsToMany
    {
        return $this->belongsToMany(Esporte::class, 'categoria_esporte');
    }
}
