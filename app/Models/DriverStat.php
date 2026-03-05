<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverStat extends Model
{
    protected $fillable = [
        'user_id',
        'irating',
        'safety_rating',
        'wins',
        'podiums',
        'races',
        'poles',
        'favorite_category',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
