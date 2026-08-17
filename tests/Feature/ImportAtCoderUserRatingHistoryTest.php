<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\DTOs\RatingChangeDTO;
use App\Enums\PlatformSyncStatus;
use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\PlatformSyncState;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\AtCoder\Importers\UserRatingHistoryImporter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Tests\TestCase;

class ImportAtCoderUserRatingHistoryTest extends TestCase
{
    public function test_atcoder_user_rating_history_import_saves_rating_changes_successfully(): void
    {
        $platform = new class extends Platform {
            public $id = 2;
            public $slug = 'atcoder';
            public $name = 'AtCoder';
        };

        $profile = new class extends PlatformProfile {
            public $id = 5;
            public $platform_id = 2;
            public $handle = 'tourist';
            public $status = 'Active';
        };

        $contest = new class extends Contest {
            public $id = 10;
            public $platform_id = 2;
            public $platform_contest_id = 'agc004';
            public $name = 'AtCoder Grand Contest 004';
        };

        $ratingChangeModel = new class extends ContestRatingChange {
            public array $capturedLookup = [];
            public array $capturedValues = [];

            public function newQuery()
            {
                $self = $this;

                return new class($self) {
                    public function __construct(private readonly ContestRatingChange $model) {}

                    public function updateOrCreate(array $lookup, array $values): ContestRatingChange
                    {
                        $this->model->capturedLookup = $lookup;
                        $this->model->capturedValues = $values;
                        $this->model->wasRecentlyCreated = true;

                        return $this->model;
                    }
                };
            }
        };

        $platformModel = new class($platform) extends Platform {
            public function __construct(private readonly Platform $platform) {}

            public function newQuery()
            {
                $p = $this->platform;

                return new class($p) {
                    public function __construct(private readonly Platform $p) {}

                    public function where(string $col, mixed $val): self
                    {
                        return $this;
                    }

                    public function first(): Platform
                    {
                        return $this->p;
                    }
                };
            }
        };

        $platformProfileModel = new class($profile) extends PlatformProfile {
            public function __construct(private readonly PlatformProfile $profile) {}

            public function newQuery()
            {
                $prof = $this->profile;

                return new class($prof) {
                    public function __construct(private readonly PlatformProfile $prof) {}

                    public function where(string $col, mixed $val): self
                    {
                        return $this;
                    }

                    public function active(): self
                    {
                        return $this;
                    }

                    public function whereRaw(string $sql, array $bindings = []): self
                    {
                        return $this;
                    }

                    public function get()
                    {
                        return collect([$this->prof]);
                    }
                };
            }
        };

        $contestModel = new class($contest) extends Contest {
            public function __construct(private readonly Contest $contest) {}

            public function newQuery()
            {
                $c = $this->contest;

                return new class($c) {
                    public function __construct(private readonly Contest $c) {}

                    public function where(string $col, mixed $val): self
                    {
                        return $this;
                    }

                    public function first(): ?Contest
                    {
                        return $this->c;
                    }
                };
            }
        };

        $dto = new RatingChangeDTO(
            platform: 'atcoder',
            contestPlatformId: 'agc004',
            handle: 'tourist',
            isRated: true,
            rank: 2,
            oldRating: 0,
            newRating: 2720,
            ratingChange: 2720,
            performance: 3920,
            metadata: ['contest_name' => 'AtCoder Grand Contest 004'],
            raw: ['IsRated' => true, 'Place' => 2, 'OldRating' => 0, 'NewRating' => 2720, 'Performance' => 3920]
        );

        $adapter = $this->mock(AtCoderAdapter::class, function ($mock) use ($dto): void {
            $mock->shouldReceive('getUserRatingHistory')->once()->with('tourist')->andReturn([$dto]);
        });

        $this->app->instance(ApplicationLogger::class, new class extends ApplicationLogger {
            public function info(string $message, array $context = [], ?\Throwable $exception = null): void {}
            public function warning(string $message, array $context = [], ?\Throwable $exception = null): void {}
            public function error(string $message, array $context = [], ?\Throwable $exception = null): void {}
        });

        $fakeState = new PlatformSyncState();
        $fakeState->sync_status = PlatformSyncStatus::Pending->value;

        $syncService = $this->mock(PlatformSyncStateService::class, function ($mock) use ($fakeState): void {
            $mock->shouldReceive('markSyncing')->once()->andReturn($fakeState);
            $mock->shouldReceive('markSynced')->once()->andReturn($fakeState);
            $mock->shouldReceive('markFailed')->never();
        });

        $importer = new UserRatingHistoryImporter(
            $contestModel,
            $ratingChangeModel,
            $platformModel,
            $platformProfileModel,
            $adapter,
            $syncService
        );

        $result = $importer->import('tourist');

        $this->assertSame(1, $result->checked);
        $this->assertSame(1, $result->fetched);
        $this->assertSame(1, $result->created);

        $this->assertSame(10, $ratingChangeModel->capturedLookup['contest_id']);
        $this->assertSame('tourist', $ratingChangeModel->capturedLookup['handle']);
        $this->assertSame(2720, $ratingChangeModel->capturedValues['new_rating']);
        $this->assertSame(2720, $ratingChangeModel->capturedValues['rating_change']);
        $this->assertSame(3920, $ratingChangeModel->capturedValues['performance']);
    }
}
