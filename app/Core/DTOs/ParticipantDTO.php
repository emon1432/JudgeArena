<?php

namespace App\Core\DTOs;

readonly class ParticipantDTO
{
    /**
     * @param ProblemResultDTO[] $problemResults
     */
    public function __construct(
        public int $rank,
        public ?int $points = null,
        public ?int $penalty = null,
        public array $members = [],
        public array $problemResults = [],
        public array $raw = [],
    ) {}
}
