<?php

declare(strict_types=1);

namespace App\Platforms\AtCoder\Mappers;

use App\Platforms\AtCoder\DTOs\AtCoderContestDTO;

final class AtCoderContestMapper
{
    public static function fromNormalized(array $contest): AtCoderContestDTO
    {
        $id = isset($contest['id']) ? (string) $contest['id'] : null;
        $url = $contest['url'] ?? ($id !== null ? "https://atcoder.jp/contests/{$id}" : null);

        $startEpoch = isset($contest['start_epoch_second']) && is_numeric($contest['start_epoch_second'])
            ? (int) $contest['start_epoch_second']
            : null;

        $durationSec = isset($contest['duration_second']) && is_numeric($contest['duration_second'])
            ? (int) $contest['duration_second']
            : null;

        return new AtCoderContestDTO(
            id: $id,
            title: $contest['title'] ?? null,
            url: $url,
            date: $contest['date'] ?? null,
            duration: $contest['duration'] ?? null,
            rateChange: $contest['rate_change'] ?? $contest['rateChange'] ?? null,
            type: $contest['type'] ?? null,
            raw: $contest,
            startEpochSecond: $startEpoch,
            durationSecond: $durationSec,
        );
    }

    /** @return array<int, AtCoderContestDTO> */
    public static function fromNormalizedList(array $contests): array
    {
        return array_map(fn (array $contest): AtCoderContestDTO => self::fromNormalized($contest), $contests);
    }
}
