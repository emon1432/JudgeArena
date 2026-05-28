<?php

namespace App\Platforms\AtCoder\DTOs;

readonly class AtCoderRanklistRowDTO
{
    /**
     * @param AtCoderProblemResultDTO[] $problemResults
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public ?AtCoderPartyDTO $party,
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

