<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function createPlatform(string $slug, string $name, string $baseUrl = 'https://example.com'): Platform
    {
        return Platform::query()->create([
            'name' => $name,
            'slug' => $slug,
            'short_name' => strtoupper($slug),
            'base_url' => $baseUrl,
            'status' => 'Active',
        ]);
    }

    protected function createUserWithProfile(Platform $platform, string $handle): PlatformProfile
    {
        $user = User::query()->create([
            'name' => 'User ' . $handle,
            'username' => strtolower(Str::slug($handle . '-' . Str::random(5))),
            'email' => strtolower($handle) . '-' . Str::random(5) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        return PlatformProfile::query()->create([
            'user_id' => $user->id,
            'platform_id' => $platform->id,
            'handle' => $handle,
            'status' => 'Active',
        ]);
    }
}

