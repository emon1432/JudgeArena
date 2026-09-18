<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\Codeforces;

use App\Enums\SubmissionVerdict;
use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Models\Problem;
use App\Models\Standing;
use App\Models\StandingTaskResult;
use App\Models\Submission;
use App\Platforms\Codeforces\Importers\UserStandingImporter;
use Carbon\Carbon;
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

    public function test_import_user_standings_reconciles_missing_rated_contests_from_rating_changes(): void
    {
        Storage::fake('local');

        $platform = $this->createPlatform('codeforces', 'Codeforces');
        $profile = $this->createUserWithProfile($platform, 'emon_mon');

        $contestStartTime = Carbon::parse('2026-01-01 14:00:00');
        $contestEndTime = Carbon::parse('2026-01-01 16:00:00');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '2000',
            'name' => 'Codeforces Round 2000 (Div. 3)',
            'phase' => 'FINISHED',
            'start_time' => $contestStartTime,
            'end_time' => $contestEndTime,
            'duration_seconds' => 7200,
        ]);

        $problemA = Problem::query()->create([
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_problem_id' => '2000A',
            'slug' => '2000a-problem-a',
            'name' => 'Problem A',
            'points' => 500,
        ]);

        $problemB = Problem::query()->create([
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_problem_id' => '2000B',
            'slug' => '2000b-problem-b',
            'name' => 'Problem B',
            'points' => 1000,
        ]);

        // Live contest submissions during contest
        Submission::query()->create([
            'platform_id' => $platform->id,
            'platform_profile_id' => $profile->id,
            'contest_id' => $contest->id,
            'problem_id' => $problemA->id,
            'platform_submission_id' => '100001',
            'author_handle' => 'emon_mon',
            'verdict' => SubmissionVerdict::WA,
            'submitted_at' => Carbon::parse('2026-01-01 14:15:00'),
            'raw' => [
                'author' => ['participantType' => 'CONTESTANT'],
                'relativeTimeSeconds' => 900,
            ],
        ]);

        Submission::query()->create([
            'platform_id' => $platform->id,
            'platform_profile_id' => $profile->id,
            'contest_id' => $contest->id,
            'problem_id' => $problemA->id,
            'platform_submission_id' => '100002',
            'author_handle' => 'emon_mon',
            'verdict' => SubmissionVerdict::AC,
            'submitted_at' => Carbon::parse('2026-01-01 14:20:00'),
            'raw' => [
                'author' => ['participantType' => 'CONTESTANT'],
                'relativeTimeSeconds' => 1200,
            ],
        ]);

        // Post-contest practice submission on Problem B (should NOT count for live contest)
        Submission::query()->create([
            'platform_id' => $platform->id,
            'platform_profile_id' => $profile->id,
            'contest_id' => $contest->id,
            'problem_id' => $problemB->id,
            'platform_submission_id' => '100003',
            'author_handle' => 'emon_mon',
            'verdict' => SubmissionVerdict::AC,
            'submitted_at' => Carbon::parse('2026-01-01 18:00:00'), // after contest end!
            'raw' => [
                'author' => ['participantType' => 'PRACTICE'],
                'relativeTimeSeconds' => 14400,
            ],
        ]);

        ContestRatingChange::query()->create([
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_profile_id' => $profile->id,
            'handle' => 'emon_mon',
            'is_rated' => true,
            'rank' => 15420,
            'old_rating' => 1200,
            'new_rating' => 1250,
            'rating_change' => 50,
        ]);

        // Standings payload only contains top contestants, user is omitted due to low rank
        Http::fake([
            '*contest.standings*' => Http::response([
                'status' => 'OK',
                'result' => [
                    'contest' => [
                        'id' => 2000,
                        'name' => 'Codeforces Round 2000 (Div. 3)',
                        'type' => 'CF',
                        'phase' => 'FINISHED',
                    ],
                    'problems' => [],
                    'rows' => [
                        [
                            'party' => [
                                'contestId' => 2000,
                                'members' => [['handle' => 'top_coder']],
                                'participantType' => 'CONTESTANT',
                            ],
                            'rank' => 1,
                            'points' => 2000.0,
                            'penalty' => 10,
                            'problemResults' => [],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $importer = app(UserStandingImporter::class);
        $result = $importer->import('emon_mon');

        $this->assertSame(1, $result->checked);

        $standing = Standing::query()
            ->where('contest_id', $contest->id)
            ->where('platform_profile_id', $profile->id)
            ->first();

        $this->assertNotNull($standing);
        $this->assertSame(15420, $standing->rank);
        $this->assertSame('CONTESTANT', $standing->participant_type);
        $this->assertSame('emon_mon', $standing->participant_key);
        $this->assertSame('rating-change-fallback', $standing->metadata['source'] ?? null);

        // Only Problem A (live 500 pts) was solved in contest, Problem B (post-contest practice) excluded
        $this->assertSame(500.0, (float) $standing->points);

        // Standing task results should contain Problem A with 1 rejected attempt before AC
        $taskA = StandingTaskResult::query()
            ->where('standing_id', $standing->id)
            ->where('problem_id', $problemA->id)
            ->first();

        $this->assertNotNull($taskA);
        $this->assertSame(1, $taskA->rejected_attempt_count);
        $this->assertSame(500.0, (float) $taskA->points);
        $this->assertSame('FINAL', $taskA->result_type);
        $this->assertSame(1200, $taskA->best_submission_time_seconds);

        // Problem B should NOT have task result since post-contest practice is excluded
        $taskB = StandingTaskResult::query()
            ->where('standing_id', $standing->id)
            ->where('problem_id', $problemB->id)
            ->first();

        $this->assertNull($taskB);
    }

    public function test_import_user_standings_ignores_practice_and_virtual_participant_rows(): void
    {
        Storage::fake('local');

        $platform = $this->createPlatform('codeforces', 'Codeforces');
        $profile = $this->createUserWithProfile($platform, 'emon_mon');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '2001',
            'name' => 'Codeforces Round 2001',
            'phase' => 'FINISHED',
        ]);

        Submission::query()->create([
            'platform_id' => $platform->id,
            'platform_profile_id' => $profile->id,
            'contest_id' => $contest->id,
            'platform_submission_id' => '99999',
            'platform_problem_id' => '2001A',
            'author_handle' => 'emon_mon',
            'verdict' => SubmissionVerdict::AC,
        ]);

        Http::fake([
            '*contest.standings*' => Http::response([
                'status' => 'OK',
                'result' => [
                    'contest' => [
                        'id' => 2001,
                        'name' => 'Codeforces Round 2001',
                        'type' => 'CF',
                        'phase' => 'FINISHED',
                    ],
                    'problems' => [],
                    'rows' => [
                        [
                            'party' => [
                                'contestId' => 2001,
                                'members' => [['handle' => 'emon_mon']],
                                'participantType' => 'PRACTICE',
                            ],
                            'rank' => 0,
                            'points' => 500.0,
                            'penalty' => 0,
                            'problemResults' => [],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $importer = app(UserStandingImporter::class);
        $importer->import('emon_mon');

        $standing = Standing::query()
            ->where('contest_id', $contest->id)
            ->where('platform_profile_id', $profile->id)
            ->first();

        $this->assertNull($standing);
    }

    public function test_import_user_standings_skips_unattempted_problems_in_standings_json(): void
    {
        Storage::fake('local');

        $platform = $this->createPlatform('codeforces', 'Codeforces');
        $profile = $this->createUserWithProfile($platform, 'tourist');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '2002',
            'name' => 'Codeforces Round 2002',
            'phase' => 'FINISHED',
        ]);

        Submission::query()->create([
            'platform_id' => $platform->id,
            'platform_profile_id' => $profile->id,
            'contest_id' => $contest->id,
            'platform_submission_id' => '11111',
            'platform_problem_id' => '2002A',
            'author_handle' => 'tourist',
            'verdict' => SubmissionVerdict::AC,
        ]);

        Http::fake([
            '*contest.standings*' => Http::response([
                'status' => 'OK',
                'result' => [
                    'contest' => [
                        'id' => 2002,
                        'name' => 'Codeforces Round 2002',
                        'type' => 'CF',
                        'phase' => 'FINISHED',
                    ],
                    'problems' => [
                        [
                            'contestId' => 2002,
                            'index' => 'A',
                            'name' => 'Problem A',
                            'type' => 'PROGRAMMING',
                            'points' => 500,
                        ],
                        [
                            'contestId' => 2002,
                            'index' => 'B',
                            'name' => 'Problem B',
                            'type' => 'PROGRAMMING',
                            'points' => 1000,
                        ],
                    ],
                    'rows' => [
                        [
                            'party' => [
                                'contestId' => 2002,
                                'members' => [['handle' => 'tourist']],
                                'participantType' => 'CONTESTANT',
                            ],
                            'rank' => 1,
                            'points' => 500.0,
                            'penalty' => 10,
                            'problemResults' => [
                                [
                                    'points' => 500.0,
                                    'penalty' => 10,
                                    'rejectedAttemptCount' => 0,
                                    'type' => 'FINAL',
                                    'bestSubmissionTimeSeconds' => 600,
                                ],
                                [
                                    'points' => 0.0,
                                    'penalty' => 0,
                                    'rejectedAttemptCount' => 0,
                                    'type' => 'FINAL',
                                    'bestSubmissionTimeSeconds' => null,
                                ],
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

        $taskResults = StandingTaskResult::query()
            ->where('standing_id', $standing->id)
            ->get();

        // Exactly 1 task result should exist (Problem A), and 0 for unattempted Problem B
        $this->assertCount(1, $taskResults);
        $this->assertSame(500.0, (float) $taskResults->first()->points);
    }
}
