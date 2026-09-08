<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\AtCoder;

use App\Models\Contest;
use App\Models\Problem;
use App\Platforms\AtCoder\Importers\ProblemImporter;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportProblemsTest extends TestCase
{
    public function test_import_problems_persists_atcoder_problems_to_database(): void
    {
        $platform = $this->createPlatform('atcoder', 'AtCoder');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => 'abc300',
            'name' => 'AtCoder Beginner Contest 300',
            'phase' => 'BEFORE', // not skipped
        ]);

        Http::fake([
            '*contest-problem.json*' => Http::response([
                [
                    'contest_id' => 'abc300',
                    'problem_id' => 'abc300_a',
                    'problem_index' => 'A',
                ],
            ], 200),
            '*merged-problems.json*' => Http::response([
                [
                    'id' => 'abc300_a',
                    'contest_id' => 'abc300',
                    'title' => 'A. N-choice question',
                    'point' => 100.0,
                    'execution_time' => 2000,
                    'solver_count' => 5000,
                ],
            ], 200),
            '*problem-models.json*' => Http::response([
                'abc300_a' => [
                    'difficulty' => 100,
                    'raw_point' => 100,
                ],
            ], 200),
        ]);

        $importer = app(ProblemImporter::class);
        $result = $importer->import();

        $this->assertSame(1, $result->checked);

        $problem = Problem::query()
            ->where('platform_id', $platform->id)
            ->where('platform_problem_id', 'abc300_a')
            ->first();

        $this->assertNotNull($problem);
        $this->assertSame('N-choice question', $problem->name);
        $this->assertSame('A', $problem->code);
        $this->assertSame(100, $problem->rating);
    }
}
