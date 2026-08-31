<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\Codeforces;

use App\Core\DTOs\ContestDTO;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ProblemDTO;
use App\Models\Contest;
use App\Platforms\Codeforces\CodeforcesAdapter;
use Tests\TestCase;

class ImportProblemsTest extends TestCase
{
    public function test_import_codeforces_problems_command_persists_problems_and_sync_states(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces', 'https://codeforces.com');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '2000',
            'name' => 'Codeforces Round 950 (Div. 2)',
            'slug' => '2000-codeforces-round-950-div-2',
            'phase' => 'FINISHED',
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
                    problems: [
                        new ProblemDTO(
                            platform: 'codeforces',
                            platformProblemId: '2000A',
                            title: 'Problem A',
                            contestPlatformId: '2000',
                            code: 'A',
                            points: 500.0,
                            rating: 800,
                            tags: ['math', 'greedy'],
                            raw: ['index' => 'A'],
                        ),
                        new ProblemDTO(
                            platform: 'codeforces',
                            platformProblemId: '2000B',
                            title: 'Problem B',
                            contestPlatformId: '2000',
                            code: 'B',
                            points: 1000.0,
                            rating: 1200,
                            tags: ['dp'],
                            raw: ['index' => 'B'],
                        ),
                    ],
                    rows: [],
                    raw: ['contestId' => 2000],
                )
            );

        $this->app->instance(CodeforcesAdapter::class, $mock);

        $this->artisan('judgearena:import-problems', ['platform' => 'codeforces'])
            ->expectsOutputToContain('Platform: codeforces')
            ->expectsOutputToContain('Created: 2')
            ->assertExitCode(0);

        $this->assertDatabaseHas('problems', [
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_problem_id' => '2000A',
            'name' => 'Problem A',
            'code' => 'A',
            'points' => 500,
            'rating' => 800,
        ]);

        $this->assertDatabaseHas('problems', [
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_problem_id' => '2000B',
            'name' => 'Problem B',
            'code' => 'B',
            'points' => 1000,
            'rating' => 1200,
        ]);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'contest_problems',
            'entity_platform_id' => '2000',
            'sync_status' => 'synced',
        ]);
    }
}
