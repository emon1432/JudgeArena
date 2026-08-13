<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Mappers;

use App\Platforms\Codeforces\DTOs\CodeforcesUserDTO;

final class CodeforcesUserMapper
{
    public static function fromNormalized(array $user): CodeforcesUserDTO
    {
        return new CodeforcesUserDTO(
            handle: $user['handle'] ?? null,
            email: $user['email'] ?? null,
            vkId: $user['vkId'] ?? null,
            openId: $user['openId'] ?? null,
            firstName: $user['firstName'] ?? null,
            lastName: $user['lastName'] ?? null,
            country: $user['country'] ?? null,
            city: $user['city'] ?? null,
            organization: $user['organization'] ?? null,
            contribution: isset($user['contribution']) ? (int) $user['contribution'] : null,
            rank: $user['rank'] ?? null,
            rating: isset($user['rating']) ? (int) $user['rating'] : null,
            maxRank: $user['maxRank'] ?? null,
            maxRating: isset($user['maxRating']) ? (int) $user['maxRating'] : null,
            lastOnlineTimeSeconds: isset($user['lastOnlineTimeSeconds']) ? (int) $user['lastOnlineTimeSeconds'] : null,
            registrationTimeSeconds: isset($user['registrationTimeSeconds']) ? (int) $user['registrationTimeSeconds'] : null,
            friendOfCount: isset($user['friendOfCount']) ? (int) $user['friendOfCount'] : null,
            avatar: $user['avatar'] ?? null,
            titlePhoto: $user['titlePhoto'] ?? null,
            raw: $user,
        );
    }

    /** @return array<int, CodeforcesUserDTO> */
    public static function fromNormalizedList(array $users): array
    {
        return array_map(fn(array $user): CodeforcesUserDTO => self::fromNormalized($user), $users);
    }
}

