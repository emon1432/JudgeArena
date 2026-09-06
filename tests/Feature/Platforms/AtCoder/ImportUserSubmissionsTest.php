<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\AtCoder;

use App\Core\DTOs\SubmissionDTO;
use App\Enums\SubmissionVerdict;
use App\Models\Contest;
use App\Models\Problem;
use App\Platforms\AtCoder\AtCoderAdapter;
use Tests\TestCase;

class ImportUserSubmissionsTest extends TestCase
{
    public function test_import_atcoder_user_submissions_command_persists_submissions(): void
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

        $adapter = $this->app->make(AtCoderAdapter::class);
        $mock = \Mockery::mock($adapter)->makePartial();
        $mock->shouldReceive('getUserSubmissions')
            ->once()
            ->with([
                'handle' => 'chokudai',
                'from_second' => 0,
            ])
            ->andReturn([
                'submissions' => [
                    new SubmissionDTO(
                        platform: 'atcoder',
                        platformSubmissionId: '555001',
                        problemPlatformId: 'abc350_a',
                        authorHandle: 'chokudai',
                        verdict: SubmissionVerdict::AC,
                        language: 'C++ 20 (gcc 12.2)',
                        passedTestCount: 25,
                        timeConsumedMillis: 15,
                        createdAtSeconds: 1713615000,
                        contestPlatformId: 'abc350',
                        raw: ['id' => '555001'],
                    ),
                ],
                'reached_stop' => true,
            ]);

        $this->app->instance(AtCoderAdapter::class, $mock);

        $this->artisan('judgearena:import-user-submissions', ['platform' => 'atcoder'])
            ->expectsOutputToContain('Platform: atcoder')
            ->expectsOutputToContain('Created: 1')
            ->assertExitCode(0);

        $this->assertDatabaseHas('submissions', [
            'platform_id' => $platform->id,
            'platform_profile_id' => $profile->id,
            'contest_id' => $contest->id,
            'problem_id' => $problem->id,
            'platform_submission_id' => '555001',
            'verdict' => 'AC',
            'language' => 'C++ 20 (gcc 12.2)',
        ]);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'user_submissions',
            'entity_platform_id' => 'chokudai',
            'sync_status' => 'synced',
        ]);
    }
}
