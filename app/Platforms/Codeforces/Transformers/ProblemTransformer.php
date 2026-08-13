<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\ProblemDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesProblemDTO;

class ProblemTransformer
{
    public function fromApiProblem(CodeforcesProblemDTO $problem): ProblemDTO
    {
        return new ProblemDTO(
            platform: 'codeforces',
            platformProblemId: $this->buildProblemId($problem),
            title: (string) ($problem->name ?? ''),
            contestPlatformId: $problem->contestId,
            code: $problem->index,
            points: $problem->points,
            rating: $problem->rating,
            solvedCount: $problem->solvedCount,
            tags: $problem->tags,
            url: $this->buildProblemUrl($problem),
            raw: $problem->raw,
        );
    }

    public function fromApiProblems(array $problems): array
    {
        return array_map(fn(CodeforcesProblemDTO $problem): ProblemDTO => $this->fromApiProblem($problem), $problems);
    }

    private function buildProblemId(CodeforcesProblemDTO $problem): string
    {
        $contestId = (string) ($problem->contestId ?? '0');
        $index = strtoupper(trim((string) ($problem->index ?? '')));

        return $contestId . $index;
    }

    private function buildProblemUrl(CodeforcesProblemDTO $problem): ?string
    {
        if (isset($problem->contestId) && isset($problem->index)) {
            return "https://codeforces.com/contest/{$problem->contestId}/problem/{$problem->index}";
        }

        return null;
    }
}

