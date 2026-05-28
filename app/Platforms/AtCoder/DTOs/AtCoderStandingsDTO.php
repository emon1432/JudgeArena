<?php

namespace App\Platforms\AtCoder\DTOs;

readonly class AtCoderStandingsDTO
{
    /**
     * @param AtCoderProblemDTO[] $problems
     * @param AtCoderRanklistRowDTO[] $rows
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public AtCoderContestDTO $contest,
        public array $problems,
        public array $rows,
        public array $raw,
    ) {}
}

