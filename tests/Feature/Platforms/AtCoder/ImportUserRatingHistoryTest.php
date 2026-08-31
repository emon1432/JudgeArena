<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\AtCoder;

use App\Core\DTOs\RatingChangeDTO;
use App\Models\Contest;
use App\Platforms\AtCoder\AtCoderAdapter;
use Tests\TestCase;

class ImportUserRatingHistoryTest extends TestCase
{
    public function test_import_atcoder_user_rating_history_command_saves_rating_changes(): void
    {
        $platform = $this->createPlatform('atcoder', 'AtCoder', 'https://atcoder.jp');
        $profile = $this->createUserWithProfile($platform, 'chokudai');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => 'abc350',
            'name' => 'AtCoder Beginner Contest 350',
            'slug' => 'abc350-atcoder-beginner-contest-350',
            'phase' => 'FINISHED',
        ]);

        $adapter = $this->app->make(AtCoderAdapter::class);
        $mock = \Mockery::mock($adapter)->makePartial();
        $mock->shouldReceive('getUserRatingHistory')
            ->once()
            ->with('chokudai')
            ->andReturn([
                new RatingChangeDTO(
                    platform: 'atcoder',
                    contestPlatformId: 'abc350',
                    handle: 'chokudai',
                    rank: 1,
                    oldRating: 2950,
                    newRating: 3000,
                    raw: ['contestId' => 'abc350'],
                ),
            ]);

        $this->app->instance(AtCoderAdapter::class, $mock);

        $this->artisan('judgearena:import-user-rating-history', ['platform' => 'atcoder'])
            ->expectsOutputToContain('Platform: atcoder')
            ->expectsOutputToContain('Created: 1')
            ->assertExitCode(0);

        $this->assertDatabaseHas('contest_rating_changes', [
            'platform_id' => $platform->id,
            'platform_profile_id' => $profile->id,
            'contest_id' => $contest->id,
            'handle' => 'chokudai',
            'rank' => 1,
            'old_rating' => 2950,
            'new_rating' => 3000,
        ]);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'user_rating_history',
            'entity_platform_id' => 'chokudai',
            'sync_status' => 'synced',
        ]);
    }
}
