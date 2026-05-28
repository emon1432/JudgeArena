<?php

namespace App\Platforms\Codeforces\Mappers;

use App\Platforms\Codeforces\DTOs\CodeforcesProblemDTO;
use App\Platforms\Codeforces\Support\ResponseNormalizer;

final class CodeforcesProblemMapper
{
    public static function fromNormalized(?array $problem): ?CodeforcesProblemDTO
    {
        if ($problem === null) {
            return null;
        }

        $normalized = ResponseNormalizer::problem($problem);

        return new CodeforcesProblemDTO(
            contestId: isset($normalized['contestId']) ? (string) $normalized['contestId'] : null,
            problemsetName: $normalized['problemsetName'] ?? null,
            index: isset($normalized['index']) ? (string) $normalized['index'] : null,
            name: $normalized['name'] ?? null,
            type: $normalized['type'] ?? null,
            points: isset($normalized['points']) ? (int) $normalized['points'] : null,
            rating: isset($normalized['rating']) ? (int) $normalized['rating'] : null,
            tags: is_array($normalized['tags'] ?? null) ? $normalized['tags'] : [],
            raw: $problem,
        );
    }

    /** @return array<int, CodeforcesProblemDTO> */
    public static function fromNormalizedList(array $problems): array
    {
        return array_map(fn (array $problem): CodeforcesProblemDTO => self::fromNormalized($problem), $problems);
    }
}
