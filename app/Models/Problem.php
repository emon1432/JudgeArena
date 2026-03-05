<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Problem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'platform_id',
        'contest_id',
        'platform_problem_id',
        'slug',
        'name',
        'code',
        'description',
        'difficulty',
        'rating',
        'points',
        'accuracy',
        'acceptance_rate',
        'time_limit_ms',
        'memory_limit_mb',
        'total_submissions',
        'accepted_submissions',
        'solved_count',
        'tags',
        'topics',
        'url',
        'editorial_url',
        'raw',
        'status',
        'is_premium',
    ];

    protected $casts = [
        'points' => 'float',
        'accuracy' => 'float',
        'acceptance_rate' => 'float',
        'is_premium' => 'boolean',
        'tags' => 'array',
        'topics' => 'array',
        'raw' => 'array',
    ];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }
}
