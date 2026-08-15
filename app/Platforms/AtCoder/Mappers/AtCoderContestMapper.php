<?php

namespace App\Platforms\AtCoder\Mappers;

use App\Platforms\AtCoder\DTOs\AtCoderContestDTO;

final class AtCoderContestMapper
{
    public static function fromNormalized(array $contest): AtCoderContestDTO
    {
        return new AtCoderContestDTO(
            id: isset($contest['id']) ? (string) $contest['id'] : null,
            title: $contest['title'] ?? null,
            url: $contest['url'] ?? null,
            date: $contest['date'] ?? null,
            duration: $contest['duration'] ?? null,
            rateChange: $contest['rate_change'] ?? $contest['rateChange'] ?? null,
            type: $contest['type'] ?? null,
            raw: $contest,
        );
    }

    /** @return array<int, AtCoderContestDTO> */
    public static function fromNormalizedList(array $contests): array
    {
        return array_map(fn (array $contest): AtCoderContestDTO => self::fromNormalized($contest), $contests);
    }
}

