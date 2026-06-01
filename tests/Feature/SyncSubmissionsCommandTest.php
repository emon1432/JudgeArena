<?php

namespace Tests\Feature;

use App\Core\DTOs\SubmissionDTO;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SyncSubmissionsCommandTest extends TestCase
{
    public function test_submission_sync_validation_outputs_summary_without_writing_to_the_database(): void
    {
        $submission = new SubmissionDTO(
            platform: 'codeforces',
            platformSubmissionId: '321',
            problemPlatformId: '1840A',
            authorHandle: 'tourist',
            verdict: 'OK',
            language: 'GNU C++20',
            createdAtSeconds: 1700000000,
            contestPlatformId: '1840',
        );

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
        };

        $this->mock(PlatformProfile::class, function ($mock) use ($platformProfileQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($platformProfileQuery);
        });

        $this->mock(Contest::class, function ($mock) use ($contestQuery): void {
            $mock->shouldReceive('newQuery')->andReturn($contestQuery);
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

        $exitCode = Artisan::call('judgearena:sync-submissions', [
            'platform' => 'codeforces',
            'handle' => 'tourist',
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode, "Sync command failed with output:\n" . $output);
        $this->assertStringContainsString('Platform: codeforces', $output);
        $this->assertStringContainsString('Handle: tourist', $output);
        $this->assertStringContainsString('Contests Checked: 1', $output);
        $this->assertStringContainsString('Submissions Found: 1', $output);
        $this->assertStringContainsString('Submission ID: 321', $output);
        $this->assertStringContainsString('Problem ID: 1840A', $output);
        $this->assertStringContainsString('Contest ID: 1840', $output);
        $this->assertStringContainsString('Verdict: OK', $output);
    }
}
