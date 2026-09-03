<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\AtCoder;

use App\Core\DTOs\ContestDTO;
use App\Platforms\AtCoder\AtCoderAdapter;
use DateTimeImmutable;
use Tests\TestCase;

class ImportContestsTest extends TestCase
{
    public function test_import_atcoder_contests_command_persists_contests_with_rated_metadata_and_sync_states(): void
    {
        $platform = $this->createPlatform('atcoder', 'AtCoder', 'https://atcoder.jp');

        $adapter = $this->app->make(AtCoderAdapter::class);
        $mock = \Mockery::mock($adapter)->makePartial();
        $mock->shouldReceive('getContests')
            ->once()
            ->andReturn([
                new ContestDTO(
                    platform: 'atcoder',
                    platformContestId: 'abc350',
                    title: 'AtCoder Beginner Contest 350',
                    slug: 'abc350-atcoder-beginner-contest-350',
                    type: 'ABC',
                    phase: 'FINISHED',
                    startedAt: new DateTimeImmutable('2024-04-20 21:00:00+0900'),
                    durationSeconds: 6000,
                    endedAt: new DateTimeImmutable('2024-04-20 22:40:00+0900'),
                    url: 'https://atcoder.jp/contests/abc350',
                    raw: [
                        'id' => 'abc350',
                        'rate_change' => '~ 1999',
                        'rate_change_spec' => [
                            'is_rated' => true,
                            'min_rating' => 0,
                            'max_rating' => 1999,
                            'label' => 'Rated for ≤ 1999',
                            'raw' => '~ 1999',
                        ],
                    ],
                ),
            ]);

        $this->app->instance(AtCoderAdapter::class, $mock);

        $this->artisan('judgearena:import-contests', ['platform' => 'atcoder'])
            ->expectsOutputToContain('Platform: atcoder')
            ->expectsOutputToContain('Created: 1')
            ->assertExitCode(0);

        $this->assertDatabaseHas('contests', [
            'platform_id' => $platform->id,
            'platform_contest_id' => 'abc350',
            'name' => 'AtCoder Beginner Contest 350',
            'phase' => 'FINISHED',
            'is_rated' => true,
            'duration_seconds' => 6000,
        ]);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'contest',
            'entity_platform_id' => 'abc350',
            'sync_status' => 'synced',
        ]);
    }
}
