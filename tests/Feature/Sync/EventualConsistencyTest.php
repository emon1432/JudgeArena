<?php

declare(strict_types=1);

namespace Tests\Feature\Sync;

use App\Core\DTOs\ContestDTO;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ParticipantDTO;
use App\Core\DTOs\ProblemDTO;
use App\Core\DTOs\ProblemResultDTO;
use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\Problem;
use App\Models\Standing;
use App\Models\StandingTaskResult;
use App\Models\Submission;
use App\Models\User;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\AtCoder\Importers\ContestImporter as AtCoderContestImporter;
use App\Platforms\AtCoder\Importers\ProblemImporter as AtCoderProblemImporter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Platforms\Codeforces\Importers\ContestImporter as CodeforcesContestImporter;
use App\Platforms\Codeforces\Importers\ProblemImporter as CodeforcesProblemImporter;
use App\Platforms\Codeforces\Importers\UserStandingImporter as CodeforcesUserStandingImporter;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class EventualConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private Platform $cfPlatform;

    private Platform $atcoderPlatform;

    private User $user;

    private PlatformProfile $cfProfile;

    private PlatformProfile $atcoderProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cfPlatform = Platform::create([
            'name' => 'Codeforces',
            'slug' => 'codeforces',
            'url' => 'https://codeforces.com',
            'status' => 'Active',
        ]);

        $this->atcoderPlatform = Platform::create([
            'name' => 'AtCoder',
            'slug' => 'atcoder',
            'url' => 'https://atcoder.jp',
            'status' => 'Active',
        ]);

        $this->user = User::create([
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->cfProfile = PlatformProfile::create([
            'user_id' => $this->user->id,
            'platform_id' => $this->cfPlatform->id,
            'handle' => 'tourist',
            'status' => 'Active',
        ]);

        $this->atcoderProfile = PlatformProfile::create([
            'user_id' => $this->user->id,
            'platform_id' => $this->atcoderPlatform->id,
            'handle' => 'chokudai',
            'status' => 'Active',
        ]);
    }

    public function test_codeforces_out_of_order_submissions_self_healing(): void
    {
        // 1. Ingest submission first when Contest and Problem DO NOT exist in DB yet
        $submission = Submission::create([
            'platform_id' => $this->cfPlatform->id,
            'platform_profile_id' => $this->cfProfile->id,
            'platform_submission_id' => '999001',
            'author_handle' => 'tourist',
            'verdict' => 'AC',
            'contest_id' => null,
            'problem_id' => null,
            'metadata' => [
                'contest_platform_id' => '1000',
                'problem_platform_id' => '1000A',
            ],
            'status' => 'Active',
        ]);

        $this->assertNull($submission->contest_id);
        $this->assertNull($submission->problem_id);

        // 2. Ingest Contest -> ContestImporter should backfill contest_id on orphaned submission
        $cfAdapter = Mockery::mock(CodeforcesAdapter::class);
        $cfAdapter->shouldReceive('getContests')->andReturn([
            new ContestDTO(
                platform: 'codeforces',
                platformContestId: '1000',
                title: 'Codeforces Round 1000',
                phase: 'FINISHED',
                type: 'CF',
                startedAt: new DateTimeImmutable('2026-01-01 10:00:00'),
                durationSeconds: 7200,
                endedAt: new DateTimeImmutable('2026-01-01 12:00:00'),
                url: 'https://codeforces.com/contest/1000',
                raw: ['type' => 'CF'],
            ),
        ]);

        $this->app->instance(CodeforcesAdapter::class, $cfAdapter);
        $contestImporter = $this->app->make(CodeforcesContestImporter::class);
        $contestResult = $contestImporter->import();

        $this->assertSame(1, $contestResult->created);
        $contest = Contest::where('platform_contest_id', '1000')->first();
        $this->assertNotNull($contest);

        $submission->refresh();
        $this->assertSame($contest->id, $submission->contest_id);
        $this->assertNull($submission->problem_id);

        // 3. Ingest Problems -> ProblemImporter should backfill problem_id on orphaned submission
        $standingsDto = new ContestStandingsDTO(
            contest: new ContestDTO(
                platform: 'codeforces',
                platformContestId: '1000',
                title: 'Codeforces Round 1000',
                phase: 'FINISHED',
            ),
            problems: [
                new ProblemDTO(
                    platform: 'codeforces',
                    platformProblemId: '1000A',
                    title: 'Problem A',
                    contestPlatformId: '1000',
                    code: 'A',
                    points: 500.0,
                    rating: 800,
                ),
            ],
            rows: [],
        );

        $cfAdapter->shouldReceive('getUserStandings')->with('1000')->andReturn($standingsDto);
        $problemImporter = $this->app->make(CodeforcesProblemImporter::class);
        $problemResult = $problemImporter->import();

        $this->assertSame(1, $problemResult->created);
        $problem = Problem::where('platform_problem_id', '1000A')->first();
        $this->assertNotNull($problem);

        $submission->refresh();
        $this->assertSame($contest->id, $submission->contest_id);
        $this->assertSame($problem->id, $submission->problem_id);
    }

    public function test_atcoder_out_of_order_submissions_self_healing(): void
    {
        // 1. Ingest submission first when Contest and Problem DO NOT exist in DB yet
        $submission = Submission::create([
            'platform_id' => $this->atcoderPlatform->id,
            'platform_profile_id' => $this->atcoderProfile->id,
            'platform_submission_id' => '888001',
            'author_handle' => 'chokudai',
            'verdict' => 'AC',
            'contest_id' => null,
            'problem_id' => null,
            'metadata' => [
                'contest_platform_id' => 'abc999',
                'problem_platform_id' => 'abc999_a',
            ],
            'status' => 'Active',
        ]);

        // 2. Ingest Contest
        $atcoderAdapter = Mockery::mock(AtCoderAdapter::class);
        $atcoderAdapter->shouldReceive('getContests')->andReturn([
            new ContestDTO(
                platform: 'atcoder',
                platformContestId: 'abc999',
                title: 'AtCoder Beginner Contest 999',
                phase: 'FINISHED',
                type: 'ABC',
                startedAt: new DateTimeImmutable('2026-02-01 12:00:00'),
                durationSeconds: 6000,
                endedAt: new DateTimeImmutable('2026-02-01 13:40:00'),
                url: 'https://atcoder.jp/contests/abc999',
                raw: ['rate_change_spec' => ['is_rated' => true]],
            ),
        ]);

        $this->app->instance(AtCoderAdapter::class, $atcoderAdapter);
        $contestImporter = $this->app->make(AtCoderContestImporter::class);
        $contestImporter->import();

        $contest = Contest::where('platform_contest_id', 'abc999')->first();
        $this->assertNotNull($contest);

        $submission->refresh();
        $this->assertSame($contest->id, $submission->contest_id);
        $this->assertNull($submission->problem_id);

        // 3. Ingest Problems
        $atcoderAdapter->shouldReceive('getUserStandings')->with('abc999')->andReturn(
            new ContestStandingsDTO(
                contest: new ContestDTO(platform: 'atcoder', platformContestId: 'abc999', title: 'AtCoder Beginner Contest 999', phase: 'FINISHED'),
                problems: [
                    new ProblemDTO(
                        platform: 'atcoder',
                        platformProblemId: 'abc999_a',
                        title: 'Problem A',
                        contestPlatformId: 'abc999',
                        code: 'A',
                        points: 100.0,
                    ),
                ],
                rows: [],
            )
        );
        $atcoderAdapter->shouldReceive('getContestProblems')->with('abc999')->andReturn([
            new ProblemDTO(
                platform: 'atcoder',
                platformProblemId: 'abc999_a',
                title: 'Problem A',
                contestPlatformId: 'abc999',
                code: 'A',
                points: 100.0,
            ),
        ]);

        $problemImporter = $this->app->make(AtCoderProblemImporter::class);
        $problemImporter->import();

        $problem = Problem::where('platform_problem_id', 'abc999_a')->first();
        $this->assertNotNull($problem);

        $submission->refresh();
        $this->assertSame($contest->id, $submission->contest_id);
        $this->assertSame($problem->id, $submission->problem_id);
    }

    public function test_standings_jit_creates_problems_and_persists_all_task_results(): void
    {
        $contest = Contest::create([
            'platform_id' => $this->cfPlatform->id,
            'platform_contest_id' => '2000',
            'name' => 'Codeforces Round 2000',
            'phase' => 'FINISHED',
            'status' => 'Active',
        ]);

        // Standings DTO has problems and user standing row with problem results
        $standingsDto = new ContestStandingsDTO(
            contest: new ContestDTO(
                platform: 'codeforces',
                platformContestId: '2000',
                title: 'Codeforces Round 2000',
                phase: 'FINISHED',
            ),
            problems: [
                new ProblemDTO(
                    platform: 'codeforces',
                    platformProblemId: '2000A',
                    title: 'Problem 2000A',
                    contestPlatformId: '2000',
                    code: 'A',
                    points: 500.0,
                ),
            ],
            rows: [
                new ParticipantDTO(
                    rank: 1,
                    points: 500,
                    penalty: 10,
                    members: [['handle' => 'tourist']],
                    problemResults: [
                        new ProblemResultDTO(
                            points: 500.0,
                            penalty: 10,
                            rejectedAttemptCount: 0,
                            type: 'FINAL',
                            bestSubmissionTimeSeconds: 600,
                        ),
                    ],
                    raw: ['party' => ['members' => [['handle' => 'tourist']]]],
                ),
            ],
        );

        // Note: No problems exist in DB yet for contest 2000!
        $this->assertSame(0, Problem::where('contest_id', $contest->id)->count());

        $cfAdapter = Mockery::mock(CodeforcesAdapter::class);
        $cfAdapter->shouldReceive('getUserStandings')->with('2000')->andReturn($standingsDto);
        $this->app->instance(CodeforcesAdapter::class, $cfAdapter);

        // Register rating change so standings importer discovers contest 2000 for tourist
        ContestRatingChange::create([
            'platform_id' => $this->cfPlatform->id,
            'contest_id' => $contest->id,
            'handle' => 'tourist',
            'old_rating' => 3000,
            'new_rating' => 3050,
            'rank' => 1,
        ]);

        $standingImporter = $this->app->make(CodeforcesUserStandingImporter::class);
        $standingResult = $standingImporter->import('tourist');

        // Verify Problem was JIT created from standings DTO
        $this->assertSame(1, Problem::where('contest_id', $contest->id)->count());
        $problem = Problem::where('platform_problem_id', '2000A')->first();
        $this->assertNotNull($problem);

        // Verify Standing was created
        $standing = Standing::where('contest_id', $contest->id)->where('platform_profile_id', $this->cfProfile->id)->first();
        $this->assertNotNull($standing);

        // Verify StandingTaskResult was NOT skipped and was created linking the JIT problem!
        $taskResult = StandingTaskResult::where('standing_id', $standing->id)->first();
        $this->assertNotNull($taskResult);
        $this->assertSame($problem->id, $taskResult->problem_id);
        $this->assertEquals(500.0, (float) $taskResult->points);
    }
}
