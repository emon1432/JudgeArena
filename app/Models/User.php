<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'username',
        'role',
        'email',
        'phone',
        'password',
        'date_of_birth',
        'gender',
        'country_id',
        'institute_id',
        'fav_quote',
        'website',
        'twitter',
        'github',
        'linkedin',
        'image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function platformProfiles()
    {
        return $this->hasMany(PlatformProfile::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }
}
