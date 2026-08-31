<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\DTOs\CodeforcesProblemDTO;
use App\Platforms\Codeforces\Transformers\ProblemTransformer;
use Tests\TestCase;

class ProblemTransformerTest extends TestCase
{
    public function test_problem_transformer_maps_problem_payload(): void
    {
        $problemData = [
            'contestId' => 2225,
            'index' => 'A',
            'name' => 'A Number Between Two Others',
            'type' => 'PROGRAMMING',
            'points' => 500,
            'rating' => 800,
            'tags' => ['greedy', 'math'],
        ];

        $problemDto = new CodeforcesProblemDTO(
            contestId: (string) $problemData['contestId'],
            index: $problemData['index'],
            name: $problemData['name'],
            type: $problemData['type'],
            points: $problemData['points'],
            rating: $problemData['rating'],
            tags: $problemData['tags'],
            raw: $problemData,
        );

        $dto = (new ProblemTransformer())->fromApiProblem($problemDto);

        $this->assertSame('codeforces', $dto->platform);
        $this->assertSame('2225A', $dto->platformProblemId);
        $this->assertSame('A Number Between Two Others', $dto->title);
        $this->assertSame('2225', $dto->contestPlatformId);
        $this->assertSame(['greedy', 'math'], $dto->tags);
        $this->assertSame($problemData, $problemDto->raw);
    }
}
