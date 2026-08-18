<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\DTOs\ContestDTO;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ParticipantDTO;
use App\Core\DTOs\ProblemDTO;
use App\Core\DTOs\ProblemResultDTO;
use App\Enums\PlatformSyncStatus;
use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\PlatformSyncState;
use App\Models\Problem;
use App\Models\Standing;
use App\Models\StandingTaskResult;
use App\Models\Submission;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\AtCoder\Importers\UserStandingsImporter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Tests\TestCase;

class ImportAtCoderStandingsTest extends TestCase
{
    public function test_atcoder_user_standings_import_saves_only_registered_users(): void
    {
        $platform = new class extends Platform {
            public $id = 2;
            public $slug = 'atcoder';
            public $name = 'AtCoder';
        };

        $registeredProfile = new class extends PlatformProfile {
            public $id = 5;
            public $platform_id = 2;
            public $handle = 'Baozii';
            public $status = 'Active';
        };

        $contest = new class extends Contest {
            public $id = 10;
            public $platform_id = 2;
            public $platform_contest_id = 'abc452';
            public $name = 'AtCoder Beginner Contest 452';
        };

        $problem = new class extends Problem {
            public $id = 100;
            public $platform_id = 2;
            public $platform_problem_id = 'abc452_a';
        };

        $standingModel = new class extends Standing {
            public array $capturedLookup = [];
            public array $capturedValues = [];

            public function newQuery()
            {
                $self = $this;

                return new class($self) {
                    public function __construct(private readonly Standing $model) {}

                    public function updateOrCreate(array $lookup, array $values): Standing
                    {
                        $this->model->capturedLookup = $lookup;
                        $this->model->capturedValues = $values;
                        $this->model->id = 1;
                        $this->model->wasRecentlyCreated = true;

                        return $this->model;
                    }
                };
            }
        };

        $standingTaskResultModel = new class extends StandingTaskResult {
            public array $capturedLookup = [];
            public array $capturedValues = [];

            public function newQuery()
            {
                $self = $this;

                return new class($self) {
                    public function __construct(private readonly StandingTaskResult $model) {}

                    public function updateOrCreate(array $lookup, array $values): StandingTaskResult
                    {
                        $this->model->capturedLookup = $lookup;
                        $this->model->capturedValues = $values;

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

        $platformProfileModel = new class($registeredProfile) extends PlatformProfile {
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

                    public function latest(string $col): self
                    {
                        return $this;
                    }

                    public function limit(int $n): self
                    {
                        return $this;
                    }

                    public function get()
                    {
                        return collect([$this->c]);
                    }
                };
            }
        };

        $problemModel = new class($problem) extends Problem {
            public function __construct(private readonly Problem $problem) {}

            public function newQuery()
            {
                $p = $this->problem;

                return new class($p) {
                    public function __construct(private readonly Problem $p) {}

                    public function where(string $col, mixed $val): self
                    {
                        return $this;
                    }

                    public function get()
                    {
                        return collect([$this->p]);
                    }
                };
            }
        };

        $contestRatingChangeModel = new class extends ContestRatingChange {
            public function newQuery()
            {
                return new class {
                    public function where(string $col, mixed $val): self { return $this; }
                    public function whereRaw(string $sql, array $bindings = []): self { return $this; }
                    public function distinct(): self { return $this; }
                    public function pluck(string $col) { return collect([10]); }
                };
            }
        };

        $submissionModel = new class extends Submission {
            public function newQuery()
            {
                return new class {
                    public function where(string $col, mixed $val): self { return $this; }
                    public function distinct(): self { return $this; }
                    public function pluck(string $col) { return collect([10]); }
                };
            }
        };

        $contestDto = new ContestDTO(
            platform: 'atcoder',
            platformContestId: 'abc452',
            title: 'AtCoder Beginner Contest 452'
        );

        $problemDto = new ProblemDTO(
            platform: 'atcoder',
            platformProblemId: 'abc452_a',
            title: 'Gothec'
        );

        // Row 1: Registered User "Baozii" (Score: 255000)
        $rowRegistered = new ParticipantDTO(
            rank: 1,
            points: 255000,
            penalty: 0,
            members: [['handle' => 'Baozii', 'name' => 'Baozii']],
            problemResults: [
                new ProblemResultDTO(
                    points: 10000,
                    penalty: 0,
                    rejectedAttemptCount: 1,
                    type: '1',
                    bestSubmissionTimeSeconds: 556
                ),
            ],
            raw: ['totalResult' => ['elapsed' => 1977000000000]]
        );

        // Row 2: Unregistered User "RandomGuest" (Should be filtered out)
        $rowUnregistered = new ParticipantDTO(
            rank: 2,
            points: 100000,
            penalty: 0,
            members: [['handle' => 'RandomGuest', 'name' => 'RandomGuest']],
            problemResults: [],
            raw: []
        );

        $standingsDto = new ContestStandingsDTO(
            contest: $contestDto,
            problems: [$problemDto],
            rows: [$rowRegistered, $rowUnregistered],
            raw: []
        );

        $adapter = $this->mock(AtCoderAdapter::class, function ($mock) use ($standingsDto): void {
            $mock->shouldReceive('getUserStandings')->once()->with('abc452')->andReturn($standingsDto);
        });

        $this->app->instance(ApplicationLogger::class, new class extends ApplicationLogger {
            public function info(string $message, array $context = [], ?\Throwable $exception = null): void {}
            public function warning(string $message, array $context = [], ?\Throwable $exception = null): void {}
            public function error(string $message, array $context = [], ?\Throwable $exception = null): void {}
        });

        $fakeState = new PlatformSyncState();
        $fakeState->sync_status = PlatformSyncStatus::Pending->value;

        $syncService = $this->mock(PlatformSyncStateService::class, function ($mock) use ($fakeState): void {
            $mock->shouldReceive('isSynced')->once()->andReturn(false);
            $mock->shouldReceive('markSyncing')->once()->andReturn($fakeState);
            $mock->shouldReceive('markSynced')->once()->andReturn($fakeState);
            $mock->shouldReceive('markFailed')->never();
        });

        $importer = new UserStandingsImporter(
            $standingModel,
            $standingTaskResultModel,
            $problemModel,
            $contestModel,
            $platformModel,
            $platformProfileModel,
            $contestRatingChangeModel,
            $submissionModel,
            $adapter,
            $syncService
        );

        $result = $importer->import('Baozii');

        $this->assertSame(1, $result->checked);
        $this->assertSame(1, $result->fetched);
        $this->assertSame(1, $result->created); // ONLY Registered User "Baozii" created! Unregistered skipped!

        $this->assertSame(10, $standingModel->capturedLookup['contest_id']);
        $this->assertSame('Baozii', $standingModel->capturedLookup['participant_key']);
        $this->assertSame(2550.0, $standingModel->capturedValues['points']); // 255000 / 100 = 2550.0
        $this->assertSame(1977, $standingModel->capturedValues['last_submission_time_seconds']); // 1977 seconds

        $this->assertSame(1, $standingTaskResultModel->capturedLookup['standing_id']);
        $this->assertSame(100, $standingTaskResultModel->capturedLookup['problem_id']);
        $this->assertSame(100.0, $standingTaskResultModel->capturedValues['points']); // 10000 / 100 = 100.0
        $this->assertSame('AC', $standingTaskResultModel->capturedValues['result_type']);
        $this->assertSame(556, $standingTaskResultModel->capturedValues['best_submission_time_seconds']);
    }
}
