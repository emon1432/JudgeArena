<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\Codeforces;

use App\Core\DTOs\ContestDTO;
use App\Models\Contest;
use App\Models\PlatformSyncState;
use App\Platforms\Codeforces\CodeforcesAdapter;
use DateTimeImmutable;
use Tests\TestCase;

class ImportContestsTest extends TestCase
{
    public function test_import_codeforces_contests_command_persists_contests_and_sync_states(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces', 'https://codeforces.com');

        $adapter = $this->app->make(CodeforcesAdapter::class);
        $mock = \Mockery::mock($adapter)->makePartial();
        $mock->shouldReceive('getContests')
            ->once()
            ->andReturn([
                new ContestDTO(
                    platform: 'codeforces',
                    platformContestId: '2000',
                    title: 'Codeforces Round 950 (Div. 2)',
                    slug: '2000-codeforces-round-950-div-2',
                    type: 'CF',
                    phase: 'FINISHED',
                    startedAt: new DateTimeImmutable('2024-06-01 14:35:00'),
                    durationSeconds: 7200,
                    endedAt: new DateTimeImmutable('2024-06-01 16:35:00'),
                    url: 'https://codeforces.com/contest/2000',
                    raw: ['id' => 2000],
                ),
                new ContestDTO(
                    platform: 'codeforces',
                    platformContestId: '2001',
                    title: 'Codeforces Round 951 (Div. 2)',
                    slug: '2001-codeforces-round-951-div-2',
                    type: 'CF',
                    phase: 'BEFORE',
                    startedAt: new DateTimeImmutable('2024-06-15 14:35:00'),
                    durationSeconds: 7200,
                    endedAt: new DateTimeImmutable('2024-06-15 16:35:00'),
                    url: 'https://codeforces.com/contest/2001',
                    raw: ['id' => 2001],
                ),
            ]);

        $this->app->instance(CodeforcesAdapter::class, $mock);

        $this->artisan('judgearena:import-contests', ['platform' => 'codeforces'])
            ->expectsOutputToContain('Platform: codeforces')
            ->expectsOutputToContain('Created: 2')
            ->assertExitCode(0);

        $this->assertDatabaseHas('contests', [
            'platform_id' => $platform->id,
            'platform_contest_id' => '2000',
            'name' => 'Codeforces Round 950 (Div. 2)',
            'phase' => 'FINISHED',
        ]);

        $this->assertDatabaseHas('contests', [
            'platform_id' => $platform->id,
            'platform_contest_id' => '2001',
            'name' => 'Codeforces Round 951 (Div. 2)',
            'phase' => 'BEFORE',
        ]);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'contest',
            'entity_platform_id' => '2000',
            'sync_status' => 'synced',
        ]);
    }
}
