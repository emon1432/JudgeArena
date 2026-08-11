<?php

namespace App\Platforms\Codeforces\Mappers;

use App\Platforms\Codeforces\DTOs\CodeforcesContestDTO;

final class CodeforcesContestMapper
{
    public static function fromNormalized(array $contest): CodeforcesContestDTO
    {
        return new CodeforcesContestDTO(
            id: isset($contest['id']) ? (string) $contest['id'] : null,
            name: $contest['name'] ?? null,
            type: $contest['type'] ?? null,
            phase: $contest['phase'] ?? null,
            frozen: array_key_exists('frozen', $contest) ? (bool) $contest['frozen'] : null,
            durationSeconds: isset($contest['durationSeconds']) ? (int) $contest['durationSeconds'] : null,
            freezeDurationSeconds: isset($contest['freezeDurationSeconds']) ? (int) $contest['freezeDurationSeconds'] : null,
            startTimeSeconds: isset($contest['startTimeSeconds']) ? (int) $contest['startTimeSeconds'] : null,
            relativeTimeSeconds: isset($contest['relativeTimeSeconds']) ? (int) $contest['relativeTimeSeconds'] : null,
            preparedBy: $contest['preparedBy'] ?? null,
            url: isset($contest['id']) ? config('platforms.codeforces.base_url') . (($contest['phase'] ?? '') === 'BEFORE' ? 'contestRegistration/' : 'contest/') . $contest['id'] : null,
            description: $contest['description'] ?? null,
            difficulty: isset($contest['difficulty']) ? (string) $contest['difficulty'] : null,
            kind: $contest['kind'] ?? null,
            icpcRegion: $contest['icpcRegion'] ?? null,
            country: $contest['country'] ?? null,
            city: $contest['city'] ?? null,
            season: $contest['season'] ?? null,
            raw: $contest,
        );
    }

    /** @return array<int, CodeforcesContestDTO> */
    public static function fromNormalizedList(array $contests): array
    {
        return array_map(fn(array $contest): CodeforcesContestDTO => self::fromNormalized($contest), $contests);
    }
}
