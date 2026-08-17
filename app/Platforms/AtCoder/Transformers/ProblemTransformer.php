<?php

namespace App\Platforms\AtCoder\Transformers;

use App\Core\DTOs\ProblemDTO;
use App\Platforms\AtCoder\DTOs\AtCoderProblemDTO;
use App\Platforms\AtCoder\Services\AtCoderCategoryTagService;

class ProblemTransformer
{
    private readonly AtCoderCategoryTagService $categoryTagService;

    public function __construct(
        ?AtCoderCategoryTagService $categoryTagService = null
    ) {
        $this->categoryTagService = $categoryTagService ?? app(AtCoderCategoryTagService::class);
    }

    public function fromApiProblem(AtCoderProblemDTO $problem): ProblemDTO
    {
        $problemId = (string) ($problem->id ?? '');
        $enriched = $this->categoryTagService->enrichProblem($problemId, null, []);

        return new ProblemDTO(
            platform: 'atcoder',
            platformProblemId: $problemId,
            title: (string) ($problem->title ?? ''),
            contestPlatformId: $problem->contestId,
            code: $problem->position,
            points: $problem->points,
            rating: $enriched['rating'],
            timeLimit: isset($problem->timeLimit) ? $this->parseTimeLimit($problem->timeLimit) : null,
            memoryLimit: isset($problem->memoryLimit) ? $this->parseMemoryLimit($problem->memoryLimit) : null,
            tags: $enriched['tags'],
            url: $problem->url,
            raw: $problem->raw,
        );
    }

    public function fromApiProblems(array $problems): array
    {
        return array_map(fn(AtCoderProblemDTO $problem): ProblemDTO => $this->fromApiProblem($problem), $problems);
    }

    private function parseTimeLimit(string $timeLimit): ?int
    {
        if (preg_match('/([\d\.]+)\s*sec/i', $timeLimit, $matches)) {
            return (int) (floatval($matches[1]) * 1000);
        }
        if (preg_match('/(\d+)\s*ms/i', $timeLimit, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function parseMemoryLimit(string $memoryLimit): ?int
    {
        if (preg_match('/(\d+)\s*(MiB|MB)/i', $memoryLimit, $matches)) {
            return (int) $matches[1];
        }
        if (preg_match('/([\d\.]+)\s*GB/i', $memoryLimit, $matches)) {
            return (int) (floatval($matches[1]) * 1024);
        }

        return null;
    }
}

