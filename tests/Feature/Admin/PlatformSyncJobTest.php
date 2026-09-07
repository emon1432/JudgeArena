<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\PlatformSyncEntityType;
use App\Models\PlatformSyncJob;
use App\Models\User;
use Tests\TestCase;

class PlatformSyncJobTest extends TestCase
{
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::query()->create([
            'name' => 'Admin User',
            'username' => 'admin_test',
            'email' => 'admin_test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_view_platform_sync_jobs_index(): void
    {
        $platform = $this->createPlatform('atcoder', 'AtCoder', 'https://atcoder.jp');

        PlatformSyncJob::query()->create([
            'platform_id' => $platform->id,
            'entity' => PlatformSyncEntityType::Contest->value,
            'priority' => 100,
            'interval_minutes' => 15,
            'enabled' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.platform-sync-jobs.index'));

        $response->assertOk();
        $response->assertSee('Platform Sync Jobs');
    }

    public function test_admin_can_fetch_platform_sync_jobs_datatable_json(): void
    {
        $platform = $this->createPlatform('atcoder', 'AtCoder', 'https://atcoder.jp');

        $job = PlatformSyncJob::query()->create([
            'platform_id' => $platform->id,
            'entity' => PlatformSyncEntityType::Contest->value,
            'priority' => 100,
            'interval_minutes' => 15,
            'enabled' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.platform-sync-jobs.index', ['length' => 12]), [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'draw',
            'recordsTotal',
            'recordsFiltered',
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertStringContainsString('dt-status-toggle', $data[0]['enabled_status']);
        $this->assertStringContainsString('checked', $data[0]['enabled_status']);
    }

    public function test_admin_can_toggle_platform_sync_job_status(): void
    {
        $platform = $this->createPlatform('atcoder', 'AtCoder', 'https://atcoder.jp');

        $job = PlatformSyncJob::query()->create([
            'platform_id' => $platform->id,
            'entity' => PlatformSyncEntityType::Contest->value,
            'priority' => 100,
            'interval_minutes' => 15,
            'enabled' => true,
        ]);

        $this->assertTrue($job->enabled);

        // Toggle from enabled (true) to disabled (false)
        $response = $this->actingAs($this->adminUser)
            ->patchJson(route('admin.platform-sync-jobs.toggle-status', $job->id), [
                'enabled' => false,
            ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 200,
            'enabled' => false,
        ]);
        $this->assertFalse($job->fresh()->enabled);

        // Toggle back from disabled (false) to enabled (true)
        $response = $this->actingAs($this->adminUser)
            ->patchJson(route('admin.platform-sync-jobs.toggle-status', $job->id), [
                'enabled' => true,
            ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 200,
            'enabled' => true,
        ]);
        $this->assertTrue($job->fresh()->enabled);
    }
}

