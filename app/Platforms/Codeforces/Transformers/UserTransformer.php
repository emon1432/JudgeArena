<?php

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\UserDTO;
use App\Platforms\Codeforces\Support\ResponseNormalizer;

class UserTransformer
{
    public function fromApiUser(array $user): UserDTO
    {
        $normalized = ResponseNormalizer::user($user);

        return new UserDTO(
            platform: 'codeforces',
            platformHandle: (string) ($normalized['handle'] ?? ''),
            firstName: $normalized['firstName'] ?? null,
            lastName: $normalized['lastName'] ?? null,
            rating: isset($normalized['rating']) ? (int) $normalized['rating'] : null,
            country: $normalized['country'] ?? null,
            raw: $normalized,
        );
    }
}
