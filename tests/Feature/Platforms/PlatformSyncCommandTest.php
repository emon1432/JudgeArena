<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms;

use App\Enums\PlatformSyncJobEntity;
use App\Models\Platform;
use App\Models\PlatformSyncJob;
use Tests\TestCase;

class PlatformSyncCommandTest extends TestCase
{
    public function test_sync_command_reports_no_jobs_when_empty(): void
    {
        $this->artisan('judgearena:sync')
            ->expectsOutputToContain('No synchronization jobs are due.')
            ->assertExitCode(0);
    }

    public function test_sync_command_executes_due_jobs(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces', 'https://codeforces.com');

        $job = PlatformSyncJob::query()->create([
            'platform_id' => $platform->id,
            'entity' => PlatformSyncJobEntity::Contest,
            'enabled' => true,
            'priority' => 100,
            'interval_minutes' => 15,
            'last_success_at' => null,
        ]);

        $this->artisan('judgearena:sync')
            ->expectsOutputToContain('Found 1 synchronization job(s).')
            ->assertExitCode(0);

        $job->refresh();
        $this->assertNotNull($job->last_started_at);
    }
}
