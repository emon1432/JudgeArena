<?php

namespace App\Platforms\AtCoder\Transformers;

use App\Core\DTOs\UserDTO;
use App\Platforms\AtCoder\DTOs\AtCoderUserDTO;

class UserTransformer
{
    /** @return UserDTO */
    public function fromApiUser(AtCoderUserDTO $user): UserDTO
    {
        $rating = $this->extractRating($user->contestStatus);

        $raw = $user->raw;
        $raw['rating'] = $rating;
        $raw['accepted_count'] = $user->acceptedCount;
        $raw['accepted_count_rank'] = $user->acceptedCountRank;
        $raw['rated_point_sum'] = $user->ratedPointSum;
        $raw['rated_point_sum_rank'] = $user->ratedPointSumRank;

        return new UserDTO(
            platform: 'atcoder',
            platformHandle: (string) ($user->username ?? ''),
            firstName: null,
            lastName: null,
            rating: $rating,
            country: $user->country,
            raw: $raw,
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
