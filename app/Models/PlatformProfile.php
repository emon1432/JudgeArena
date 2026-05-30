<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformProfile extends Model
{
    protected $fillable = [
        'user_id',
        'platform_id',
        'handle',
        'raw',
        'metadata',
        'status',
        'last_synced_at',
    ];

    protected $casts = [
        'raw' => 'array',
        'metadata' => 'array',
        'last_synced_at' => 'datetime',
        'status' => 'string',
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class, 'platform_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contestRatingChanges(): HasMany
    {
        return $this->hasMany(ContestRatingChange::class, 'platform_profile_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
