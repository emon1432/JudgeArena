<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\DTOs\ProblemDTO;
use App\Enums\PlatformSyncStatus;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\PlatformSyncState;
use App\Models\Problem;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\AtCoder\Importers\ProblemImporter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Tests\TestCase;

class ImportAtCoderProblemsTest extends TestCase
{
    public function test_atcoder_problem_import_claims_and_syncs_each_contest_problems(): void
    {
        $platform = new class extends Platform {
            public $id = 1;
            public $slug = 'atcoder';
            public $name = 'AtCoder';
        };

        $contest = new class extends Contest {
            public $id = 10;
            public $platform_id = 1;
            public $platform_contest_id = 'abc399';
            public $name = 'AtCoder Beginner Contest 399';
            public $phase = 'FINISHED';
        };
        $contest->setRelation('platform', $platform);

        $problem = new class extends Problem {
            public array $capturedLookup = [];
            public array $capturedValues = [];

            public function newQuery()
            {
                $self = $this;

                return new class($self) {
                    public function __construct(private readonly Problem $problem) {}

                    public function updateOrCreate(array $lookup, array $values): Problem
                    {
                        $this->problem->capturedLookup = $lookup;
                        $this->problem->capturedValues = $values;

                        $problem = new Problem();
                        $problem->id = 100;
                        $problem->wasRecentlyCreated = true;

                        return $problem;
                    }
                };
            }
        };

        $platformQuery = new class($platform) {
            public function __construct(private readonly Platform $platform) {}

            public function where($column, $value)
            {
                return $this;
            }

            public function first()
            {
                return $this->platform;
            }
        };

        $contestQuery = new class($contest) {
            public function __construct(private readonly Contest $contest) {}

            public function where($column, $value)
            {
                return $this;
            }

            public function whereNotNull($column)
            {
                return $this;
            }

            public function with($relation)
            {
                return $this;
            }

            public function get()
            {
                return collect([$this->contest]);
            }

            public function first()
            {
                return $this->contest;
            }
        };

        $problemDto = new ProblemDTO(
            platform: 'atcoder',
            platformProblemId: 'abc399_a',
            title: 'Hamming Distance',
            contestPlatformId: 'abc399',
            code: 'A',
            points: 100.0,
            timeLimit: 2000,
            memoryLimit: 1024,
            url: 'https://atcoder.jp/contests/abc399/tasks/abc399_a',
            raw: ['id' => 'abc399_a']
        );

        $this->mock(AtCoderAdapter::class, function ($mock) use ($problemDto): void {
            $mock->shouldReceive('getContestProblems')
                ->once()
                ->with('abc399')
                ->andReturn([$problemDto]);
        });

        $this->app->instance(ApplicationLogger::class, new class extends ApplicationLogger {
            public function info(string $message, array $context = [], ?\Throwable $exception = null): void {}
            public function warning(string $message, array $context = [], ?\Throwable $exception = null): void {}
            public function error(string $message, array $context = [], ?\Throwable $exception = null): void {}
            public function critical(string $message, array $context = [], ?\Throwable $exception = null): void {}
        });

        $this->mock(\App\Platforms\AtCoder\Services\AtCoderCategoryTagService::class, function ($mock): void {
            $mock->shouldReceive('enrichProblem')->andReturn([
                'rating' => 1200,
                'tags' => ['Dynamic Programming'],
            ]);
        });

        $fakeState = new PlatformSyncState();
        $fakeState->sync_status = PlatformSyncStatus::Pending->value;

        $this->mock(PlatformSyncStateService::class, function ($mock) use ($fakeState): void {
            $mock->shouldReceive('markSyncing')->once()->andReturn($fakeState);
            $mock->shouldReceive('markSynced')->once()->andReturn($fakeState);
            $mock->shouldReceive('markFailed')->never();
        });

        $this->mock(Platform::class, function ($mock) use ($platformQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($platformQuery);
        });

        $this->mock(Contest::class, function ($mock) use ($contestQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($contestQuery);
        });

        $this->mock(Problem::class, function ($mock) use ($problem): void {
            $mock->shouldReceive('newQuery')->andReturn($problem->newQuery());
        });

        $stats = app(ProblemImporter::class)->import();

        $this->assertSame(1, $stats->checked);
        $this->assertSame(1, $stats->fetched);
        $this->assertSame(1, $stats->created);
        $this->assertSame(0, $stats->updated);
        $this->assertSame(0, $stats->failed);
        $this->assertSame([
            'platform_id' => 1,
            'platform_problem_id' => 'abc399_a',
        ], $problem->capturedLookup);
        $this->assertSame(10, $problem->capturedValues['contest_id']);
        $this->assertSame('Hamming Distance', $problem->capturedValues['name']);
        $this->assertSame('abc399-a-hamming-distance', $problem->capturedValues['slug']);
        $this->assertSame('A', $problem->capturedValues['code']);
        $this->assertSame(2000, $problem->capturedValues['time_limit_ms']);
        $this->assertSame(1024, $problem->capturedValues['memory_limit_mb']);
    }
}
