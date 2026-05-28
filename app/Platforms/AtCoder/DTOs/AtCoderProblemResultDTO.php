<?php

namespace App\Platforms\AtCoder\DTOs;

readonly class AtCoderProblemResultDTO
{
    public function __construct(
        public ?float $points,
        public ?int $penalty,
        public ?int $rejectedAttemptCount,
        public ?string $type,
        public ?int $bestSubmissionTimeSeconds,
    ) {}
}

