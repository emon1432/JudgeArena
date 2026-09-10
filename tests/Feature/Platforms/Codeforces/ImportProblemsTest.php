<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\Codeforces;

use App\Models\Contest;
use App\Models\Problem;
use App\Platforms\Codeforces\Importers\ProblemImporter;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportProblemsTest extends TestCase
{
    public function test_import_problems_persists_contest_problems_to_database(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '1900',
            'name' => 'Codeforces Round 900 (Div. 3)',
            'phase' => 'BEFORE', // not FINISHED so it is not skipped
        ]);

        Http::fake([
            '*contest.standings*' => Http::response([
                'status' => 'OK',
                'result' => [
                    'contest' => [
                        'id' => 1900,
                        'name' => 'Codeforces Round 900 (Div. 3)',
                        'type' => 'CF',
                        'phase' => 'FINISHED',
                    ],
                    'problems' => [
                        [
                            'contestId' => 1900,
                            'index' => 'A',
                            'name' => 'How Much Does Daytona Cost?',
                            'type' => 'PROGRAMMING',
                            'points' => 500,
                            'rating' => 800,
                        ],
                    ],
                    'rows' => [],
                ],
            ], 200),
        ]);

        $importer = app(ProblemImporter::class);
        $result = $importer->import();

        $this->assertSame(1, $result->checked);

        $problem = Problem::query()
            ->where('platform_id', $platform->id)
            ->where('platform_problem_id', '1900A')
            ->first();

        $this->assertNotNull($problem);
        $this->assertSame('How Much Does Daytona Cost?', $problem->name);
        $this->assertSame('A', $problem->code);
        $this->assertSame(800, $problem->rating);
    }

    public function test_import_problems_handles_finished_contest_with_no_standings_gracefully(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces');

        Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '1595',
            'name' => 'Technocup 2022 - Elimination Round 1',
            'phase' => 'FINISHED',
        ]);

        Http::fake([
            '*contest.standings*' => Http::response([
                'status' => 'FAILED',
                'comment' => 'contestId: Contest with id 1595 not found',
            ], 400),
        ]);

        $importer = app(ProblemImporter::class);
        $result = $importer->import();

        $this->assertSame(1, $result->checked);
        $this->assertSame(1, $result->skipped);
        $this->assertSame(0, $result->failed);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'contest_problems',
            'entity_platform_id' => '1595',
            'sync_status' => 'synced',
        ]);
    }

    public function test_import_problems_does_not_mark_before_phase_contest_as_synced_on_error(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces');

        Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '2050',
            'name' => 'Future Contest',
            'phase' => 'BEFORE',
        ]);

        Http::fake([
            '*contest.standings*' => Http::response([
                'status' => 'FAILED',
                'comment' => 'contestId: Contest with id 2050 not found',
            ], 400),
        ]);

        $importer = app(ProblemImporter::class);
        $result = $importer->import();

        $this->assertSame(1, $result->checked);
        $this->assertSame(1, $result->failed);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'contest_problems',
            'entity_platform_id' => '2050',
            'sync_status' => 'failed',
        ]);
    }

    public function test_import_problems_respects_batch_limit_and_skips_synced(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces');

        Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '101',
            'name' => 'Contest 101',
            'phase' => 'FINISHED',
        ]);

        Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '102',
            'name' => 'Contest 102',
            'phase' => 'FINISHED',
        ]);

        Http::fake(function (\Illuminate\Http\Client\Request $request) {
            $contestId = $request['contestId'] ?? null;

            if ($contestId == '102' || str_contains($request->url(), 'contestId=102')) {
                return Http::response([
                    'status' => 'OK',
                    'result' => [
                        'contest' => ['id' => 102, 'phase' => 'FINISHED'],
                        'problems' => [
                            ['contestId' => 102, 'index' => 'A', 'name' => 'Problem 102A'],
                        ],
                        'rows' => [],
                    ],
                ], 200);
            }

            return Http::response([
                'status' => 'OK',
                'result' => [
                    'contest' => ['id' => 101, 'phase' => 'FINISHED'],
                    'problems' => [
                        ['contestId' => 101, 'index' => 'A', 'name' => 'Problem 101A'],
                    ],
                    'rows' => [],
                ],
            ], 200);
        });

        $importer = app(ProblemImporter::class);

        // Run batch limit = 1
        $result1 = $importer->import(limit: 1);
        $this->assertSame(1, $result1->checked);
        $this->assertSame(1, $result1->created);

        // Next run should pick Contest 102
        $result2 = $importer->import(limit: 1);
        $this->assertSame(1, $result2->checked);
        $this->assertSame(1, $result2->created);

        $this->assertDatabaseHas('problems', [
            'platform_problem_id' => '101A',
        ]);
        $this->assertDatabaseHas('problems', [
            'platform_problem_id' => '102A',
        ]);
    }
}
