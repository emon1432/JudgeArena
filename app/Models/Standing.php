<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Standing extends Model
{
    protected $fillable = [
        'platform_id',
        'contest_id',
        'platform_profile_id',
        'participant_key',
        'participant_type',
        'participant_name',
        'rank',
        'points',
        'penalty',
        'successful_hack_count',
        'unsuccessful_hack_count',
        'last_submission_time_seconds',
        'last_synced_at',
        'metadata',
        'raw',
        'status',
    ];

    protected $casts = [
        'rank' => 'integer',
        'points' => 'float',
        'penalty' => 'integer',
        'successful_hack_count' => 'integer',
        'unsuccessful_hack_count' => 'integer',
        'last_submission_time_seconds' => 'integer',
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

    public function platformProfile(): BelongsTo
    {
        return $this->belongsTo(PlatformProfile::class, 'platform_profile_id');
    }
}
