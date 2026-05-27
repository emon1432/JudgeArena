<?php

namespace App\Platforms\Codeforces\DTOs;

use App\Platforms\Codeforces\Support\ResponseNormalizer;

readonly class CodeforcesUserDTO
{
    public function __construct(
        public ?string $handle,
        public ?string $email,
        public ?string $vkId,
        public ?string $openId,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $country,
        public ?string $city,
        public ?string $organization,
        public ?int $contribution,
        public ?string $rank,
        public ?int $rating,
        public ?string $maxRank,
        public ?int $maxRating,
        public ?int $lastOnlineTimeSeconds,
        public ?int $registrationTimeSeconds,
        public ?int $friendOfCount,
        public ?string $avatar,
        public ?string $titlePhoto,
        public array $raw,
    ) {}

    public static function fromApiResponse(array $user): self
    {
        $raw = $user;
        $normalized = ResponseNormalizer::user($user);

        return new self(
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
            raw: $raw,
        );
    }

    /** @return array<int, self> */
    public static function fromApiResponses(array $users): array
    {
        return array_map(fn (array $user): self => self::fromApiResponse($user), $users);
    }
}
