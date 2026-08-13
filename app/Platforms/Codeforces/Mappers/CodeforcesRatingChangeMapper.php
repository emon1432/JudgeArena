<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Mappers;

use App\Platforms\Codeforces\DTOs\CodeforcesRatingChangeDTO;

final class CodeforcesRatingChangeMapper
{
    /**
     * @param array<int, array<string, mixed>> $normalizedList
     * @return CodeforcesRatingChangeDTO[]
     */
    public static function fromNormalizedList(array $normalizedList): array
    {
        return array_map([self::class, 'fromNormalized'], $normalizedList);
    }

    /**
     * @param array<string, mixed> $normalized
     */
    public static function fromNormalized(array $normalized): CodeforcesRatingChangeDTO
    {
        return new CodeforcesRatingChangeDTO(
            contestPlatformId: isset($normalized['contestId']) ? (string) $normalized['contestId'] : null,
            contestName: $normalized['contestName'] ?? null,
            handle: $normalized['handle'] ?? null,
            rank: $normalized['rank'] ?? null,
            ratingUpdateTimeSeconds: $normalized['ratingUpdateTimeSeconds'] ?? null,
            oldRating: $normalized['oldRating'] ?? null,
            newRating: $normalized['newRating'] ?? null,
            isRated: true,
            raw: $normalized,
        );
    }
}

