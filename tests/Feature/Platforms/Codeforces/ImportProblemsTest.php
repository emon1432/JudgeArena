<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\Codeforces;

use App\Models\Contest;
use App\Models\Problem;
use App\Platforms\Codeforces\Importers\ProblemImporter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportProblemsTest extends TestCase
{
    public function test_import_problems_persists_contest_problems_to_database(): void
    {
        Storage::fake('local');
        $platform = $this->createPlatform('codeforces', 'Codeforces');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '1900',
            'name' => 'Codeforces Round 900 (Div. 3)',
            'phase' => 'BEFORE', // not FINISHED so it is not skipped
            'phase' => 'FINISHED',
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

    public function test_import_problems_respects_batch_limit(): void
    {
        Storage::fake('local');
        $platform = $this->createPlatform('codeforces', 'Codeforces');

        Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '1001',
            'name' => 'Contest 1',
            'phase' => 'BEFORE',
            'phase' => 'FINISHED',
        ]);
        Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '1002',
            'name' => 'Contest 2',
            'phase' => 'BEFORE',
            'phase' => 'FINISHED',
        ]);

        Http::fake([
            '*contest.standings*' => Http::response([
                'status' => 'OK',
                'result' => [
                    'contest' => ['id' => 1001, 'name' => 'Contest 1', 'phase' => 'FINISHED'],
                    'problems' => [['contestId' => 1001, 'index' => 'A', 'name' => 'Problem 1A']],
                    'rows' => [],
                ],
            ], 200),
        ]);

        $importer = app(ProblemImporter::class);
        $result = $importer->import(limit: 1);

        $this->assertSame(1, $result->checked);
    }

    public function test_import_problems_ignores_upcoming_before_phase_contests(): void
    {
        Storage::fake('local');
        $platform = $this->createPlatform('codeforces', 'Codeforces');

        Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '2264',
            'name' => 'Codeforces Round 1121 (Div. 2)',
            'phase' => 'BEFORE',
        ]);

        $importer = app(ProblemImporter::class);
        $result = $importer->import();

        $this->assertSame(0, $result->checked);
    }
}
