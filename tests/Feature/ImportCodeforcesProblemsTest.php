<?php

namespace Tests\Feature;

use App\Core\DTOs\ProblemDTO;
use App\Enums\PlatformSyncStatus;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\PlatformSyncState;
use App\Models\Problem;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Platforms\Codeforces\Importers\ProblemImporter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Tests\TestCase;

class ImportCodeforcesProblemsTest extends TestCase
{
    public function test_codeforces_problem_import_claims_and_syncs_each_problem(): void
    {
        $platform = new class extends Platform {
            public $id = 1;
            public $slug = 'codeforces';
            public $name = 'Codeforces';
        };

        $contest = new class extends Contest {
            public $id = 20;
            public $platform_id = 1;
            public $platform_contest_id = '1840';
            public $phase = 'BEFORE';
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
                        $problem->id = 30;
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
            platform: 'codeforces',
            platformProblemId: '1840A',
            title: 'Problem A',
            contestPlatformId: '1840',
            code: 'A',
            points: 100.0,
            rating: 1200,
            tags: ['math'],
            raw: ['id' => '1840A']
        );

        $standingsDto = new \App\Core\DTOs\ContestStandingsDTO(
            contest: new \App\Core\DTOs\ContestDTO(
                platform: 'codeforces',
                platformContestId: '1840',
                title: 'Contest 1840'
            ),
            problems: [$problemDto],
            rows: [new \App\Core\DTOs\ParticipantDTO(rank: 1)]
        );

        $this->mock(CodeforcesAdapter::class, function ($mock) use ($standingsDto): void {
            $mock->shouldReceive('getStandings')
                ->once()
                ->andReturn($standingsDto);
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
            $mock->shouldReceive('resetForRetry')->once()->andReturn($fakeState);
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
            'platform_problem_id' => '1840A',
        ], $problem->capturedLookup);
        $this->assertSame(20, $problem->capturedValues['contest_id']);
        $this->assertSame('Problem A', $problem->capturedValues['name']);
        $this->assertSame('A', $problem->capturedValues['code']);
        $this->assertSame(100.0, $problem->capturedValues['points']);
        $this->assertSame(1200, $problem->capturedValues['rating']);
        $this->assertSame(['math'], $problem->capturedValues['tags']);
        $this->assertSame(['id' => '1840A'], $problem->capturedValues['raw']);
        $this->assertSame('contest-scoped-sync', $problem->capturedValues['metadata']['source'] ?? null);
    }
}
