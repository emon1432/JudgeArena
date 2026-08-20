<?php

namespace App\Platforms\AtCoder\Transformers;

use App\Core\DTOs\UserDTO;
use App\Platforms\AtCoder\DTOs\AtCoderUserDTO;

class UserTransformer
{
    /** @return UserDTO */
    public function fromApiUser(AtCoderUserDTO $user): UserDTO
    {
        return new UserDTO(
            platform: 'atcoder',
            platformHandle: (string) ($user->username ?? ''),
            firstName: null,
            lastName: null,
            rating: $this->extractRating($user->contestStatus),
            country: $user->country,
            raw: $user->raw,
        );
    }

    /**
     * @param array<string, mixed>|null $contestStatus
     */
    private function extractRating(?array $contestStatus): ?int
    {
        if ($contestStatus === null) {
            return null;
        }

        $algoRating = $this->parseRatingValue($contestStatus['algo']['rating'] ?? null);
        if ($algoRating !== null) {
            return $algoRating;
        }

        return $this->parseRatingValue($contestStatus['heuristic']['rating'] ?? null);
    }

    private function parseRatingValue(mixed $rating): ?int
    {
        if (is_int($rating)) {
            return $rating;
        }

        if (is_float($rating)) {
            return (int) $rating;
        }

        if (! is_string($rating) || $rating === '') {
            return null;
        }

        if (preg_match('/-?\d+/', $rating, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }
}
