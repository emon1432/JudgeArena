<?php

namespace App\Platforms\AtCoder\Mappers;

use App\Platforms\AtCoder\DTOs\AtCoderUserDTO;

final class AtCoderUserMapper
{
    public static function fromNormalized(array $user): AtCoderUserDTO
    {
        return new AtCoderUserDTO(
            username: $user['username'] ?? null,
            avatarUrl: $user['avatarUrl'] ?? null,
            country: $user['country'] ?? null,
            birthYear: $user['birthYear'] ?? null,
            twitterId: $user['twitterId'] ?? null,
            topcoderId: $user['topcoderId'] ?? null,
            codeforcesId: $user['codeforcesId'] ?? null,
            affiliation: $user['affiliation'] ?? null,
            contestStatus: is_array($user['contestStatus'] ?? null) ? $user['contestStatus'] : null,
            raw: $user,
        );
    }
}

