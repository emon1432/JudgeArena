<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\DTOs;

readonly class CodeforcesRatingChangeDTO
{
    public function __construct(
        public ?string $contestPlatformId = null,
        public ?string $contestName = null,
        public ?string $handle = null,
        public ?int $rank = null,
        public ?int $ratingUpdateTimeSeconds = null,
        public ?int $oldRating = null,
        public ?int $newRating = null,
        public ?bool $isRated = true,
        public array $raw = [],
    ) {}
}

