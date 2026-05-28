<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
