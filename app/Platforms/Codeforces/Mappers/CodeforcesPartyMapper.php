<?php

namespace App\Platforms\Codeforces\Mappers;

use App\Platforms\Codeforces\DTOs\CodeforcesPartyDTO;
use App\Platforms\Codeforces\Support\ResponseNormalizer;

final class CodeforcesPartyMapper
{
    public static function fromNormalized(?array $party): ?CodeforcesPartyDTO
    {
        if ($party === null) {
            return null;
        }

        $normalized = ResponseNormalizer::party($party);

        return new CodeforcesPartyDTO(
            contestId: isset($normalized['contestId']) ? (int) $normalized['contestId'] : null,
            members: is_array($normalized['members'] ?? null) ? $normalized['members'] : [],
            participantType: $normalized['participantType'] ?? null,
            teamId: isset($normalized['teamId']) ? (int) $normalized['teamId'] : null,
            teamName: $normalized['teamName'] ?? null,
            ghost: array_key_exists('ghost', $normalized) ? (bool) $normalized['ghost'] : null,
            room: isset($normalized['room']) ? (int) $normalized['room'] : null,
            startTimeSeconds: isset($normalized['startTimeSeconds']) ? (int) $normalized['startTimeSeconds'] : null,
            raw: $party,
        );
    }
}
