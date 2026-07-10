<?php

namespace Tests\Feature;

use App\Core\DTOs\SubmissionDTO;
use App\Enums\PlatformSyncStatus;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\PlatformSyncState;
use App\Models\Problem;
use App\Models\Submission;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Platforms\Codeforces\Importers\SubmissionImporter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Tests\TestCase;

class ImportCodeforcesSubmissionsTest extends TestCase
{
    public function test_codeforces_submission_import_claims_and_syncs_each_contest(): void
    {
        $platform = new class extends Platform {
            public $id = 1;
            public $slug = 'codeforces';
            public $name = 'Codeforces';
        };

        $profile = new class extends PlatformProfile {
            public $id = 10;
            public $platform_id = 1;
            public $handle = 'tourist';
        };
        $profile->platform = $platform;

        $contest = new class extends Contest {
            public $id = 20;
            public $platform_id = 1;
            public $platform_contest_id = '1840';
            public $name = 'Codeforces Round 1840';
        };
        $contest->platform = $platform;

        $problem = new class extends Problem {
            public $id = 30;
            public $platform_id = 1;
            public $platform_problem_id = '1840A';
        };

        $submission = new SubmissionDTO(
            platform: 'codeforces',
            platformSubmissionId: '321',
            problemPlatformId: '1840A',
            authorHandle: 'tourist',
            verdict: 'OK',
            language: 'GNU C++20',
            passedTestCount: 12,
            timeConsumedMillis: 46,
            createdAtSeconds: 1700000000,
            raw: ['id' => 321],
            contestPlatformId: '1840',
            points: 500.0,
            testset: 'TESTS',
            memoryConsumedBytes: 102400,
        );

        $platformProfileQuery = new class($profile) {
            public function __construct(private readonly PlatformProfile $profile) {}
            public function with($relations) { return $this; }
            public function whereHas($relation, $callback) { return $this; }
            public function whereRaw($sql, $bindings) { return $this; }
            public function first() { return $this->profile; }
        };

        $contestQuery = new class($contest) {
            public function __construct(private readonly Contest $contest) {}
            public function where($column, $value) { return $this; }
            public function whereHas($relation, $callback) { return $this; }
            public function with($relations) { return $this; }
            public function whereNotNull($column) { return $this; }
            public function orderBy($column) { return $this; }
            public function get() { return collect([$this->contest]); }
        };

        $problemQuery = new class($problem) {
            public function __construct(private readonly Problem $problem) {}
            public function where($column, $value) { return $this; }
            public function first() { return $this->problem; }
        };

        $submissionQuery = new class {
            public array $lookup = [];
            public array $values = [];

            public function updateOrCreate(array $lookup, array $values): Submission
            {
                $this->lookup = $lookup;
                $this->values = $values;

                $submission = new Submission();
                $submission->wasRecentlyCreated = true;

                return $submission;
            }
        };

        $this->mock(PlatformProfile::class, function ($mock) use ($platformProfileQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($platformProfileQuery);
        });

        $this->mock(Contest::class, function ($mock) use ($contestQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($contestQuery);
        });

        $this->mock(Problem::class, function ($mock) use ($problemQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($problemQuery);
        });

        $this->mock(Submission::class, function ($mock) use ($submissionQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($submissionQuery);
        });

        $this->mock(CodeforcesAdapter::class, function ($mock) use ($submission): void {
            $mock->shouldReceive('getSubmissions')
                ->once()
                ->with('1840')
                ->andReturn([$submission]);
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

        $importer = app(SubmissionImporter::class);

        $stats = $importer->import();

        $this->assertSame(1, $stats['contests_checked']);
        $this->assertSame(1, $stats['contests_synced']);
        $this->assertSame(0, $stats['contests_already_synced']);
        $this->assertSame(0, $stats['contests_failed']);
        $this->assertSame(1, $stats['submissions_fetched']);
        $this->assertSame(1, $stats['submissions_created']);
        $this->assertSame(0, $stats['submissions_updated']);
        $this->assertSame([
            'platform_submission_id' => '321',
        ], $submissionQuery->lookup);
        $this->assertNull($submissionQuery->values['platform_profile_id']);
        $this->assertSame(20, $submissionQuery->values['contest_id']);
        $this->assertSame(30, $submissionQuery->values['problem_id']);
        $this->assertSame('OK', $submissionQuery->values['verdict']);
        $this->assertSame('TESTS', $submissionQuery->values['metadata']['testset']);
        $this->assertSame(['id' => 321], $submissionQuery->values['raw']);
    }
}
