<?php

namespace App\Models;

use App\Enums\PlatformSyncEntityType;
use App\Enums\PlatformSyncStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Persisted sync state is the importer's source of truth for progress,
 * retries, and crash recovery. It is separate from the imported contest,
 * problem, or user rows because those rows describe domain data, not whether
 * the ingestion workflow has already completed.
 */
class PlatformSyncState extends Model
{
    protected $fillable = [
        'platform_id',
        'entity_type',
        'entity_platform_id',
        'sync_status',
        'last_synced_at',
        'last_attempted_at',
        'last_error',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_synced_at' => 'datetime',
        'last_attempted_at' => 'datetime',
        'entity_type' => PlatformSyncEntityType::class,
        'sync_status' => PlatformSyncStatus::class,
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
