<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\AtCoder;

use App\Models\Contest;
use App\Models\Problem;
use App\Models\Submission;
use App\Platforms\AtCoder\Importers\UserSubmissionImporter;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportUserSubmissionsTest extends TestCase
{
    public function test_import_user_submissions_persists_submissions_to_database(): void
    {
        $platform = $this->createPlatform('atcoder', 'AtCoder');
        $profile = $this->createUserWithProfile($platform, 'tourist');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => 'abc300',
            'name' => 'AtCoder Beginner Contest 300',
        ]);

        $problem = Problem::query()->create([
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_problem_id' => 'abc300_a',
            'name' => 'N-choice question',
            'code' => 'A',
        ]);

        Http::fake([
            '*v3/user/submissions*' => Http::response([
                [
                    'id' => 77777,
                    'contest_id' => 'abc300',
                    'problem_id' => 'abc300_a',
                    'user_id' => 'tourist',
                    'language' => 'C++ 20 (gcc 12.2)',
                    'point' => 100.0,
                    'length' => 500,
                    'result' => 'AC',
                    'execution_time' => 15,
                    'epoch_second' => 1682769900,
                ],
            ], 200),
        ]);

        $importer = app(UserSubmissionImporter::class);
        $result = $importer->import('tourist');

        $this->assertSame(1, $result->checked);

        $submission = Submission::query()
            ->where('platform_id', $platform->id)
            ->where('platform_submission_id', '77777')
            ->first();

        $this->assertNotNull($submission);
        $this->assertSame($profile->id, $submission->platform_profile_id);
        $this->assertSame('AC', $submission->verdict->value);
        $this->assertSame(15, $submission->time_consumed_ms);
    }
}
