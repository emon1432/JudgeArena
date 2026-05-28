<?php

namespace App\Platforms\Codeforces\DTOs;

readonly class CodeforcesUserDTO
{
    /**
     * @param array<string, mixed> $raw
     */
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
}
