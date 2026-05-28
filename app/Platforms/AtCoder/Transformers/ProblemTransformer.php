<?php

namespace App\Platforms\AtCoder\Transformers;

use App\Core\DTOs\ProblemDTO;
use App\Platforms\AtCoder\DTOs\AtCoderProblemDTO;

class ProblemTransformer
{
    /** @return ProblemDTO */
    public function fromApiProblem(AtCoderProblemDTO $problem): ProblemDTO
    {
        return new ProblemDTO(
            platform: 'atcoder',
            platformProblemId: (string) ($problem->id ?? ''),
            title: (string) ($problem->fullTitle ?? $problem->title ?? ''),
            contestPlatformId: $problem->contestId,
            rating: null,
            tags: [],
            raw: $problem->raw,
        );
    }

    /** @return array<int, ProblemDTO> */
    public function fromApiProblems(array $problems): array
    {
        return array_map(fn (AtCoderProblemDTO $problem): ProblemDTO => $this->fromApiProblem($problem), $problems);
    }
}

