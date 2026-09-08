<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\Mappers\CodeforcesProblemMapper;
use App\Platforms\Codeforces\Transformers\ProblemTransformer;
use Tests\TestCase;

class ProblemTransformerTest extends TestCase
{
    public function test_from_api_problem_transforms_codeforces_problem(): void
    {
        $cfProblem = CodeforcesProblemMapper::fromNormalized([
            'contestId' => 1000,
            'index' => 'A',
            'name' => 'Codehorses T-shirts',
            'type' => 'PROGRAMMING',
            'points' => 500,
            'rating' => 1400,
            'tags' => ['greedy', 'strings'],
            'solvedCount' => 1500,
        ]);

        $transformer = new ProblemTransformer();
        $dto = $transformer->fromApiProblem($cfProblem);

        $this->assertSame('codeforces', $dto->platform);
        $this->assertSame('1000A', $dto->platformProblemId);
        $this->assertSame('Codehorses T-shirts', $dto->title);
        $this->assertSame('1000', $dto->contestPlatformId);
        $this->assertSame('A', $dto->code);
        $this->assertSame(500.0, (float) $dto->points);
        $this->assertSame(1400, $dto->rating);
        $this->assertSame(1500, $dto->solvedCount);
        $this->assertSame(['greedy', 'strings'], $dto->tags);
        $this->assertSame('https://codeforces.com/contest/1000/problem/A', $dto->url);
    }
}

