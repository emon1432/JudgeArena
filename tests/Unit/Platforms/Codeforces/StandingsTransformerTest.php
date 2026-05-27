<?php

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\Transformers\StandingsTransformer;
use Tests\TestCase;

class StandingsTransformerTest extends TestCase
{
    private function sample(string $fileName): array
    {
        $path = base_path('docs/platforms/codeforces.com/sample-response/' . $fileName);

        return json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_standings_transformer_maps_rows_and_problems(): void
    {
        $standings = $this->sample('codeforces-contest-standings.json');

        $result = (new StandingsTransformer())->fromApiStandings($standings);

        $this->assertInstanceOf(\App\Core\DTOs\ContestStandingsDTO::class, $result);

        $this->assertSame('2225', $result->contest->platformContestId);
        $this->assertCount(7, $result->problems);
        $this->assertGreaterThan(0, count($result->rows));

        $first = $result->rows[0];
        $this->assertSame(1, $first->rank);
        $this->assertSame(7, $first->points);
        $this->assertIsArray($first->members);
        $this->assertIsArray($first->problemResults);
    }
}
