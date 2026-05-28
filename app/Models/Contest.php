<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contest extends Model
{
    protected $fillable = [
        'platform_id',
        'platform_contest_id',
        'slug',
        'name',
        'type',
        'phase',
        'is_rated',
        'duration_seconds',
        'start_time',
        'end_time',
        'url',
        'participant_count',
        'last_synced_at',
        'metadata',
        'raw',
        'status',
    ];

    protected $casts = [
        'is_rated' => 'boolean',
        'metadata' => 'array',
        'raw' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function problems()
    {
        return $this->hasMany(Problem::class);
    }
}
