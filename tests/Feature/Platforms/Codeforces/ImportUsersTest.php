<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\Codeforces;

use App\Platforms\Codeforces\Mappers\CodeforcesUserMapper;
use App\Platforms\Codeforces\Services\Users as CodeforcesUsersService;
use Mockery\MockInterface;
use Tests\TestCase;

class ImportUsersTest extends TestCase
{
    public function test_import_codeforces_users_command_updates_registered_profiles(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces', 'https://codeforces.com');
        $profile = $this->createUserWithProfile($platform, 'tourist');

        $this->mock(CodeforcesUsersService::class, function (MockInterface $mock) {
            $mock->shouldReceive('infos')
                ->once()
                ->with(['tourist'])
                ->andReturn([
                    CodeforcesUserMapper::fromNormalized([
                        'handle' => 'tourist',
                        'firstName' => 'Gennady',
                        'lastName' => 'Korotkevich',
                        'rating' => 3800,
                        'rank' => 'legendary grandmaster',
                        'country' => 'Belarus',
                    ]),
                ]);
        });

        $this->artisan('judgearena:import-users', ['platform' => 'codeforces'])
            ->expectsOutputToContain('Platform: codeforces')
            ->expectsOutputToContain('Updated: 1')
            ->assertExitCode(0);

        $profile->refresh();
        $this->assertNotNull($profile->last_synced_at);
        $this->assertSame(3800, $profile->raw['rating']);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'user',
            'entity_platform_id' => 'tourist',
            'sync_status' => 'synced',
        ]);
    }
}
