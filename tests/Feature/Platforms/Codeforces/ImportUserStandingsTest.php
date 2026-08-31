<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\Codeforces;

use App\Core\DTOs\ContestDTO;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ParticipantDTO;
use App\Core\DTOs\ProblemResultDTO;
use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Models\Problem;
use App\Platforms\Codeforces\CodeforcesAdapter;
use Tests\TestCase;

class ImportUserStandingsTest extends TestCase
{
    public function test_import_codeforces_user_standings_command_persists_standings(): void
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

        $problem = Problem::query()->create([
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_problem_id' => '2000A',
            'name' => 'Problem A',
            'index' => 'A',
        ]);

        ContestRatingChange::query()->create([
            'platform_id' => $platform->id,
            'platform_profile_id' => $profile->id,
            'contest_id' => $contest->id,
            'handle' => 'tourist',
            'rank' => 1,
            'old_rating' => 3700,
            'new_rating' => 3750,
        ]);

        $adapter = $this->app->make(CodeforcesAdapter::class);
        $mock = \Mockery::mock($adapter)->makePartial();
        $mock->shouldReceive('getUserStandings')
            ->once()
            ->with('2000')
            ->andReturn(
                new ContestStandingsDTO(
                    contest: new ContestDTO(
                        platform: 'codeforces',
                        platformContestId: '2000',
                        title: 'Codeforces Round 950 (Div. 2)',
                    ),
                    problems: [],
                    rows: [
                        new ParticipantDTO(
                            rank: 1,
                            points: 500,
                            penalty: 0,
                            members: [
                                ['handle' => 'tourist'],
                            ],
                            problemResults: [
                                new ProblemResultDTO(
                                    points: 500.0,
                                    rejectedAttemptCount: 0,
                                    bestSubmissionTimeSeconds: 300,
                                ),
                            ],
                            raw: ['party' => ['members' => [['handle' => 'tourist']]]],
                        ),
                    ],
                    raw: ['contestId' => 2000],
                )
            );

        $this->app->instance(CodeforcesAdapter::class, $mock);

        $this->artisan('judgearena:import-user-standings', ['platform' => 'codeforces'])
            ->expectsOutputToContain('Platform: codeforces')
            ->expectsOutputToContain('Created: 1')
            ->assertExitCode(0);

        $this->assertDatabaseHas('standings', [
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_profile_id' => $profile->id,
            'rank' => 1,
            'points' => 500.0,
        ]);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'user_standings',
            'entity_platform_id' => 'tourist',
            'sync_status' => 'synced',
        ]);
    }
}
