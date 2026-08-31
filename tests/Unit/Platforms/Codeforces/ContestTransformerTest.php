<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\Mappers\CodeforcesContestMapper;
use App\Platforms\Codeforces\Transformers\ContestTransformer;
use Tests\TestCase;

class ContestTransformerTest extends TestCase
{
    private function sample(string $fileName): array
    {
        $path = base_path('docs/platforms/codeforces.com/sample-response/' . $fileName);

        return json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_contest_transformer_maps_standings_contest(): void
    {
        $standings = $this->sample('codeforces-contest-standings.json');
        $contestDto = CodeforcesContestMapper::fromNormalized($standings['contest']);
        $dto = (new ContestTransformer())->fromApiContest($contestDto);

        $this->assertSame('codeforces', $dto->platform);
        $this->assertSame('2225', $dto->platformContestId);
        $this->assertSame('Educational Codeforces Round 189 (Rated for Div. 2)', $dto->title);
        $this->assertSame('FINISHED', $dto->phase);
        $this->assertSame(7200, $dto->durationSeconds);
        $this->assertSame(1776782100, $dto->startedAt?->getTimestamp());
        $this->assertSame($standings['contest'], $contestDto->raw);
    }
}
