<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'participant_count' => 'integer',
        'duration_seconds' => 'integer',
        'metadata' => 'array',
        'raw' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class, 'platform_id');
    }

    public function problems(): HasMany
    {
        return $this->hasMany(Problem::class, 'contest_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'contest_id');
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class, 'contest_id');
    }

    public function contestRatingChanges(): HasMany
    {
        return $this->hasMany(ContestRatingChange::class, 'contest_id');
    }
}
