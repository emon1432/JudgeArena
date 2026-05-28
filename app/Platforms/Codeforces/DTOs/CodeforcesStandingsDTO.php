<?php

namespace App\Platforms\Codeforces\DTOs;

readonly class CodeforcesStandingsDTO
{
    /**
     * @param CodeforcesProblemDTO[] $problems
     * @param CodeforcesRanklistRowDTO[] $rows
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public ?CodeforcesContestDTO $contest,
        public array $problems,
        public array $rows,
        public array $raw,
    ) {}
}
