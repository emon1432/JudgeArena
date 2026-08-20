<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\DTOs\SubmissionDTO;
use App\Enums\PlatformSyncStatus;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\PlatformSyncState;
use App\Models\Problem;
use App\Models\Submission;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\AtCoder\Importers\UserSubmissionImporter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Tests\TestCase;

class ImportAtCoderUserSubmissionsTest extends TestCase
{
    public function test_atcoder_user_submissions_import_saves_submissions_successfully(): void
    {
        $platform = new class extends Platform {
            public $id = 2;
            public $slug = 'atcoder';
            public $name = 'AtCoder';
        };

        $profile = new class extends PlatformProfile {
            public $id = 5;
            public $platform_id = 2;
            public $handle = 'chokudai';
            public $status = 'Active';
        };

        $contest = new class extends Contest {
            public $id = 10;
            public $platform_id = 2;
            public $platform_contest_id = 'abc399';
            public $name = 'AtCoder Beginner Contest 399';
        };

        $problem = new class extends Problem {
            public $id = 100;
            public $platform_id = 2;
            public $platform_problem_id = 'abc399_a';
        };

        $submissionModel = new class extends Submission {
            public array $capturedLookup = [];
            public array $capturedValues = [];

            public function newQuery()
            {
                $self = $this;

                return new class($self) {
                    public function __construct(private readonly Submission $model) {}

                    public function updateOrCreate(array $lookup, array $values): Submission
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

                    public function latest(string $col): self
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

        $subDto = new SubmissionDTO(
            platform: 'atcoder',
            platformSubmissionId: '62849102',
            contestPlatformId: 'abc399',
            problemPlatformId: 'abc399_a',
            authorHandle: 'chokudai',
            verdict: 'AC',
            language: 'C++ 20 (gcc 12.2)',
            points: 100.0,
            passedTestCount: null,
            timeConsumedMillis: 15,
            memoryConsumedBytes: 3686400,
            createdAtSeconds: 1770000000,
            raw: ['submission_id' => '62849102', 'result' => 'AC']
        );

        $adapter = $this->mock(AtCoderAdapter::class, function ($mock) use ($subDto): void {
            $mock->shouldReceive('getUserSubmissions')->once()->with([
                'contestId' => 'abc399',
                'handle' => 'chokudai',
                'stopSubmissionId' => null,
            ])->andReturn([
                'submissions' => [$subDto],
                'reached_stop' => false,
            ]);
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

        $importer = new UserSubmissionImporter(
            $submissionModel,
            $problemModel,
            $contestModel,
            $platformModel,
            $platformProfileModel,
            $adapter,
            $syncService
        );

        $result = $importer->import('chokudai');

        $this->assertSame(1, $result->checked);
        $this->assertSame(1, $result->fetched);
        $this->assertSame(1, $result->created);

        $this->assertSame(2, $submissionModel->capturedLookup['platform_id']);
        $this->assertSame('62849102', $submissionModel->capturedLookup['platform_submission_id']);
        $this->assertSame('chokudai', $submissionModel->capturedValues['author_handle']);
        $this->assertSame('AC', $submissionModel->capturedValues['verdict']);
        $this->assertSame('C++ 20 (gcc 12.2)', $submissionModel->capturedValues['language']);
        $this->assertSame(15, $submissionModel->capturedValues['time_consumed_ms']);
        $this->assertSame(3686400, $submissionModel->capturedValues['memory_consumed_bytes']);
    }
}
