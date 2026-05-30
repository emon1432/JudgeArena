<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContestRatingChange extends Model
{
    protected $fillable = [
        'platform_id',
        'contest_id',
        'platform_profile_id',
        'handle',
        'rank',
        'old_rating',
        'new_rating',
        'rating_change',
        'performance',
        'last_synced_at',
        'metadata',
        'raw',
        'status',
    ];

    protected $casts = [
        'rank' => 'integer',
        'old_rating' => 'integer',
        'new_rating' => 'integer',
        'rating_change' => 'integer',
        'performance' => 'integer',
        'last_synced_at' => 'datetime',
        'metadata' => 'array',
        'raw' => 'array',
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function platformProfile(): BelongsTo
    {
        return $this->belongsTo(PlatformProfile::class);
    }
}
