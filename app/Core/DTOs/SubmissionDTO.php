<?php

namespace App\Core\DTOs;

readonly class SubmissionDTO
{
    public function __construct(
        public string $platform,
        public string $platformSubmissionId,
        public string $problemPlatformId,
        public string $authorHandle,
        public ?string $verdict = null,
        public ?string $language = null,
        public ?int $passedTestCount = null,
        public ?int $timeConsumedMillis = null,
        public ?int $createdAtSeconds = null,
        public array $raw = [],
        public ?string $contestPlatformId = null,
        public ?float $points = null,
        public ?string $testset = null,
        public ?int $memoryConsumedBytes = null,
    ) {
    }
}
