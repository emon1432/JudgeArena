<?php

namespace App\Platforms\Codeforces\Mappers;

use App\Platforms\Codeforces\DTOs\CodeforcesContestDTO;
use App\Platforms\Codeforces\Support\ResponseNormalizer;

final class CodeforcesContestMapper
{
    public static function fromNormalized(array $contest): CodeforcesContestDTO
    {
        $normalized = ResponseNormalizer::contest($contest);

        return new CodeforcesContestDTO(
            id: isset($normalized['id']) ? (string) $normalized['id'] : null,
            name: $normalized['name'] ?? null,
            type: $normalized['type'] ?? null,
            phase: $normalized['phase'] ?? null,
            frozen: array_key_exists('frozen', $normalized) ? (bool) $normalized['frozen'] : null,
            durationSeconds: isset($normalized['durationSeconds']) ? (int) $normalized['durationSeconds'] : null,
            freezeDurationSeconds: isset($normalized['freezeDurationSeconds']) ? (int) $normalized['freezeDurationSeconds'] : null,
            startTimeSeconds: isset($normalized['startTimeSeconds']) ? (int) $normalized['startTimeSeconds'] : null,
            relativeTimeSeconds: isset($normalized['relativeTimeSeconds']) ? (int) $normalized['relativeTimeSeconds'] : null,
            preparedBy: $normalized['preparedBy'] ?? null,
            websiteUrl: $normalized['websiteUrl'] ?? null,
            description: $normalized['description'] ?? null,
            difficulty: isset($normalized['difficulty']) ? (string) $normalized['difficulty'] : null,
            kind: $normalized['kind'] ?? null,
            icpcRegion: $normalized['icpcRegion'] ?? null,
            country: $normalized['country'] ?? null,
            city: $normalized['city'] ?? null,
            season: $normalized['season'] ?? null,
            raw: $contest,
        );
    }

    /** @return array<int, CodeforcesContestDTO> */
    public static function fromNormalizedList(array $contests): array
    {
        return array_map(fn(array $contest): CodeforcesContestDTO => self::fromNormalized($contest), $contests);
    }
}
