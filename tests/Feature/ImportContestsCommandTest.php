<?php

namespace Tests\Feature;

use App\Enums\PlatformSyncStatus;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\PlatformSyncState;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\AtCoder\Importers\ContestImporter as AtCoderContestImporter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Platforms\Codeforces\Importers\ContestImporter as CodeforcesContestImporter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportContestsCommandTest extends TestCase
{
    public function test_codeforces_contest_import_uses_platform_sync_states(): void
    {
        $this->assertContestImportUsesPlatformSyncState(
            'codeforces',
            CodeforcesAdapter::class,
            CodeforcesContestImporter::class
        );
    }

    public function test_atcoder_contest_import_uses_platform_sync_states(): void
    {
        $this->assertContestImportUsesPlatformSyncState(
            'atcoder',
            AtCoderAdapter::class,
            AtCoderContestImporter::class
        );
    }

    private function assertContestImportUsesPlatformSyncState(string $platformSlug, string $adapterClass, string $importerClass): void
    {
        $platform = new class extends Platform {
            public $id = 1;
            public $slug;
            public $name;
        };

        $platform->slug = $platformSlug;
        $platform->name = ucfirst($platformSlug);

        $contest = new class extends Contest {
            public array $capturedLookup = [];
            public array $capturedValues = [];

            public function newQuery()
            {
                $self = $this;

                return new class($self) {
                    public function __construct(private readonly Contest $contest) {}

                    public function updateOrCreate(array $lookup, array $values): Contest
                    {
                        $this->contest->capturedLookup = $lookup;
                        $this->contest->capturedValues = $values;
                        $this->contest->wasRecentlyCreated = true;

                        return $this->contest;
                    }
                };
            }
        };

        $contest->id = 10;

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

        $contestDto = (object) [
            'platform' => $platformSlug,
            'platformContestId' => 'contest-1',
            'title' => 'Contest 1',
            'phase' => 'BEFORE',
            'startedAt' => null,
            'durationSeconds' => 7200,
            'raw' => ['id' => 'contest-1'],
        ];

        $this->mock($adapterClass, function ($mock) use ($contestDto, $importerClass): void {
            $mock->shouldReceive('getContests')
                ->once()
                ->andReturn([$contestDto]);

            $mock->shouldReceive('contestImporter')
                ->once()
                ->andReturnUsing(function () use ($importerClass) {
                    return app($importerClass);
                });
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

        $this->mock(Platform::class, function ($mock) use ($platformQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($platformQuery);
        });

        $contestQuery = new class($contest) {
            public function __construct(private readonly Contest $contest) {}

            public function updateOrCreate(array $lookup, array $values): Contest
            {
                return $this->contest->newQuery()->updateOrCreate($lookup, $values);
            }
        };

        $this->mock(Contest::class, function ($mock) use ($contestQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($contestQuery);
        });

        $exitCode = Artisan::call('judgearena:import-contests', [
            'platform' => $platformSlug,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode, "Import command failed with output:\n" . $output);
        $this->assertStringContainsString('Platform: ' . $platformSlug, $output);
        $this->assertStringContainsString('Contests Checked: 1', $output);
        $this->assertStringContainsString('Contests Synced: 1', $output);
        $this->assertStringContainsString('Contests Already Synced: 0', $output);
        $this->assertStringContainsString('Contests Failed: 0', $output);
        $this->assertSame([
            'platform_id' => 1,
            'platform_contest_id' => 'contest-1',
        ], $contest->capturedLookup);
        $this->assertSame('Contest 1', $contest->capturedValues['name']);
        $this->assertSame('BEFORE', $contest->capturedValues['phase']);
        $this->assertSame(7200, $contest->capturedValues['duration_seconds']);
        $this->assertSame(['id' => 'contest-1'], $contest->capturedValues['raw']);
        $this->assertSame('adapter', $contest->capturedValues['metadata']['source'] ?? null);
    }
}