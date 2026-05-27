<?php

namespace App\Platforms\Codeforces\DTOs;

readonly class CodeforcesProblemResultDTO
{
    public function __construct(
        public ?float $points,
        public ?int $penalty,
        public ?int $rejectedAttemptCount,
        public ?string $type,
        public ?int $bestSubmissionTimeSeconds,
    ) {}
}
