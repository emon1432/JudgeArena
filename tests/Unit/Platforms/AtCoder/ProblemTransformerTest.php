<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Platforms\AtCoder\Mappers\AtCoderProblemMapper;
use App\Platforms\AtCoder\Transformers\ProblemTransformer;
use Tests\TestCase;

class ProblemTransformerTest extends TestCase
{
    public function test_from_api_problem_transforms_atcoder_problem_dto(): void
    {
        $atcoderProblem = AtCoderProblemMapper::fromNormalized([
            'id' => 'abc300_a',
            'contest_id' => 'abc300',
            'position' => 'A',
            'title' => 'A. N-choice question',
            'rating' => 100,
            'point' => 100.0,
            'timeLimit' => '2 sec',
            'memoryLimit' => '1024 MB',
        ]);

        $transformer = new ProblemTransformer;
        $dto = $transformer->fromApiProblem($atcoderProblem);

        $this->assertSame('atcoder', $dto->platform);
        $this->assertSame('abc300_a', $dto->platformProblemId);
        $this->assertSame('N-choice question', $dto->title);
        $this->assertSame('abc300', $dto->contestPlatformId);
        $this->assertSame('A', $dto->code);
        $this->assertSame(100.0, $dto->points);
        $this->assertSame(100, $dto->rating);
        $this->assertSame(2000, $dto->timeLimit);
        $this->assertSame(1024, $dto->memoryLimit);
    }
}
