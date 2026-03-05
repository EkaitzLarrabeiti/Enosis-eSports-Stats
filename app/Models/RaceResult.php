<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceResult extends Model
{
    protected $fillable = [
        'user_id',
        'subsession_id',
        'series_name',
        'track_name',
        'finish_position',
        'starting_position',
        'incidents',
        'irating_change',
        'race_date',
    ];

    protected function casts(): array
    {
        return [
            'race_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
