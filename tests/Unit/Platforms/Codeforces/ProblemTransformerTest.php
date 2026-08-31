<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\DTOs\CodeforcesProblemDTO;
use App\Platforms\Codeforces\Transformers\ProblemTransformer;
use Tests\TestCase;

class ProblemTransformerTest extends TestCase
{
    private function sample(string $fileName): array
    {
        $path = base_path('docs/platforms/codeforces.com/sample-response/' . $fileName);

        return json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_problem_transformer_maps_problem_payload(): void
    {
        $standings = $this->sample('codeforces-contest-standings.json');
        $problem = $standings['problems'][0];
        $problemDto = new CodeforcesProblemDTO(
            contestId: isset($problem['contestId']) ? (string) $problem['contestId'] : null,
            index: isset($problem['index']) ? (string) $problem['index'] : null,
            name: $problem['name'] ?? null,
            type: $problem['type'] ?? null,
            points: isset($problem['points']) ? (int) $problem['points'] : null,
            rating: isset($problem['rating']) ? (int) $problem['rating'] : null,
            tags: is_array($problem['tags'] ?? null) ? $problem['tags'] : [],
            raw: $problem,
        );
        $dto = (new ProblemTransformer())->fromApiProblem($problemDto);

        $this->assertSame('codeforces', $dto->platform);
        $this->assertSame('2225A', $dto->platformProblemId);
        $this->assertSame('A Number Between Two Others', $dto->title);
        $this->assertSame('2225', $dto->contestPlatformId);
        $this->assertSame(['greedy', 'math'], $dto->tags);
        $this->assertSame($standings['problems'][0], $problemDto->raw);
    }
}
