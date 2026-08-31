<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\AtCoder;

use App\Core\DTOs\UserDTO;
use App\Platforms\AtCoder\AtCoderAdapter;
use Tests\TestCase;

class ImportUsersTest extends TestCase
{
    public function test_import_atcoder_users_command_updates_registered_profiles(): void
    {
        $platform = $this->createPlatform('atcoder', 'AtCoder', 'https://atcoder.jp');
        $profile = $this->createUserWithProfile($platform, 'chokudai');

        $adapter = $this->app->make(AtCoderAdapter::class);
        $mock = \Mockery::mock($adapter)->makePartial();
        $mock->shouldReceive('getUser')
            ->once()
            ->with('chokudai')
            ->andReturn(
                new UserDTO(
                    platform: 'atcoder',
                    platformHandle: 'chokudai',
                    rating: 3000,
                    country: 'Japan',
                    raw: ['handle' => 'chokudai', 'rating' => 3000],
                )
            );

        $this->app->instance(AtCoderAdapter::class, $mock);

        $this->artisan('judgearena:import-users', ['platform' => 'atcoder'])
            ->expectsOutputToContain('Platform: atcoder')
            ->expectsOutputToContain('Updated: 1')
            ->assertExitCode(0);

        $profile->refresh();
        $this->assertNotNull($profile->last_synced_at);
        $this->assertSame(3000, $profile->raw['rating']);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'user',
            'entity_platform_id' => 'chokudai',
            'sync_status' => 'synced',
        ]);
    }
}
