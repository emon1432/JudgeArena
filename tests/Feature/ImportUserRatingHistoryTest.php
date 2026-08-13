<?php

namespace Tests\Feature;

use App\Core\DTOs\RatingChangeDTO;
use App\Enums\PlatformSyncStatus;
use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\PlatformSyncState;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Platforms\Codeforces\Importers\UserRatingHistoryImporter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Tests\TestCase;

class ImportUserRatingHistoryTest extends TestCase
{
    public function test_user_rating_history_import_claims_and_syncs_user_rating_changes(): void
    {
        $platform = new class extends Platform {
            public $id = 1;
            public $slug = 'codeforces';
            public $name = 'Codeforces';
        };

        $contest = new class extends Contest {
            public $id = 10;
            public $platform_id = 1;
            public $platform_contest_id = '1840';
            public $name = 'Codeforces Round 1840';
        };

        $profile = new class extends PlatformProfile {
            public $id = 100;
            public $platform_id = 1;
            public $handle = 'tourist';
        };

        $contestRatingChangeModel = new class extends ContestRatingChange {
            public array $capturedLookup = [];
            public array $capturedValues = [];

            public function newQuery()
            {
                $self = $this;

                return new class($self) {
                    public function __construct(private readonly ContestRatingChange $change) {}

                    public function updateOrCreate(array $lookup, array $values): ContestRatingChange
                    {
                        $this->change->capturedLookup = $lookup;
                        $this->change->capturedValues = $values;

                        $instance = new ContestRatingChange();
                        $instance->id = 500;
                        $instance->wasRecentlyCreated = true;

                        return $instance;
                    }
                };
            }
        };

        $platformQuery = new class($platform) {
            public function __construct(private readonly Platform $platform) {}
            public function where($column, $value) { return $this; }
            public function first() { return $this->platform; }
        };

        $profileQuery = new class($profile) {
            public function __construct(private readonly PlatformProfile $profile) {}
            public function where($column, $value) { return $this; }
            public function active() { return $this; }
            public function whereRaw($sql, $bindings) { return $this; }
            public function get() { return collect([$this->profile]); }
        };

        $contestQuery = new class($contest) {
            public function __construct(private readonly Contest $contest) {}
            public function where($column, $value) { return $this; }
            public function whereNotNull($column) { return $this; }
            public function get() { return collect([$this->contest]); }
            public function first() { return $this->contest; }
        };

        $ratingChangeDto = new RatingChangeDTO(
            platform: 'codeforces',
            contestPlatformId: '1840',
            handle: 'tourist',
            isRated: true,
            rank: 1,
            oldRating: 3500,
            newRating: 3600,
            ratingChange: 100,
            performance: 3800,
            metadata: ['contest_name' => 'Codeforces Round 1840'],
            raw: ['contestId' => 1840]
        );

        $this->mock(CodeforcesAdapter::class, function ($mock) use ($ratingChangeDto): void {
            $mock->shouldReceive('getUserRatingHistory')
                ->once()
                ->with('tourist')
                ->andReturn([$ratingChangeDto]);
        });

        $this->app->instance(ApplicationLogger::class, new class extends ApplicationLogger {
            public function info(string $message, array $context = [], ?\Throwable $exception = null): void {}
            public function warning(string $message, array $context = [], ?\Throwable $exception = null): void {}
            public function error(string $message, array $context = [], ?\Throwable $exception = null): void {}
            public function critical(string $message, array $context = [], ?\Throwable $exception = null): void {}
        });

        $fakeState = new PlatformSyncState();
        $fakeState->sync_status = PlatformSyncStatus::Pending->value;

        $this->mock(PlatformSyncStateService::class, function ($mock) use ($fakeState): void {
            $mock->shouldReceive('isSynced')->once()->andReturn(false);
            $mock->shouldReceive('markSyncing')->once()->andReturn($fakeState);
            $mock->shouldReceive('markSynced')->once()->andReturn($fakeState);
            $mock->shouldReceive('markFailed')->never();
        });

        $this->mock(Platform::class, function ($mock) use ($platformQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($platformQuery);
        });

        $this->mock(PlatformProfile::class, function ($mock) use ($profileQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($profileQuery);
        });

        $this->mock(Contest::class, function ($mock) use ($contestQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($contestQuery);
        });

        $this->mock(ContestRatingChange::class, function ($mock) use ($contestRatingChangeModel): void {
            $mock->shouldReceive('newQuery')->andReturn($contestRatingChangeModel->newQuery());
        });

        $stats = app(UserRatingHistoryImporter::class)->import();

        $this->assertSame(1, $stats->checked);
        $this->assertSame(1, $stats->fetched);
        $this->assertSame(1, $stats->created);
        $this->assertSame(0, $stats->updated);
        $this->assertSame(0, $stats->failed);
    }
}
