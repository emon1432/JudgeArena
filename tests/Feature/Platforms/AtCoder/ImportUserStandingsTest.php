<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\AtCoder;

use App\Enums\SubmissionVerdict;
use App\Models\Contest;
use App\Models\Standing;
use App\Models\Submission;
use App\Platforms\AtCoder\Importers\UserStandingImporter;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportUserStandingsTest extends TestCase
{
    public function test_import_user_standings_persists_user_standing_to_database(): void
    {
        $platform = $this->createPlatform('atcoder', 'AtCoder');
        $profile = $this->createUserWithProfile($platform, 'tourist');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => 'abc300',
            'name' => 'AtCoder Beginner Contest 300',
            'phase' => 'FINISHED',
        ]);

        Submission::query()->create([
            'platform_id' => $platform->id,
            'platform_profile_id' => $profile->id,
            'contest_id' => $contest->id,
            'platform_submission_id' => '66666',
            'author_handle' => 'tourist',
            'verdict' => SubmissionVerdict::AC,
        ]);

        Http::fake([
            '*contests/abc300/standings/json*' => Http::response([
                'Fixed' => true,
                'TaskInfo' => [
                    [
                        'TaskScreenName' => 'abc300_a',
                        'TaskName' => 'abc300_a',
                        'Assignment' => 'A',
                    ],
                ],
                'StandingsData' => [
                    [
                        'Rank' => 1,
                        'UserName' => 'tourist',
                        'UserScreenName' => 'tourist',
                        'TotalResult' => [
                            'Count' => 1,
                            'Score' => 10000,
                            'Elapsed' => 900000000,
                            'Penalty' => 0,
                        ],
                        'TaskResults' => [
                            'abc300_a' => [
                                'Count' => 1,
                                'Score' => 10000,
                                'Elapsed' => 900000000,
                                'Penalty' => 0,
                                'Status' => 1,
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $importer = app(UserStandingImporter::class);
        $result = $importer->import('tourist');

        $this->assertSame(1, $result->checked);

        $standing = Standing::query()
            ->where('contest_id', $contest->id)
            ->where('platform_profile_id', $profile->id)
            ->first();

        $this->assertNotNull($standing);
        $this->assertSame(1, $standing->rank);
        $this->assertSame(100.0, (float) $standing->points);
    }
}
