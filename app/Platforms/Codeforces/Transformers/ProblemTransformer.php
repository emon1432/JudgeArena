<?php

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\ProblemDTO;
use App\Platforms\Codeforces\Support\ResponseNormalizer;

class ProblemTransformer
{
    public function fromApiProblem(array $problem): ProblemDTO
    {
        $normalized = ResponseNormalizer::problem($problem);

        return new ProblemDTO(
            platform: 'codeforces',
            platformProblemId: $this->buildProblemId($normalized),
            title: (string) ($normalized['name'] ?? ''),
            contestPlatformId: isset($normalized['contestId']) ? (string) $normalized['contestId'] : null,
            rating: isset($normalized['rating']) ? (int) $normalized['rating'] : null,
            tags: is_array($normalized['tags'] ?? null) ? $normalized['tags'] : [],
            raw: $normalized,
        );
    }

    /** @return array<int, ProblemDTO> */
    public function fromApiProblems(array $problems): array
    {
        return array_map(fn (array $problem): ProblemDTO => $this->fromApiProblem($problem), $problems);
    }

    private function buildProblemId(array $problem): string
    {
        $contestId = (string) ($problem['contestId'] ?? '0');
        $index = strtoupper(trim((string) ($problem['index'] ?? '')));

        return $contestId . $index;
    }
}
