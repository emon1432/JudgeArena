<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\Codeforces;

use App\Models\Contest;
use App\Models\Problem;
use App\Models\Submission;
use App\Platforms\Codeforces\Importers\UserSubmissionImporter;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportUserSubmissionsTest extends TestCase
{
    public function test_import_user_submissions_persists_submissions_to_database(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces');
        $profile = $this->createUserWithProfile($platform, 'tourist');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '1900',
            'name' => 'CF Round 1900',
        ]);

        $problem = Problem::query()->create([
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_problem_id' => '1900A',
            'name' => 'Daytona',
            'code' => 'A',
        ]);

        Http::fake([
            '*user.status*' => Http::response([
                'status' => 'OK',
                'result' => [
                    [
                        'id' => 99999,
                        'contestId' => 1900,
                        'creationTimeSeconds' => 1695739000,
                        'relativeTimeSeconds' => 100,
                        'problem' => [
                            'contestId' => 1900,
                            'index' => 'A',
                            'name' => 'Daytona',
                            'type' => 'PROGRAMMING',
                            'rating' => 800,
                        ],
                        'author' => [
                            'contestId' => 1900,
                            'members' => [['handle' => 'tourist']],
                            'participantType' => 'CONTESTANT',
                        ],
                        'programmingLanguage' => 'GNU C++20',
                        'verdict' => 'OK',
                        'testset' => 'TESTS',
                        'passedTestCount' => 15,
                        'timeConsumedMillis' => 30,
                        'memoryConsumedBytes' => 1024000,
                    ],
                ],
            ], 200),
        ]);

        $importer = app(UserSubmissionImporter::class);
        $result = $importer->import('tourist');

        $this->assertSame(1, $result->checked);

        $submission = Submission::query()
            ->where('platform_id', $platform->id)
            ->where('platform_submission_id', '99999')
            ->first();

        $this->assertNotNull($submission);
        $this->assertSame($profile->id, $submission->platform_profile_id);
        $this->assertSame('AC', $submission->verdict->value);
    }
}
