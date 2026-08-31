<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\Codeforces;

use App\Core\DTOs\SubmissionDTO;
use App\Enums\SubmissionVerdict;
use App\Models\Contest;
use App\Models\Problem;
use App\Platforms\Codeforces\CodeforcesAdapter;
use Tests\TestCase;

class ImportUserSubmissionsTest extends TestCase
{
    public function test_import_codeforces_user_submissions_command_persists_submissions(): void
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

        $adapter = $this->app->make(CodeforcesAdapter::class);
        $mock = \Mockery::mock($adapter)->makePartial();
        $mock->shouldReceive('getUserSubmissions')
            ->once()
            ->with([
                'handle' => 'tourist',
                'from' => 1,
                'count' => 100,
            ])
            ->andReturn([
                'submissions' => [
                    new SubmissionDTO(
                        platform: 'codeforces',
                        platformSubmissionId: '999001',
                        problemPlatformId: '2000A',
                        authorHandle: 'tourist',
                        verdict: SubmissionVerdict::AC,
                        language: 'GNU C++20',
                        passedTestCount: 30,
                        timeConsumedMillis: 45,
                        createdAtSeconds: 1717256000,
                        contestPlatformId: '2000',
                        raw: ['id' => 999001],
                    ),
                ],
                'reached_stop' => true,
            ]);

        $this->app->instance(CodeforcesAdapter::class, $mock);

        $this->artisan('judgearena:import-user-submissions', ['platform' => 'codeforces'])
            ->expectsOutputToContain('Platform: codeforces')
            ->expectsOutputToContain('Created: 1')
            ->assertExitCode(0);

        $this->assertDatabaseHas('submissions', [
            'platform_id' => $platform->id,
            'platform_profile_id' => $profile->id,
            'contest_id' => $contest->id,
            'problem_id' => $problem->id,
            'platform_submission_id' => '999001',
            'verdict' => 'AC',
            'language' => 'GNU C++20',
        ]);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'user_submissions',
            'entity_platform_id' => 'tourist',
            'sync_status' => 'synced',
        ]);
    }
}
