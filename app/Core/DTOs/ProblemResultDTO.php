<?php

namespace App\Core\DTOs;

readonly class ProblemResultDTO
{
    public function __construct(
        public ?float $points = null,
        public ?int $penalty = null,
        public ?int $rejectedAttemptCount = null,
        public ?string $type = null,
        public ?int $bestSubmissionTimeSeconds = null,
    ) {}
}
