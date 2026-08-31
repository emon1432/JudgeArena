<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\AtCoder;

use App\Core\DTOs\ContestDTO;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ParticipantDTO;
use App\Core\DTOs\ProblemResultDTO;
use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Models\Problem;
use App\Platforms\AtCoder\AtCoderAdapter;
use Tests\TestCase;

class ImportUserStandingsTest extends TestCase
{
    public function test_import_atcoder_user_standings_command_persists_standings(): void
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

        $problem = Problem::query()->create([
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_problem_id' => 'abc350_a',
            'name' => 'Past ABCs',
            'code' => 'A',
        ]);

        ContestRatingChange::query()->create([
            'platform_id' => $platform->id,
            'platform_profile_id' => $profile->id,
            'contest_id' => $contest->id,
            'handle' => 'chokudai',
            'rank' => 1,
            'old_rating' => 2950,
            'new_rating' => 3000,
        ]);

        $adapter = $this->app->make(AtCoderAdapter::class);
        $mock = \Mockery::mock($adapter)->makePartial();
        $mock->shouldReceive('getUserStandings')
            ->once()
            ->with('abc350')
            ->andReturn(
                new ContestStandingsDTO(
                    contest: new ContestDTO(
                        platform: 'atcoder',
                        platformContestId: 'abc350',
                        title: 'AtCoder Beginner Contest 350',
                    ),
                    problems: [],
                    rows: [
                        new ParticipantDTO(
                            rank: 1,
                            points: 10000,
                            penalty: 0,
                            members: [
                                ['handle' => 'chokudai'],
                            ],
                            problemResults: [
                                new ProblemResultDTO(
                                    points: 10000.0,
                                    rejectedAttemptCount: 0,
                                    bestSubmissionTimeSeconds: 120,
                                ),
                            ],
                            raw: ['userScreenName' => 'chokudai'],
                        ),
                    ],
                    raw: ['contestId' => 'abc350'],
                )
            );

        $this->app->instance(AtCoderAdapter::class, $mock);

        $this->artisan('judgearena:import-user-standings', ['platform' => 'atcoder'])
            ->expectsOutputToContain('Platform: atcoder')
            ->expectsOutputToContain('Created: 1')
            ->assertExitCode(0);

        $this->assertDatabaseHas('standings', [
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_profile_id' => $profile->id,
            'rank' => 1,
            'points' => 100.0,
        ]);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'user_standings',
            'entity_platform_id' => 'chokudai',
            'sync_status' => 'synced',
        ]);
    }
}
