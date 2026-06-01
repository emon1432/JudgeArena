<?php

namespace Tests\Feature;

use App\Core\DTOs\UserDTO;
use App\Enums\PlatformSyncStatus;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\PlatformSyncState;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportUsersCommandTest extends TestCase
{
    public function test_user_import_syncs_platform_profiles_and_updates_only_raw_metadata_and_last_synced_at(): void
    {
        $platform = new class extends Platform {
            public $id = 1;
            public $slug = 'codeforces';
            public $name = 'Codeforces';
        };

        $profile = new class extends PlatformProfile {
            public array $capturedAttributes = [];

            public function forceFill(array $attributes)
            {
                $this->capturedAttributes = $attributes;

                return $this;
            }

            public function save(array $options = [])
            {
                return true;
            }

            public function refresh()
            {
                return $this;
            }
        };

        $profile->id = 10;
        $profile->user_id = 2;
        $profile->platform_id = 1;
        $profile->handle = 'tourist';
        $profile->platform = $platform;
        $profile->status = 'Active';

        $queryFake = new class($profile) {
            private PlatformProfile $profile;

            public function __construct(PlatformProfile $profile)
            {
                $this->profile = $profile;
            }

            public function active()
            {
                return $this;
            }

            public function with($relations)
            {
                return $this;
            }

            public function whereHas($relation, $callback)
            {
                return $this;
            }

            public function get()
            {
                return collect([$this->profile]);
            }
        };

        $this->mock(PlatformProfile::class, function ($mock) use ($queryFake): void {
            $mock->shouldReceive('newQuery')->andReturn($queryFake);
        });

        $user = new UserDTO(
            platform: 'codeforces',
            platformHandle: 'tourist',
            firstName: 'Petr',
            lastName: 'Mitrichev',
            rating: 3797,
            country: 'Russia',
            raw: ['handle' => 'tourist', 'rating' => 3797]
        );

        $this->mock(CodeforcesAdapter::class, function ($mock) use ($user): void {
            $mock->shouldReceive('getUser')
                ->once()
                ->with('tourist')
                ->andReturn($user);
        });

        $this->mock(AtCoderAdapter::class, function ($mock): void {
            $mock->shouldNotReceive('getUser');
        });

        $this->app->instance(ApplicationLogger::class, new class {
            public function info() {}
            public function warning() {}
            public function error() {}
            public function critical() {}
        });

        $fakeState = new PlatformSyncState();
        $fakeState->sync_status = PlatformSyncStatus::Pending->value;
        $fakeState->last_attempted_at = null;
        $fakeState->last_synced_at = null;

        $this->mock(PlatformSyncStateService::class, function ($mock) use ($fakeState): void {
            $mock->shouldReceive('findState')->andReturn(null);
            $mock->shouldReceive('canBeRetried')->andReturn(true);
            $mock->shouldReceive('isSynced')->andReturn(false);
            $mock->shouldReceive('markSyncing')->andReturn($fakeState);
            $mock->shouldReceive('markSynced')->andReturnUsing(function ($state, $metadata = []) use ($fakeState) {
                $fakeState->sync_status = PlatformSyncStatus::Synced->value;
                $fakeState->last_synced_at = now();
                $fakeState->last_attempted_at = now();

                return $fakeState;
            });
            $mock->shouldReceive('markFailed')->never();
        });

        $exitCode = Artisan::call('judgearena:import-users', [
            'platform' => 'codeforces',
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode, "Import command failed with output:\n" . $output);
        $this->assertStringContainsString('Platform: codeforces', $output);
        $this->assertStringContainsString('Profiles Checked: 1', $output);
        $this->assertStringContainsString('Profiles Synced: 1', $output);

        $this->assertSame($user->raw, $profile->capturedAttributes['raw'] ?? null);
        $this->assertSame('user-sync', $profile->capturedAttributes['metadata']['source'] ?? null);
        $this->assertSame('codeforces', $profile->capturedAttributes['metadata']['platform'] ?? null);
        $this->assertArrayHasKey('synced_at', $profile->capturedAttributes['metadata'] ?? []);
        $this->assertNotNull($profile->capturedAttributes['last_synced_at'] ?? null);
        $this->assertArrayNotHasKey('rating', $profile->capturedAttributes);
        $this->assertArrayNotHasKey('country', $profile->capturedAttributes);
        $this->assertArrayNotHasKey('avatar', $profile->capturedAttributes);
        $this->assertArrayNotHasKey('rank', $profile->capturedAttributes);
        $this->assertArrayNotHasKey('organization', $profile->capturedAttributes);
    }
}
