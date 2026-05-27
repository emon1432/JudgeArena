<?php

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\ProblemDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesProblemDTO;

class ProblemTransformer
{
    /**
     * @param CodeforcesProblemDTO|array<string, mixed> $problem
     */
    public function fromApiProblem(CodeforcesProblemDTO|array $problem): ProblemDTO
    {
        $dto = $problem instanceof CodeforcesProblemDTO
            ? $problem
            : CodeforcesProblemDTO::fromApiResponse($problem);

        return new ProblemDTO(
            platform: 'codeforces',
            platformProblemId: $this->buildProblemId($dto),
            title: (string) ($dto->name ?? ''),
            contestPlatformId: $dto->contestId,
            rating: $dto->rating,
            tags: $dto->tags,
            raw: $dto->raw,
        );
    }

    /** @return array<int, ProblemDTO> */
    public function fromApiProblems(array $problems): array
    {
        return array_map(fn (CodeforcesProblemDTO|array $problem): ProblemDTO => $this->fromApiProblem($problem), $problems);
    }

    private function buildProblemId(CodeforcesProblemDTO $problem): string
    {
        $contestId = (string) ($problem->contestId ?? '0');
        $index = strtoupper(trim((string) ($problem->index ?? '')));

        return $contestId . $index;
    }
}
