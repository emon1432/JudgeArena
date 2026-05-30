<?php

namespace App\Platforms\AtCoder\Mappers;

use App\Platforms\AtCoder\DTOs\AtCoderProblemDTO;

final class AtCoderProblemMapper
{
    public static function fromNormalized(?array $problem): ?AtCoderProblemDTO
    {
        if ($problem === null) {
            return null;
        }

        return new AtCoderProblemDTO(
            id: isset($problem['id']) ? (string) $problem['id'] : null,
            contestId: isset($problem['contestId']) ? (string) $problem['contestId'] : null,
            title: $problem['title'] ?? null,
            position: $problem['position'] ?? null,
            fullTitle: $problem['fullTitle'] ?? null,
            points: isset($problem['score']) && is_numeric($problem['score']) ? (float) $problem['score'] : null,
            timeLimit: $problem['timeLimit'] ?? null,
            memoryLimit: $problem['memoryLimit'] ?? null,
            url: $problem['url'] ?? null,
            raw: $problem,
        );
    }

    /** @return array<int, AtCoderProblemDTO> */
    public static function fromNormalizedList(array $problems): array
    {
        return array_map(fn (array $problem): AtCoderProblemDTO => self::fromNormalized($problem), $problems);
    }
}

