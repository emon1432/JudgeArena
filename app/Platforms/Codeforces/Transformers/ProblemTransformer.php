<?php

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\ProblemDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesProblemDTO;

class ProblemTransformer
{
    /** @return ProblemDTO */
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
            tags: $problem->tags,
            raw: $problem->raw,
            solvedCount: $problem->solvedCount,
        );
    }

    /** @return array<int, ProblemDTO> */
    public function fromApiProblems(array $problems): array
    {
        return array_map(fn (CodeforcesProblemDTO $problem): ProblemDTO => $this->fromApiProblem($problem), $problems);
    }

    private function buildProblemId(CodeforcesProblemDTO $problem): string
    {
        $contestId = (string) ($problem->contestId ?? '0');
        $index = strtoupper(trim((string) ($problem->index ?? '')));

        return $contestId . $index;
    }
}
