<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Platforms\AtCoder\DTOs\AtCoderContestDTO;
use App\Platforms\AtCoder\Transformers\ContestTransformer;
use Tests\TestCase;

class ContestTransformerTest extends TestCase
{
    public function test_contest_transformer_maps_atcoder_contest(): void
    {
        $contestDto = new AtCoderContestDTO(
            id: 'abc350',
            title: 'AtCoder Beginner Contest 350',
            type: 'Algorithm',
            date: '2024-04-20 21:00:00+0900',
            duration: '01:40',
            rateChange: ' ~ 1999',
            url: 'https://atcoder.jp/contests/abc350',
            raw: ['id' => 'abc350']
        );

        $dto = (new ContestTransformer())->fromApiContest($contestDto);

        $this->assertSame('atcoder', $dto->platform);
        $this->assertSame('abc350', $dto->platformContestId);
        $this->assertSame('AtCoder Beginner Contest 350', $dto->title);
        $this->assertSame(6000, $dto->durationSeconds);
        $this->assertSame('FINISHED', $dto->phase);
    }
}
