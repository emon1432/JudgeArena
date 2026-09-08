<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Platform;
use App\Models\PlatformSyncJob;
use Tests\TestCase;

class PlatformSyncJobTest extends TestCase
{
    public function test_non_admin_cannot_access_platform_sync_jobs(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces');
        $profile = $this->createUserWithProfile($platform, 'regular_user');

        $response = $this->actingAs($profile->user)
            ->get(route('admin.platform-sync-jobs.index'));

        $this->assertTrue($response->isForbidden() || $response->isRedirect());
    }

    public function test_admin_can_view_platform_sync_jobs_index(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)
            ->get(route('admin.platform-sync-jobs.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_query_platform_sync_jobs_datatable_ajax(): void
    {
        $admin = $this->createAdminUser();
        $platform = $this->createPlatform('codeforces', 'Codeforces');

        PlatformSyncJob::query()->create([
            'platform_id' => $platform->id,
            'entity' => \App\Enums\PlatformSyncJobEntity::Contest,
            'interval_minutes' => 60,
            'enabled' => true,
        ]);

        $response = $this->actingAs($admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('admin.platform-sync-jobs.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);
    }

    public function test_admin_can_toggle_platform_sync_job_status(): void
    {
        $admin = $this->createAdminUser();
        $platform = $this->createPlatform('codeforces', 'Codeforces');

        $job = PlatformSyncJob::query()->create([
            'platform_id' => $platform->id,
            'entity' => \App\Enums\PlatformSyncJobEntity::Contest,
            'interval_minutes' => 60,
            'enabled' => true,
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.platform-sync-jobs.toggle-status', $job->id), [
                'enabled' => false,
            ]);

        $response->assertStatus(200);
        $this->assertFalse($job->fresh()->enabled);

        // Toggle back
        $response = $this->actingAs($admin)
            ->patch(route('admin.platform-sync-jobs.toggle-status', $job->id), [
                'enabled' => true,
            ]);

        $response->assertStatus(200);
        $this->assertTrue($job->fresh()->enabled);
    }
}

