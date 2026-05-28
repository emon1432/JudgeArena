<?php

namespace App\Platforms\AtCoder\Mappers;

use App\Platforms\AtCoder\DTOs\AtCoderPartyDTO;

final class AtCoderPartyMapper
{
    public static function fromNormalized(?array $party): ?AtCoderPartyDTO
    {
        if ($party === null) {
            return null;
        }

        return new AtCoderPartyDTO(
            contestId: isset($party['contestId']) ? (string) $party['contestId'] : null,
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

