<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\PlatformSyncEntityType;
use App\Enums\PlatformSyncStatus;
use App\Livewire\Admin\SyncMonitor;
use App\Models\PlatformSyncState;
use Livewire\Livewire;
use Tests\TestCase;

class SyncMonitorTest extends TestCase
{
    public function test_admin_can_view_sync_monitor_page(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)
            ->get(route('admin.sync-monitor.index'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(SyncMonitor::class);
    }

    public function test_admin_can_reset_stuck_syncing_states(): void
    {
        $admin = $this->createAdminUser();
        $platform = $this->createPlatform('atcoder', 'AtCoder');

        PlatformSyncState::query()->create([
            'platform_id' => $platform->id,
            'entity_type' => PlatformSyncEntityType::ContestProblems->value,
            'entity_platform_id' => 'abc300',
            'sync_status' => PlatformSyncStatus::Syncing->value,
            'last_attempted_at' => now()->subMinutes(30),
        ]);

        PlatformSyncState::query()->create([
            'platform_id' => $platform->id,
            'entity_type' => PlatformSyncEntityType::ContestProblems->value,
            'entity_platform_id' => 'abc301',
            'sync_status' => PlatformSyncStatus::Syncing->value,
            'last_attempted_at' => now()->subMinutes(35),
        ]);

        $this->assertSame(2, PlatformSyncState::query()->where('sync_status', PlatformSyncStatus::Syncing->value)->count());

        Livewire::actingAs($admin)
            ->test(SyncMonitor::class)
            ->call('resetStuckSyncs')
            ->assertSet('feedbackType', 'success');

        $this->assertSame(0, PlatformSyncState::query()->where('sync_status', PlatformSyncStatus::Syncing->value)->count());
        $this->assertSame(2, PlatformSyncState::query()->where('sync_status', PlatformSyncStatus::Pending->value)->count());
    }

    public function test_admin_can_retry_specific_failed_or_syncing_state(): void
    {
        $admin = $this->createAdminUser();
        $platform = $this->createPlatform('atcoder', 'AtCoder');

        $state = PlatformSyncState::query()->create([
            'platform_id' => $platform->id,
            'entity_type' => PlatformSyncEntityType::ContestProblems->value,
            'entity_platform_id' => 'abc300',
            'sync_status' => PlatformSyncStatus::Syncing->value,
            'last_attempted_at' => now()->subMinutes(30),
        ]);

        Livewire::actingAs($admin)
            ->test(SyncMonitor::class)
            ->call('retry', $state->id)
            ->assertSet('feedbackType', 'success');

        $this->assertSame(PlatformSyncStatus::Pending, $state->fresh()->sync_status);
    }
}
