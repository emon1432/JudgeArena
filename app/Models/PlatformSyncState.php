<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformSyncState extends Model
{
    protected $fillable = [
        'platform_id',
        'resource',
        'cursor',
        'last_synced_at',
        'next_allowed_at',
        'last_error',
        'retry_count',
        'metadata',
        'status',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_synced_at' => 'datetime',
        'next_allowed_at' => 'datetime',
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
