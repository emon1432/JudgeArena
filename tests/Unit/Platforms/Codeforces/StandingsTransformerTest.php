<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\Mappers\CodeforcesStandingsMapper;
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
        $dto = CodeforcesStandingsMapper::fromApiResponse($standings);
        $core = (new StandingsTransformer())->fromApiStandings($dto);

        $this->assertSame('codeforces', $core->contest->platform);
        $this->assertSame('2225', $core->contest->platformContestId);
        $this->assertNotEmpty($core->problems);
        $this->assertNotEmpty($core->rows);
        $this->assertSame(1, $core->rows[0]->rank);
    }
}
