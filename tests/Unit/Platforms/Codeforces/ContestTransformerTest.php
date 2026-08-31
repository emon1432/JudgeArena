<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\Mappers\CodeforcesContestMapper;
use App\Platforms\Codeforces\Transformers\ContestTransformer;
use Tests\TestCase;

class ContestTransformerTest extends TestCase
{
    public function test_contest_transformer_maps_standings_contest(): void
    {
        $contestData = [
            'id' => 2225,
            'name' => 'Educational Codeforces Round 189 (Rated for Div. 2)',
            'type' => 'CF',
            'phase' => 'FINISHED',
            'frozen' => false,
            'durationSeconds' => 7200,
            'startTimeSeconds' => 1776782100,
            'relativeTimeSeconds' => 7200,
        ];

        $contestDto = CodeforcesContestMapper::fromNormalized($contestData);
        $dto = (new ContestTransformer())->fromApiContest($contestDto);

        $this->assertSame('codeforces', $dto->platform);
        $this->assertSame('2225', $dto->platformContestId);
        $this->assertSame('Educational Codeforces Round 189 (Rated for Div. 2)', $dto->title);
        $this->assertSame('FINISHED', $dto->phase);
        $this->assertSame(7200, $dto->durationSeconds);
        $this->assertSame(1776782100, $dto->startedAt?->getTimestamp());
        $this->assertSame($contestData, $contestDto->raw);
    }
}
