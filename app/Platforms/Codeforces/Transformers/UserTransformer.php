<?php

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\UserDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesUserDTO;

class UserTransformer
{
    /**
     * @param CodeforcesUserDTO|array<string, mixed> $user
     */
    public function fromApiUser(CodeforcesUserDTO|array $user): UserDTO
    {
        $dto = $user instanceof CodeforcesUserDTO
            ? $user
            : CodeforcesUserDTO::fromApiResponse($user);

        return new UserDTO(
            platform: 'codeforces',
            platformHandle: (string) ($dto->handle ?? ''),
            firstName: $dto->firstName,
            lastName: $dto->lastName,
            rating: $dto->rating,
            country: $dto->country,
            raw: $dto->raw,
        );
    }
}
