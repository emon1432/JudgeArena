<?php

namespace App\Platforms\Codeforces\Mappers;

use App\Platforms\Codeforces\DTOs\CodeforcesProblemDTO;

final class CodeforcesProblemMapper
{
    public static function fromNormalized(?array $problem): ?CodeforcesProblemDTO
    {
        if ($problem === null) {
            return null;
        }

        return new CodeforcesProblemDTO(
            contestId: isset($problem['contestId']) ? (string) $problem['contestId'] : null,
            problemsetName: $problem['problemsetName'] ?? null,
            index: isset($problem['index']) ? (string) $problem['index'] : null,
            name: $problem['name'] ?? null,
            type: $problem['type'] ?? null,
            points: isset($problem['points']) ? (int) $problem['points'] : null,
            rating: isset($problem['rating']) ? (int) $problem['rating'] : null,
            tags: is_array($problem['tags'] ?? null) ? $problem['tags'] : [],
            raw: $problem,
        );
    }

    /** @return array<int, CodeforcesProblemDTO> */
    public static function fromNormalizedList(array $problems): array
    {
        return array_map(fn (array $problem): CodeforcesProblemDTO => self::fromNormalized($problem), $problems);
    }
}
