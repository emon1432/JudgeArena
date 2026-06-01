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
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportSubmissionsCommandTest extends TestCase
{
    public function test_submission_import_persists_core_submission_dtos(): void
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

        $problem = new class extends Problem {
            public $id = 30;
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
            public function whereNotNull($column) { return $this; }
            public function orderBy($column) { return $this; }
            public function get() { return collect([$this->contest]); }
            public function first() { return $this->contest; }
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
                ->with('1840', 'tourist')
                ->andReturn([$submission]);
        });

        $this->mock(AtCoderAdapter::class, function ($mock): void {
            $mock->shouldNotReceive('getSubmissions');
        });

        $this->app->instance(ApplicationLogger::class, new class {
            public function info(...$args) {}
            public function warning(...$args) {}
            public function error(...$args) {}
            public function critical(...$args) {}
        });

        $fakeState = new PlatformSyncState();
        $fakeState->sync_status = PlatformSyncStatus::Pending->value;

        $this->mock(PlatformSyncStateService::class, function ($mock) use ($fakeState): void {
            $mock->shouldReceive('findState')->andReturn(null);
            $mock->shouldReceive('canBeRetried')->andReturn(true);
            $mock->shouldReceive('isSynced')->andReturn(false);
            $mock->shouldReceive('markSyncing')->andReturn($fakeState);
            $mock->shouldReceive('markSynced')->once()->andReturn($fakeState);
            $mock->shouldReceive('markFailed')->never();
        });

        $exitCode = Artisan::call('judgearena:import-submissions', [
            'platform' => 'codeforces',
            'handle' => 'tourist',
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode, "Import command failed with output:\n" . $output);
        $this->assertStringContainsString('Submissions Fetched: 1', $output);
        $this->assertStringContainsString('Submissions Created: 1', $output);
        $this->assertSame([
            'platform_id' => 1,
            'platform_submission_id' => '321',
        ], $submissionQuery->lookup);
        $this->assertSame(10, $submissionQuery->values['platform_profile_id']);
        $this->assertSame(20, $submissionQuery->values['contest_id']);
        $this->assertSame(30, $submissionQuery->values['problem_id']);
        $this->assertSame('OK', $submissionQuery->values['verdict']);
        $this->assertSame('TESTS', $submissionQuery->values['metadata']['testset']);
        $this->assertSame(['id' => 321], $submissionQuery->values['raw']);
    }
}
