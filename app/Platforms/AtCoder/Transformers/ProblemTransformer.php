<?php

namespace App\Platforms\AtCoder\Transformers;

use App\Core\DTOs\ProblemDTO;
use App\Platforms\AtCoder\DTOs\AtCoderProblemDTO;
use App\Platforms\AtCoder\Services\AtCoderCategoryTagService;
use App\Platforms\AtCoder\Services\AtCoderTitleTranslatorService;

class ProblemTransformer
{
    private readonly AtCoderCategoryTagService $categoryTagService;
    private readonly AtCoderTitleTranslatorService $translator;

    public function __construct(
        ?AtCoderCategoryTagService $categoryTagService = null,
        ?AtCoderTitleTranslatorService $translator = null
    ) {
        $this->categoryTagService = $categoryTagService ?? app(AtCoderCategoryTagService::class);
        $this->translator = $translator ?? app(AtCoderTitleTranslatorService::class);
    }

    public function fromApiProblem(AtCoderProblemDTO $problem): ProblemDTO
    {
        $problemId = (string) ($problem->id ?? '');
        $enriched = $this->categoryTagService->enrichProblem($problemId, null, []);
        $title = $this->translator->translate((string) ($problem->title ?? ''));

        return new ProblemDTO(
            platform: 'atcoder',
            platformProblemId: $problemId,
            title: $title,
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
