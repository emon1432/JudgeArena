<?php

namespace Tests\Feature;

use App\Core\DTOs\RatingChangeDTO;
use App\Enums\PlatformSyncStatus;
use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\PlatformSyncState;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\AtCoder\Importers\RatingChangeImporter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Tests\TestCase;

class ImportAtCoderRatingChangesTest extends TestCase
{
    public function test_atcoder_rating_change_import_claims_and_syncs_each_contest(): void
    {
        $platform = new class extends Platform {
            public $id = 1;
            public $slug = 'atcoder';
            public $name = 'AtCoder';
        };

        $contest = new class extends Contest {
            public $id = 20;
            public $platform_id = 1;
            public $platform_contest_id = 'abc123';
            public $name = 'ABC 123';
        };
        $contest->platform = $platform;

        $profile = new class extends PlatformProfile {
            public $id = 30;
            public $platform_id = 1;
            public $handle = 'tourist';
        };

        $change = new RatingChangeDTO(
            platform: 'atcoder',
            contestPlatformId: 'abc123',
            handle: 'tourist',
            isRated: true,
            rank: 1,
            oldRating: 3790,
            newRating: 3800,
            ratingChange: 10,
            performance: 4000,
            metadata: ['badge' => 'gold'],
            raw: ['handle' => 'tourist']
        );

        $platformQuery = new class($platform) {
            public function __construct(private readonly Platform $platform) {}
            public function where($column, $value) { return $this; }
            public function first() { return $this->platform; }
        };

        $contestQuery = new class($contest) {
            public function __construct(private readonly Contest $contest) {}
            public function with($relations) { return $this; }
            public function where($column, $value) { return $this; }
            public function whereNotNull($column) { return $this; }
            public function get() { return collect([$this->contest]); }
        };

        $profileQuery = new class($profile) {
            public function __construct(private readonly PlatformProfile $profile) {}
            public function where($column, $value) { return $this; }
            public function get() { return collect([$this->profile]); }
        };

        $contestRatingChangeQuery = new class {
            public array $lookup = [];
            public array $values = [];

            public function updateOrCreate(array $lookup, array $values): ContestRatingChange
            {
                $this->lookup = $lookup;
                $this->values = $values;

                $model = new ContestRatingChange();
                $model->wasRecentlyCreated = true;

                return $model;
            }
        };

        $this->mock(AtCoderAdapter::class, function ($mock) use ($change): void {
            $mock->shouldReceive('getRatingChanges')
                ->once()
                ->with('abc123')
                ->andReturn([$change]);
        });

        $this->mock(Platform::class, function ($mock) use ($platformQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($platformQuery);
        });

        $this->mock(Contest::class, function ($mock) use ($contestQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($contestQuery);
        });

        $this->mock(PlatformProfile::class, function ($mock) use ($profileQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($profileQuery);
        });

        $this->mock(ContestRatingChange::class, function ($mock) use ($contestRatingChangeQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($contestRatingChangeQuery);
        });

        $this->app->instance(ApplicationLogger::class, new class {
            public function info() {}
            public function warning() {}
            public function error() {}
            public function critical() {}
        });

        $fakeState = new PlatformSyncState();
        $fakeState->sync_status = PlatformSyncStatus::Pending->value;

        $this->mock(PlatformSyncStateService::class, function ($mock) use ($fakeState): void {
            $mock->shouldReceive('findState')->once()->andReturn(null);
            $mock->shouldReceive('canBeRetried')->once()->andReturn(true);
            $mock->shouldReceive('isSynced')->once()->andReturn(false);
            $mock->shouldReceive('markSyncing')->once()->andReturn($fakeState);
            $mock->shouldReceive('markSynced')->once()->andReturn($fakeState);
            $mock->shouldReceive('markFailed')->never();
        });

        $importer = app(RatingChangeImporter::class);

        $stats = $importer->import();

        $this->assertSame(1, $stats['contests_checked']);
        $this->assertSame(1, $stats['contests_synced']);
        $this->assertSame(0, $stats['contests_already_synced']);
        $this->assertSame(0, $stats['contests_failed']);
        $this->assertSame(1, $stats['rating_changes_fetched']);
        $this->assertSame(1, $stats['rating_changes_created']);
        $this->assertSame(0, $stats['rating_changes_updated']);
        $this->assertSame([
            'contest_id' => 20,
            'handle' => 'tourist',
        ], $contestRatingChangeQuery->lookup);
        $this->assertSame(1, $contestRatingChangeQuery->values['platform_id']);
        $this->assertSame(30, $contestRatingChangeQuery->values['platform_profile_id']);
        $this->assertSame(true, $contestRatingChangeQuery->values['is_rated']);
        $this->assertSame(10, $contestRatingChangeQuery->values['rating_change']);
        $this->assertSame('rating-change-import', $contestRatingChangeQuery->values['metadata']['source']);
        $this->assertSame('atcoder', $contestRatingChangeQuery->values['metadata']['platform']);
        $this->assertSame('abc123', $contestRatingChangeQuery->values['metadata']['contest_platform_id']);
        $this->assertSame('tourist', $contestRatingChangeQuery->values['metadata']['handle']);
    }
}
