<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\Codeforces;

use App\Enums\SubmissionVerdict;
use App\Models\Contest;
use App\Models\Standing;
use App\Models\Submission;
use App\Platforms\Codeforces\Importers\UserStandingImporter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportUserStandingsTest extends TestCase
{
    public function test_import_user_standings_persists_user_standing_to_database(): void
    {
        Storage::fake('local');

        $platform = $this->createPlatform('codeforces', 'Codeforces');
        $profile = $this->createUserWithProfile($platform, 'tourist');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '1999',
            'name' => 'Codeforces Round 1999',
            'phase' => 'FINISHED',
        ]);

        Submission::query()->create([
            'platform_id' => $platform->id,
            'platform_profile_id' => $profile->id,
            'contest_id' => $contest->id,
            'platform_submission_id' => '88888',
            'platform_problem_id' => '1999A',
            'author_handle' => 'tourist',
            'verdict' => SubmissionVerdict::AC,
        ]);

        Http::fake([
            '*contest.standings*' => Http::response([
                'status' => 'OK',
                'result' => [
                    'contest' => [
                        'id' => 1999,
                        'name' => 'Codeforces Round 1999',
                        'type' => 'CF',
                        'phase' => 'FINISHED',
                    ],
                    'problems' => [],
                    'rows' => [
                        [
                            'party' => [
                                'contestId' => 1999,
                                'members' => [['handle' => 'tourist']],
                                'participantType' => 'CONTESTANT',
                            ],
                            'rank' => 1,
                            'points' => 500.0,
                            'penalty' => 15,
                            'problemResults' => [],
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
        $this->assertSame(500.0, (float) $standing->points);
    }
}
