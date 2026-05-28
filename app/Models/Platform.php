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
        'icon',
        'description',
        'settings',
        'status',
    ];

    protected $casts = [
        'settings' => 'array',
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

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
