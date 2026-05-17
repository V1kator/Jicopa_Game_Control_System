<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoringConfig extends Model
{
    protected $fillable = [
        'points_per_win',
        'points_per_draw',
        'points_per_extra',
    ];

    protected $casts = [
        'points_per_win'   => 'integer',
        'points_per_draw'  => 'integer',
        'points_per_extra' => 'integer',
    ];
}
