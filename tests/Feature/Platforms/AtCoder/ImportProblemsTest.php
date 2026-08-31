<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\AtCoder;

use App\Core\DTOs\ProblemDTO;
use App\Models\Contest;
use App\Platforms\AtCoder\AtCoderAdapter;
use Tests\TestCase;

class ImportProblemsTest extends TestCase
{
    public function test_import_atcoder_problems_command_persists_problems_and_sync_states(): void
    {
        $platform = $this->createPlatform('atcoder', 'AtCoder', 'https://atcoder.jp');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => 'abc350',
            'name' => 'AtCoder Beginner Contest 350',
            'slug' => 'abc350-atcoder-beginner-contest-350',
            'phase' => 'FINISHED',
        ]);

        $adapter = $this->app->make(AtCoderAdapter::class);
        $mock = \Mockery::mock($adapter)->makePartial();
        $mock->shouldReceive('getContestProblems')
            ->once()
            ->with('abc350')
            ->andReturn([
                new ProblemDTO(
                    platform: 'atcoder',
                    platformProblemId: 'abc350_a',
                    title: 'Past ABCs',
                    contestPlatformId: 'abc350',
                    code: 'A',
                    points: 100.0,
                    timeLimit: 2000,
                    memoryLimit: 1024,
                    raw: ['id' => 'abc350_a'],
                ),
            ]);

        $this->app->instance(AtCoderAdapter::class, $mock);

        $this->artisan('judgearena:import-problems', ['platform' => 'atcoder'])
            ->expectsOutputToContain('Platform: atcoder')
            ->expectsOutputToContain('Created: 1')
            ->assertExitCode(0);

        $this->assertDatabaseHas('problems', [
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_problem_id' => 'abc350_a',
            'name' => 'Past ABCs',
            'code' => 'A',
            'points' => 100.0,
        ]);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'contest_problems',
            'entity_platform_id' => 'abc350',
            'sync_status' => 'synced',
        ]);
    }
}
