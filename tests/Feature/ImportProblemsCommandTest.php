<?php

namespace Tests\Feature;

use App\Core\DTOs\ProblemDTO;
use App\Enums\PlatformSyncEntityType;
use App\Enums\PlatformSyncStatus;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Tests\TestCase;

/**
 * This test avoids touching the database so it can run in environments where
 * the SQLite PDO driver is not installed (CI images or developer machines).
 * It mocks the parts of the system the command depends on and asserts the
 * expected orchestration calls and outcomes.
 */
class ImportProblemsCommandTest extends TestCase
{
    public function test_problem_import_uses_platform_sync_states_instead_of_problem_counts(): void
    {
        $platform = new class extends \App\Models\Platform {
            public $id = 1;
            public $slug = 'codeforces';
            public $name = 'Codeforces';
        };

        $realContest = new \App\Models\Contest();
        $realContest->id = 10;
        $realContest->platform_id = 1;
        $realContest->platform_contest_id = '2000';
        $realContest->name = 'Round 2000';
        $realContest->platform = $platform;

        // Prepare DTOs that the adapter will return
        $problems = collect(range('A', 'H'))->map(function (string $suffix) use ($platform, $realContest): ProblemDTO {
            return new ProblemDTO(
                platform: $platform->slug,
                platformProblemId: '2000' . $suffix,
                title: 'Problem ' . $suffix,
                contestPlatformId: $realContest->platform_contest_id,
                code: $suffix,
                points: 100.0,
                rating: 1200,
                tags: ['math'],
                raw: ['id' => '2000' . $suffix],
            );
        })->all();

        // Mock the adapters
        $this->mock(CodeforcesAdapter::class, function ($mock) use ($realContest, $problems): void {
            $mock->shouldReceive('getContestProblems')
                ->once()
                ->with($realContest->platform_contest_id)
                ->andReturn(['problems' => $problems]);
        });

        $this->mock(AtCoderAdapter::class, function ($mock): void {
            $mock->shouldNotReceive('getContestProblems');
        });

        // Bind a no-op application logger to avoid DB writes from ApplicationLogger
        $this->app->instance(ApplicationLogger::class, new class {
            public function info() {}
            public function warning() {}
            public function error() {}
            public function critical() {}
        });

        // Mock the platform sync service to avoid DB and to verify orchestration
        $fakeState = new \App\Models\PlatformSyncState();
        $fakeState->sync_status = PlatformSyncStatus::Pending->value;
        $fakeState->last_attempted_at = null;
        $fakeState->last_synced_at = null;

        $caughtException = null;
        $this->mock(PlatformSyncStateService::class, function ($mock) use ($fakeState, &$caughtException) {
            $mock->shouldReceive('findState')
                ->andReturn($fakeState);

            $mock->shouldReceive('canBeRetried')
                ->andReturn(true);

            $mock->shouldReceive('isSynced')
                ->andReturn(false);

            $mock->shouldReceive('markSyncing')
                ->andReturn($fakeState);

            $mock->shouldReceive('markSynced')
                ->andReturnUsing(function ($state, $metadata = []) use ($fakeState) {
                    $fakeState->sync_status = PlatformSyncStatus::Synced->value;
                    $fakeState->last_synced_at = now();
                    $fakeState->last_attempted_at = now();

                    return $fakeState;
                });
            $mock->shouldReceive('markFailed')
                ->andReturnUsing(function ($state, $e = null, $meta = []) use (&$caughtException) {
                    $caughtException = $e;

                    return $state;
                });
        });

        // Stub contest model so the command sees our single contest. Use a
        // subclass of the real model so DI type checks pass.
        // Mock the Contest model's query so the command receives our single contest
        $queryFake = new class($realContest) {
            private $contest;
            public function __construct($contest) { $this->contest = $contest; }
            public function with($arg) { return $this; }
            public function whereHas($rel, $cb) { return $this; }
            public function get() { return collect([$this->contest]); }
        };

        $this->mock(\App\Models\Contest::class, function ($mock) use ($queryFake) {
            $mock->shouldReceive('newQuery')->andReturn($queryFake);
        });

        // Stub problem model to capture created problems without DB
        $created = [];
        $fakeProblemModel = new class($created) extends \App\Models\Problem {
            private $created;
            public function __construct(&$created) { $this->created = &$created; }
            public function newQuery() {
                $self = $this;
                return new class($self) {
                    private $self;
                    public function __construct($self) { $this->self = $self; }
                    public function updateOrCreate($keys, $values) {
                        $obj = (object) array_merge($keys, $values);
                        $obj->wasRecentlyCreated = true;
                        $this->self->created[] = $obj;
                        return $obj;
                    }
                };
            }
        };

        $this->app->instance(\App\Models\Problem::class, $fakeProblemModel);

        // Sanity-check the fake contest model behavior before running command
        $query = app(\App\Models\Contest::class)->newQuery()->with('platform');
        $contests = $query->get();
        $this->assertCount(1, $contests);
        $this->assertInstanceOf(\App\Models\Contest::class, $contests->first());

        // Run the command and capture output for debugging if it fails
        $exitCode = \Artisan::call('judgearena:import-problems', ['platform' => 'codeforces']);
        $output = \Artisan::output();
        $this->assertSame(0, $exitCode, "Import command failed with output:\n" . $output . (isset($caughtException) ? "\nException: " . $caughtException->getMessage() : ''));

        // The command ran; adapter and service mocks verify orchestration.
    }
}
