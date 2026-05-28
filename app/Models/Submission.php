<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = [
        'platform_id',
        'contest_id',
        'problem_id',
        'platform_profile_id',
        'platform_submission_id',
        'author_handle',
        'verdict',
        'language',
        'points',
        'passed_test_count',
        'time_consumed_ms',
        'memory_consumed_bytes',
        'submitted_at',
        'last_synced_at',
        'metadata',
        'raw',
        'status'
    ];

    protected $casts = [
        'points' => 'float',
        'passed_test_count' => 'integer',
        'time_consumed_ms' => 'integer',
        'memory_consumed_bytes' => 'integer',
        'submitted_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'metadata' => 'array',
        'raw' => 'array',
        'status' => 'string',
    ];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function problem()
    {
        return $this->belongsTo(Problem::class);
    }

    public function profile()
    {
        return $this->belongsTo(PlatformProfile::class, 'platform_profile_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
