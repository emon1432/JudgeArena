<?php

namespace App\Platforms\AtCoder\DTOs;

readonly class AtCoderUserDTO
{
    /**
     * @param array<string, mixed>|null $contestStatus
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public ?string $username,
        public ?string $avatarUrl,
        public ?string $country,
        public ?string $birthYear,
        public ?string $twitterId,
        public ?string $topcoderId,
        public ?string $codeforcesId,
        public ?string $affiliation,
        public ?array $contestStatus,
        public array $raw,
    ) {}
}

