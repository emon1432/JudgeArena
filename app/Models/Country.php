<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'flag',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'country_id');
    }

    public function institutes(): HasMany
    {
        return $this->hasMany(Institute::class, 'country_id');
    }
}
