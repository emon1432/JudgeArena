<?php

namespace App\Platforms\Codeforces\DTOs;

readonly class CodeforcesRanklistRowDTO
{
    /**
     * @param CodeforcesProblemResultDTO[] $problemResults
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
