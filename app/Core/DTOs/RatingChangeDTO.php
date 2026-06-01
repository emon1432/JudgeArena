<?php

namespace App\Core\DTOs;

readonly class RatingChangeDTO
{
    public function __construct(
        public string $platform,
        public string $contestPlatformId,
        public string $handle,
        public ?bool $isRated = null,
        public ?int $rank = null,
        public ?int $oldRating = null,
        public ?int $newRating = null,
        public ?int $ratingChange = null,
        public ?int $performance = null,
        public array $metadata = [],
        public array $raw = [],
    ) {
    }
}
