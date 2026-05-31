<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'level',
        'category',
        'platform',
        'entity_type',
        'entity_id',
        'message',
        'context',
        'source',
        'user_id',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
