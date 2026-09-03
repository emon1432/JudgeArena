<?php

declare(strict_types=1);

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
        $rating = $problem->rating;
        $enriched = $this->categoryTagService->enrichProblem($problemId, $rating, []);

        $rawTitle = (string) ($problem->title ?? '');
        $cleanTitle = preg_replace('/^[A-Z1-9]\.\s*/', '', $rawTitle);
        $title = $this->translator->translate($cleanTitle);

        $finalRating = $enriched['rating'] ?? $rating;

        return new ProblemDTO(
            platform: 'atcoder',
            platformProblemId: $problemId,
            title: $title,
            contestPlatformId: $problem->contestId,
            code: $problem->position,
            points: $problem->points,
            rating: $finalRating,
            timeLimit: isset($problem->timeLimit) ? $this->parseTimeLimit($problem->timeLimit) : null,
            memoryLimit: isset($problem->memoryLimit) ? $this->parseMemoryLimit($problem->memoryLimit) : 1024,
            tags: $enriched['tags'],
            url: $problem->url,
            raw: $problem->raw,
            solvedCount: $problem->solverCount ?? 0,
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
