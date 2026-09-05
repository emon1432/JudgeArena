<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\PlatformSyncEntityType;
use App\Enums\PlatformSyncStatus;
use App\Livewire\Admin\SyncMonitor;
use App\Models\Platform;
use App\Models\PlatformSyncState;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class SyncMonitorLivewireTest extends TestCase
{
    public function test_sync_monitor_component_renders_and_polls_correctly(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces', 'https://codeforces.com');

        PlatformSyncState::query()->create([
            'platform_id' => $platform->id,
            'entity_type' => PlatformSyncEntityType::Contest->value,
            'entity_platform_id' => '1001',
            'sync_status' => PlatformSyncStatus::Synced->value,
        ]);

        PlatformSyncState::query()->create([
            'platform_id' => $platform->id,
            'entity_type' => PlatformSyncEntityType::ContestProblems->value,
            'entity_platform_id' => '1002',
            'sync_status' => PlatformSyncStatus::Failed->value,
            'last_error' => 'Connection timeout',
        ]);

        Livewire::test(SyncMonitor::class)
            ->assertSee('Sync Monitor')
            ->assertSee('Total Sync States')
            ->assertSee('Codeforces')
            ->assertSee('Connection timeout');
    }

    public function test_sync_monitor_retry_action_resets_failed_state(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces', 'https://codeforces.com');

        $failedState = PlatformSyncState::query()->create([
            'platform_id' => $platform->id,
            'entity_type' => PlatformSyncEntityType::Contest->value,
            'entity_platform_id' => '2001',
            'sync_status' => PlatformSyncStatus::Failed->value,
            'last_error' => 'API rate limit',
        ]);

        Livewire::test(SyncMonitor::class)
            ->call('retry', $failedState->id)
            ->assertSee('has been reset for retry');

        $this->assertSame(
            PlatformSyncStatus::Pending->value,
            $failedState->fresh()->sync_status->value
        );
    }

    public function test_sync_monitor_pagination_works_reactively(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces', 'https://codeforces.com');

        for ($i = 1; $i <= 15; $i++) {
            PlatformSyncState::query()->create([
                'platform_id' => $platform->id,
                'entity_type' => PlatformSyncEntityType::Contest->value,
                'entity_platform_id' => 'contest_' . $i,
                'sync_status' => PlatformSyncStatus::Synced->value,
            ]);
        }

        Livewire::test(SyncMonitor::class)
            ->call('gotoPage', 2, 'activity_page')
            ->assertSet('paginators.activity_page', 2)
            ->assertSee('contest_');
    }
}
