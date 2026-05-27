<?php

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\UserDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesUserDTO;

class UserTransformer
{
    public function fromApiUser(CodeforcesUserDTO $user): UserDTO
    {
        return new UserDTO(
            platform: 'codeforces',
            platformHandle: (string) ($user->handle ?? ''),
            firstName: $user->firstName,
            lastName: $user->lastName,
            rating: $user->rating,
            country: $user->country,
            raw: $user->raw,
        );
    }
}
