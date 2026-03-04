<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'platform_id',
        'platform_contest_id',
        'slug',
        'name',
        'description',
        'type',
        'phase',
        'duration_seconds',
        'start_time',
        'end_time',
        'url',
        'participant_count',
        'is_rated',
        'tags',
        'raw',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_rated' => 'boolean',
        'tags' => 'array',
        'raw' => 'array',
    ];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function problems()
    {
        return $this->hasMany(Problem::class);
    }
}
