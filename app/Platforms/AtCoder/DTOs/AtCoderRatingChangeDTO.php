<?php

namespace App\Platforms\AtCoder\DTOs;

readonly class AtCoderRatingChangeDTO
{
    public function __construct(
        public ?bool $isRated = null,
        public ?int $place = null,
        public ?int $oldRating = null,
        public ?int $newRating = null,
        public ?int $performance = null,
        public ?int $innerPerformance = null,
        public ?string $contestName = null,
        public ?string $contestNameEn = null,
        public ?string $contestScreenName = null,
        public ?string $endTime = null,
        public ?string $contestType = null,
        public ?string $userName = null,
        public ?string $userScreenName = null,
        public ?string $country = null,
        public ?string $affiliation = null,
        public ?int $rating = null,
        public ?int $competitions = null,
        public ?string $atCoderRank = null,
        public array $raw = [],
    ) {
    }
}
