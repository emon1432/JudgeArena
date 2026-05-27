<?php

namespace App\Platforms\Codeforces\Mappers;

use App\Platforms\Codeforces\DTOs\CodeforcesUserDTO;
use App\Platforms\Codeforces\Support\ResponseNormalizer;

final class CodeforcesUserMapper
{
    public static function fromNormalized(array $user): CodeforcesUserDTO
    {
        $normalized = ResponseNormalizer::user($user);

        return new CodeforcesUserDTO(
            handle: $normalized['handle'] ?? null,
            email: $normalized['email'] ?? null,
            vkId: $normalized['vkId'] ?? null,
            openId: $normalized['openId'] ?? null,
            firstName: $normalized['firstName'] ?? null,
            lastName: $normalized['lastName'] ?? null,
            country: $normalized['country'] ?? null,
            city: $normalized['city'] ?? null,
            organization: $normalized['organization'] ?? null,
            contribution: isset($normalized['contribution']) ? (int) $normalized['contribution'] : null,
            rank: $normalized['rank'] ?? null,
            rating: isset($normalized['rating']) ? (int) $normalized['rating'] : null,
            maxRank: $normalized['maxRank'] ?? null,
            maxRating: isset($normalized['maxRating']) ? (int) $normalized['maxRating'] : null,
            lastOnlineTimeSeconds: isset($normalized['lastOnlineTimeSeconds']) ? (int) $normalized['lastOnlineTimeSeconds'] : null,
            registrationTimeSeconds: isset($normalized['registrationTimeSeconds']) ? (int) $normalized['registrationTimeSeconds'] : null,
            friendOfCount: isset($normalized['friendOfCount']) ? (int) $normalized['friendOfCount'] : null,
            avatar: $normalized['avatar'] ?? null,
            titlePhoto: $normalized['titlePhoto'] ?? null,
            raw: $user,
        );
    }

    /** @return array<int, CodeforcesUserDTO> */
    public static function fromNormalizedList(array $users): array
    {
        return array_map(fn(array $user): CodeforcesUserDTO => self::fromNormalized($user), $users);
    }
}
