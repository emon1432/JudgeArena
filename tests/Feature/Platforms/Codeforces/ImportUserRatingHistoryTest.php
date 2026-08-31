<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\Codeforces;

use App\Core\DTOs\RatingChangeDTO;
use App\Models\Contest;
use App\Platforms\Codeforces\CodeforcesAdapter;
use Tests\TestCase;

class ImportUserRatingHistoryTest extends TestCase
{
    public function test_import_codeforces_user_rating_history_command_saves_rating_changes(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces', 'https://codeforces.com');
        $profile = $this->createUserWithProfile($platform, 'tourist');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '2000',
            'name' => 'Codeforces Round 950 (Div. 2)',
            'slug' => '2000-codeforces-round-950-div-2',
            'phase' => 'FINISHED',
        ]);

        $adapter = $this->app->make(CodeforcesAdapter::class);
        $mock = \Mockery::mock($adapter)->makePartial();
        $mock->shouldReceive('getUserRatingHistory')
            ->once()
            ->with('tourist')
            ->andReturn([
                new RatingChangeDTO(
                    platform: 'codeforces',
                    contestPlatformId: '2000',
                    handle: 'tourist',
                    rank: 1,
                    oldRating: 3700,
                    newRating: 3750,
                    raw: ['contestId' => 2000],
                ),
            ]);

        $this->app->instance(CodeforcesAdapter::class, $mock);

        $this->artisan('judgearena:import-user-rating-history', ['platform' => 'codeforces'])
            ->expectsOutputToContain('Platform: codeforces')
            ->expectsOutputToContain('Created: 1')
            ->assertExitCode(0);

        $this->assertDatabaseHas('contest_rating_changes', [
            'platform_id' => $platform->id,
            'platform_profile_id' => $profile->id,
            'contest_id' => $contest->id,
            'handle' => 'tourist',
            'rank' => 1,
            'old_rating' => 3700,
            'new_rating' => 3750,
        ]);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'user_rating_history',
            'entity_platform_id' => 'tourist',
            'sync_status' => 'synced',
        ]);
    }
}
