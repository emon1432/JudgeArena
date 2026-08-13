<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\DTOs;

readonly class CodeforcesContestDTO
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public ?string $id,
        public ?string $name,
        public ?string $type,
        public ?string $phase,
        public ?bool $frozen,
        public ?int $durationSeconds,
        public ?int $freezeDurationSeconds,
        public ?int $startTimeSeconds,
        public ?int $relativeTimeSeconds,
        public ?string $preparedBy,
        public ?string $url,
        public ?string $description,
        public ?string $difficulty,
        public ?string $kind,
        public ?string $icpcRegion,
        public ?string $country,
        public ?string $city,
        public ?string $season,
        public array $raw,
    ) {
    }
}

