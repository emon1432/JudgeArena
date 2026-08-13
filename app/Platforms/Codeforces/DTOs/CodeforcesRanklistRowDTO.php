<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\DTOs;

readonly class CodeforcesRanklistRowDTO
{
    /**
     * @param CodeforcesProblemResultDTO[] $problemResults
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public ?CodeforcesPartyDTO $party,
        public ?int $rank,
        public ?int $points,
        public ?int $penalty,
        public ?int $successfulHackCount,
        public ?int $unsuccessfulHackCount,
        public array $problemResults,
        public ?int $lastSubmissionTimeSeconds,
        public array $raw,
    ) {}
}

