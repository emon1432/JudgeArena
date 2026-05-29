<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandingTaskResult extends Model
{
    protected $fillable = [
        'standing_id',
        'problem_id',
        'points',
        'penalty',
        'rejected_attempt_count',
        'result_type',
        'best_submission_time_seconds',
        'metadata',
        'raw',
    ];

    protected $casts = [
        'points' => 'decimal:2',
        'penalty' => 'integer',
        'rejected_attempt_count' => 'integer',
        'best_submission_time_seconds' => 'integer',
        'metadata' => 'array',
        'raw' => 'array',
    ];

    public function standing(): BelongsTo
    {
        return $this->belongsTo(Standing::class);
    }

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class);
    }
}
