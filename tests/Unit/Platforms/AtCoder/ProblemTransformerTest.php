<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Platforms\AtCoder\DTOs\AtCoderProblemDTO;
use App\Platforms\AtCoder\Mappers\AtCoderProblemMapper;
use App\Platforms\AtCoder\Transformers\ProblemTransformer;
use Tests\TestCase;

class ProblemTransformerTest extends TestCase
{
    public function test_problem_transformer_maps_atcoder_problem(): void
    {
        $raw = [
            'id' => 'abc350_a',
            'contest_id' => 'abc350',
            'title' => 'A. Sort and Merge',
            'problem_index' => 'A',
            'point' => 100.0,
            'difficulty' => 450,
            'execution_time' => 2000,
            'solver_count' => 3500,
        ];

        $dto = AtCoderProblemMapper::fromNormalized($raw);
        $this->assertInstanceOf(AtCoderProblemDTO::class, $dto);

        $coreDto = (new ProblemTransformer())->fromApiProblem($dto);

        $this->assertSame('atcoder', $coreDto->platform);
        $this->assertSame('abc350_a', $coreDto->platformProblemId);
        $this->assertSame('Sort and Merge', $coreDto->title);
        $this->assertSame('abc350', $coreDto->contestPlatformId);
        $this->assertSame('A', $coreDto->code);
        $this->assertSame(100.0, $coreDto->points);
        $this->assertSame(450, $coreDto->rating);
        $this->assertSame(2000, $coreDto->timeLimit);
        $this->assertSame(1024, $coreDto->memoryLimit);
        $this->assertSame(3500, $coreDto->solvedCount);
        $this->assertSame('https://atcoder.jp/contests/abc350/tasks/abc350_a', $coreDto->url);
    }
}
