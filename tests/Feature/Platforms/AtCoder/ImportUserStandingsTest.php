<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\AtCoder;

use App\Enums\SubmissionVerdict;
use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Models\Problem;
use App\Models\Standing;
use App\Models\StandingTaskResult;
use App\Models\Submission;
use App\Platforms\AtCoder\Importers\UserStandingImporter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportUserStandingsTest extends TestCase
{
    public function test_import_user_standings_persists_user_standing_to_database(): void
    {
        Storage::fake('local');

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
            '*' => Http::response([], 200),
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

    public function test_import_user_standings_reconciles_missing_rated_contests_from_rating_changes(): void
    {
        Storage::fake('local');

        $platform = $this->createPlatform('atcoder', 'AtCoder');
        $profile = $this->createUserWithProfile($platform, 'chokudai');

        $contestStartTime = Carbon::parse('2026-02-01 12:00:00');
        $contestEndTime = Carbon::parse('2026-02-01 13:40:00');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => 'abc301',
            'name' => 'AtCoder Beginner Contest 301',
            'phase' => 'FINISHED',
            'start_time' => $contestStartTime,
            'end_time' => $contestEndTime,
            'duration_seconds' => 6000,
        ]);

        $problemA = Problem::query()->create([
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_problem_id' => 'abc301_a',
            'slug' => 'abc301-a-problem-a',
            'name' => 'Problem A',
            'points' => 100,
        ]);

        Submission::query()->create([
            'platform_id' => $platform->id,
            'platform_profile_id' => $profile->id,
            'contest_id' => $contest->id,
            'problem_id' => $problemA->id,
            'platform_submission_id' => '77777',
            'author_handle' => 'chokudai',
            'verdict' => SubmissionVerdict::AC,
            'submitted_at' => Carbon::parse('2026-02-01 12:10:00'),
            'raw' => [
                'relativeTimeSeconds' => 600,
            ],
        ]);

        ContestRatingChange::query()->create([
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_profile_id' => $profile->id,
            'handle' => 'chokudai',
            'is_rated' => true,
            'rank' => 8500,
            'old_rating' => 2800,
            'new_rating' => 2820,
            'performance' => 3000,
        ]);

        Http::fake([
            '*contests/abc301/standings/json*' => Http::response([
                'Fixed' => true,
                'TaskInfo' => [],
                'StandingsData' => [],
            ], 200),
            '*' => Http::response([], 200),
        ]);

        $importer = app(UserStandingImporter::class);
        $result = $importer->import('chokudai');

        $this->assertSame(1, $result->checked);

        $standing = Standing::query()
            ->where('contest_id', $contest->id)
            ->where('platform_profile_id', $profile->id)
            ->first();

        $this->assertNotNull($standing);
        $this->assertSame(8500, $standing->rank);
        $this->assertSame('rating-change-fallback', $standing->metadata['source'] ?? null);
        $this->assertSame(100.0, (float) $standing->points);

        $taskA = StandingTaskResult::query()
            ->where('standing_id', $standing->id)
            ->where('problem_id', $problemA->id)
            ->first();

        $this->assertNotNull($taskA);
        $this->assertSame(100.0, (float) $taskA->points);
        $this->assertSame('AC', $taskA->result_type);
        $this->assertSame(600, $taskA->best_submission_time_seconds);
    }

    public function test_import_user_standings_skips_unattempted_tasks_in_standings_json(): void
    {
        Storage::fake('local');

        $platform = $this->createPlatform('atcoder', 'AtCoder');
        $profile = $this->createUserWithProfile($platform, 'tourist');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => 'abc302',
            'name' => 'AtCoder Beginner Contest 302',
            'phase' => 'FINISHED',
        ]);

        Submission::query()->create([
            'platform_id' => $platform->id,
            'platform_profile_id' => $profile->id,
            'contest_id' => $contest->id,
            'platform_submission_id' => '88888',
            'author_handle' => 'tourist',
            'verdict' => SubmissionVerdict::AC,
        ]);

        Http::fake([
            '*contests/abc302/standings/json*' => Http::response([
                'Fixed' => true,
                'TaskInfo' => [
                    [
                        'TaskScreenName' => 'abc302_a',
                        'TaskName' => 'abc302_a',
                        'Assignment' => 'A',
                    ],
                    [
                        'TaskScreenName' => 'abc302_b',
                        'TaskName' => 'abc302_b',
                        'Assignment' => 'B',
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
                            'Elapsed' => 600000000000,
                            'Penalty' => 0,
                        ],
                        'TaskResults' => [
                            'abc302_a' => [
                                'Count' => 1,
                                'Score' => 10000,
                                'Elapsed' => 600000000000,
                                'Penalty' => 0,
                                'Status' => 1,
                            ],
                            'abc302_b' => [
                                'Count' => 0,
                                'Score' => 0,
                                'Elapsed' => 0,
                                'Penalty' => 0,
                                'Status' => 0,
                                'Failure' => 0,
                            ],
                        ],
                    ],
                ],
            ], 200),
            '*' => Http::response([], 200),
        ]);

        $importer = app(UserStandingImporter::class);
        $result = $importer->import('tourist');

        $this->assertSame(1, $result->checked);

        $standing = Standing::query()
            ->where('contest_id', $contest->id)
            ->where('platform_profile_id', $profile->id)
            ->first();

        $this->assertNotNull($standing);

        $taskResults = StandingTaskResult::query()
            ->where('standing_id', $standing->id)
            ->get();

        // Exactly 1 task result should exist (Problem A), and 0 for unattempted Task B
        $this->assertCount(1, $taskResults);
        $this->assertSame(100.0, (float) $taskResults->first()->points);
    }
}
