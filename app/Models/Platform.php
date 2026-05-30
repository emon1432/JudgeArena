<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Platform extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'short_name',
        'base_url',
        'profile_url',
        'icon',
        'description',
        'credentials',
        'status',
    ];

    protected $casts = [
        'credentials' => 'array',
        'status' => 'string',
    ];

    public function platformProfiles(): HasMany
    {
        return $this->hasMany(PlatformProfile::class, 'platform_id');
    }

    public function contests(): HasMany
    {
        return $this->hasMany(Contest::class, 'platform_id');
    }

    public function problems(): HasMany
    {
        return $this->hasMany(Problem::class, 'platform_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'platform_id');
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class, 'platform_id');
    }

    public function syncStates(): HasMany
    {
        return $this->hasMany(PlatformSyncState::class, 'platform_id');
    }

    public function contestRatingChanges(): HasMany
    {
        return $this->hasMany(ContestRatingChange::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
