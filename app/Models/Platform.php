<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'base_url',
        'image',
        'status',
    ];

    public function platformProfiles()
    {
        return $this->hasMany(PlatformProfile::class);
    }

    public function contests()
    {
        return $this->hasMany(Contest::class);
    }

    public function problems()
    {
        return $this->hasMany(Problem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
