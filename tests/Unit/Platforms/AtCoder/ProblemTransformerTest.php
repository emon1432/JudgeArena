<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Platforms\AtCoder\DTOs\AtCoderProblemDTO;
use App\Platforms\AtCoder\Transformers\ProblemTransformer;
use Tests\TestCase;

class ProblemTransformerTest extends TestCase
{
    public function test_problem_transformer_maps_atcoder_problem(): void
    {
        $problemDto = new AtCoderProblemDTO(
            id: 'abc350_a',
            contestId: 'abc350',
            title: 'Past ABCs',
            position: 'A',
            points: 100.0,
            timeLimit: '2 sec',
            memoryLimit: '1024 MB',
            url: 'https://atcoder.jp/contests/abc350/tasks/abc350_a',
            raw: ['id' => 'abc350_a']
        );

        $dto = (new ProblemTransformer())->fromApiProblem($problemDto);

        $this->assertSame('atcoder', $dto->platform);
        $this->assertSame('abc350_a', $dto->platformProblemId);
        $this->assertSame('abc350', $dto->contestPlatformId);
        $this->assertSame('Past ABCs', $dto->title);
        $this->assertSame('A', $dto->code);
    }
}
