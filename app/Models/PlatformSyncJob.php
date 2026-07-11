<?php

namespace App\Models;

use App\Enums\PlatformSyncJobEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PlatformSyncJob extends Model
{
    protected $fillable = [
        'platform_id',
        'entity',
        'enabled',
        'priority',
        'interval_minutes',
        'last_started_at',
        'last_finished_at',
        'last_failed_at',
        'last_success_at',
        'last_error',
        'metadata',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'entity' => PlatformSyncJobEntity::class,
        'metadata' => 'array',
        'last_started_at' => 'datetime',
        'last_finished_at' => 'datetime',
        'last_failed_at' => 'datetime',
        'last_success_at' => 'datetime',
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class, 'platform_id');
    }

    public function isDue(?Carbon $now = null): bool
    {
        if (! $this->enabled) {
            return false;
        }

        $now ??= now();

        if ($this->last_success_at === null) {
            return true;
        }

        return $this->last_success_at
            ->copy()
            ->addMinutes($this->interval_minutes)
            ->lte($now);
    }

    public function nextRunAt(): ?Carbon
    {
        if ($this->last_success_at === null) {
            return null;
        }

        return $this->last_success_at
            ->copy()
            ->addMinutes($this->interval_minutes);
    }

    public function hasNeverRun(): bool
    {
        return $this->last_success_at === null;
    }
}
