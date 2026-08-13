<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Mappers;

use App\Platforms\Codeforces\DTOs\CodeforcesPartyDTO;

final class CodeforcesPartyMapper
{
    public static function fromNormalized(?array $party): ?CodeforcesPartyDTO
    {
        if ($party === null) {
            return null;
        }

        return new CodeforcesPartyDTO(
            contestId: isset($party['contestId']) ? (int) $party['contestId'] : null,
            members: is_array($party['members'] ?? null) ? $party['members'] : [],
            participantType: $party['participantType'] ?? null,
            teamId: isset($party['teamId']) ? (int) $party['teamId'] : null,
            teamName: $party['teamName'] ?? null,
            ghost: array_key_exists('ghost', $party) ? (bool) $party['ghost'] : null,
            room: isset($party['room']) ? (int) $party['room'] : null,
            startTimeSeconds: isset($party['startTimeSeconds']) ? (int) $party['startTimeSeconds'] : null,
            raw: $party,
        );
    }
}

