<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\PlatformSyncEntityType;
use App\Enums\PlatformSyncJobEntity;
use App\Enums\PlatformSyncStatus;
use App\Livewire\Admin\SyncMonitor;
use App\Models\PlatformSyncJob;
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

    public function test_admin_can_retry_all_failed_sync_states(): void
    {
        $admin = $this->createAdminUser();
        $platform = $this->createPlatform('codeforces', 'Codeforces');

        PlatformSyncState::query()->create([
            'platform_id' => $platform->id,
            'entity_type' => PlatformSyncEntityType::Contest->value,
            'entity_platform_id' => '1001',
            'sync_status' => PlatformSyncStatus::Failed->value,
            'last_error' => 'API rate limit exceeded',
        ]);

        PlatformSyncState::query()->create([
            'platform_id' => $platform->id,
            'entity_type' => PlatformSyncEntityType::Contest->value,
            'entity_platform_id' => '1002',
            'sync_status' => PlatformSyncStatus::Failed->value,
            'last_error' => 'Connection timeout',
        ]);

        $this->assertSame(2, PlatformSyncState::query()->where('sync_status', PlatformSyncStatus::Failed->value)->count());

        Livewire::actingAs($admin)
            ->test(SyncMonitor::class)
            ->call('retryAllFailures')
            ->assertSet('feedbackType', 'success');

        $this->assertSame(0, PlatformSyncState::query()->where('sync_status', PlatformSyncStatus::Failed->value)->count());
        $this->assertSame(2, PlatformSyncState::query()->where('sync_status', PlatformSyncStatus::Pending->value)->count());
    }

    public function test_platform_breakdown_displays_status_column_and_job_schedule(): void
    {
        $admin = $this->createAdminUser();
        $platform = $this->createPlatform('codeforces', 'Codeforces');

        PlatformSyncJob::query()->create([
            'platform_id' => $platform->id,
            'entity' => PlatformSyncJobEntity::Problem,
            'enabled' => true,
            'priority' => 1,
            'interval_minutes' => 15,
            'last_success_at' => now()->subMinutes(5),
        ]);

        PlatformSyncJob::query()->create([
            'platform_id' => $platform->id,
            'entity' => PlatformSyncJobEntity::Contest,
            'enabled' => false,
            'priority' => 2,
            'interval_minutes' => 60,
        ]);

        PlatformSyncState::query()->create([
            'platform_id' => $platform->id,
            'entity_type' => PlatformSyncEntityType::Contest->value,
            'entity_platform_id' => '1000',
            'sync_status' => PlatformSyncStatus::Synced->value,
        ]);

        Livewire::actingAs($admin)
            ->test(SyncMonitor::class)
            ->assertSee('Platform Breakdown')
            ->assertSee('Disabled')
            ->assertSee('countdown-badge')
            ->assertSee('m ');
    }

    public function test_admin_can_switch_tabs_and_view_failures_and_activity(): void
    {
        $admin = $this->createAdminUser();
        $platform = $this->createPlatform('atcoder', 'AtCoder');

        PlatformSyncState::query()->create([
            'platform_id' => $platform->id,
            'entity_type' => PlatformSyncEntityType::Contest->value,
            'entity_platform_id' => 'abc350',
            'sync_status' => PlatformSyncStatus::Failed->value,
            'last_error' => 'Custom parsing error on line 42',
        ]);

        Livewire::actingAs($admin)
            ->test(SyncMonitor::class)
            ->assertSee('Drive Storage')
            ->assertSee('Platform Breakdown')
            ->call('switchTab', 'failures')
            ->assertSet('activeTab', 'failures')
            ->assertSee('Custom parsing error on line 42')
            ->call('switchTab', 'activity')
            ->assertSet('activeTab', 'activity')
            ->assertSee('Live Activity Stream');
    }

    public function test_sync_monitor_displays_drive_storage_status(): void
    {
        $admin = $this->createAdminUser();

        Livewire::actingAs($admin)
            ->test(SyncMonitor::class)
            ->assertSee('Drive Storage')
            ->assertSee('Local')
            ->assertSee('Live');
    }
}
