<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Problem extends Model
{
    protected $fillable = [
        'platform_id',
        'contest_id',
        'platform_problem_id',
        'slug',
        'name',
        'code',
        'rating',
        'points',
        'time_limit_ms',
        'memory_limit_mb',
        'total_submissions',
        'accepted_submissions',
        'solved_count',
        'tags',
        'url',
        'editorial_url',
        'last_synced_at',
        'metadata',
        'raw',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'points' => 'float',
        'time_limit_ms' => 'integer',
        'memory_limit_mb' => 'integer',
        'total_submissions' => 'integer',
        'accepted_submissions' => 'integer',
        'solved_count' => 'integer',
        'tags' => 'array',
        'metadata' => 'array',
        'raw' => 'array',
        'last_synced_at' => 'datetime',
        'status' => 'string',
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class, 'platform_id');
    }

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class, 'contest_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'problem_id');
    }

    public function standingTaskResults(): HasMany
    {
        return $this->hasMany(StandingTaskResult::class, 'problem_id');
    }

    public function contestRatingChanges(): HasMany
    {
        return $this->hasMany(ContestRatingChange::class, 'problem_id');
    }
}
