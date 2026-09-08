<?php

namespace App\Core\DTOs;

readonly class ContestStandingsDTO
{
    /**
     * @param  ProblemDTO[]  $problems
     * @param  ParticipantDTO[]  $rows
     */
    public function __construct(
        public ContestDTO $contest,
        public array $problems = [],
        public array $rows = [],
        public array $raw = [],
    ) {}
}
