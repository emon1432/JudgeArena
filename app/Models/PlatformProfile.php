<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformProfile extends Model
{
    protected $fillable = [
        'user_id',
        'platform_id',
        'handle',
        'platform_user_id',
        'name',
        'avatar_url',
        'joined_at',
        'ranking',
        'rating',
        'total_solved',
        'profile_url',
        'profile_source',
        'visibility_status',
        'profile_data',
        'status',
        'last_synced_at',
        'last_sync_status',
        'last_sync_error',
        'last_sync_duration_ms',
        'sync_attempts',
        'captured_at',
    ];


    protected $casts = [
        'profile_data' => 'array',
        'joined_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'captured_at' => 'datetime',
    ];

    public function getRawAttribute(): ?array
    {
        $value = $this->attributes['profile_data'] ?? null;

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : null;
        }

        return is_array($value) ? $value : null;
    }

    public function setRawAttribute(mixed $value): void
    {
        $this->attributes['profile_data'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getRawProfileAttribute(): ?array
    {
        return $this->raw;
    }

    public function setRawProfileAttribute(mixed $value): void
    {
        $this->raw = $value;
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function getRankingAttribute(): mixed
    {
        $ranking = $this->attributes['ranking'] ?? null;

        if (is_numeric($ranking)) {
            return (int) $ranking;
        }

        $rawProfile = $this->raw;

        if (! is_array($rawProfile)) {
            return null;
        }

        $ranking = data_get($rawProfile, 'ranking')
            ?? data_get($rawProfile, 'rank_by_solved')
            ?? data_get($rawProfile, 'rank_by_rating')
            ?? data_get($rawProfile, 'global_rank')
            ?? data_get($rawProfile, 'rank')
            ?? data_get($rawProfile, 'profile.ranking')
            ?? data_get($rawProfile, 'contest_global_ranking')
            ?? data_get($rawProfile, 'profile_metrics.global_rank')
            ?? data_get($rawProfile, 'profile.rank');

        if (is_string($ranking)) {
            $normalized = trim($ranking);

            if (preg_match('/^(\d+)(st|nd|rd|th)$/i', $normalized, $matches)) {
                return (int) $matches[1];
            }
        }

        return $ranking;
    }
}
