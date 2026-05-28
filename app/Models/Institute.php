<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_id',
        'website',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'institute_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
