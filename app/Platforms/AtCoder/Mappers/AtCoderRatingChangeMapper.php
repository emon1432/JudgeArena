<?php

namespace App\Platforms\AtCoder\Mappers;

use App\Platforms\AtCoder\DTOs\AtCoderRatingChangeDTO;

final class AtCoderRatingChangeMapper
{
    /**
     * @param array<int, array<string, mixed>> $normalizedList
     * @return AtCoderRatingChangeDTO[]
     */
    public static function fromNormalizedList(array $normalizedList): array
    {
        return array_map([self::class, 'fromNormalized'], $normalizedList);
    }

    /**
     * @param array<string, mixed> $normalized
     */
    public static function fromNormalized(array $normalized): AtCoderRatingChangeDTO
    {
        return new AtCoderRatingChangeDTO(
            contestPlatformId: (string) explode('.', $normalized['contestScreenName'] ?? '')[0] ?? '',
            isRated: $normalized['isRated'] ?? null,
            place: $normalized['place'] ?? null,
            oldRating: $normalized['oldRating'] ?? null,
            newRating: $normalized['newRating'] ?? null,
            performance: $normalized['performance'] ?? null,
            innerPerformance: $normalized['innerPerformance'] ?? null,
            contestName: $normalized['contestName'] ?? null,
            contestNameEn: $normalized['contestNameEn'] ?? null,
            contestScreenName: $normalized['contestScreenName'] ?? null,
            endTime: $normalized['endTime'] ?? null,
            contestType: $normalized['contestType'] ?? null,
            userName: $normalized['userName'] ?? null,
            userScreenName: $normalized['userScreenName'] ?? null,
            country: $normalized['country'] ?? null,
            affiliation: $normalized['affiliation'] ?? null,
            rating: $normalized['rating'] ?? null,
            competitions: $normalized['competitions'] ?? null,
            atCoderRank: $normalized['atCoderRank'] ?? null,
            raw: $normalized,
        );
    }
}
