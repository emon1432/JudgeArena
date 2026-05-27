<?php

namespace App\Core\DTOs;

readonly class ContestStandingsDTO
{
    /**
     * @param ContestDTO $contest
     * @param ProblemDTO[] $problems
     * @param ParticipantDTO[] $rows
     * @param array $raw
     */
    public function __construct(
        public ContestDTO $contest,
        public array $problems = [],
        public array $rows = [],
        public array $raw = [],
    ) {}
}
