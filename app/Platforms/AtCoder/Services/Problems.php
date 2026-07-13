<?php

namespace App\Platforms\AtCoder\Services;

use App\Platforms\AtCoder\Mappers\AtCoderProblemMapper;
use App\Platforms\AtCoder\Support\ResponseNormalizer;

class Problems
{
    public function __construct(
        private readonly Contests $contestsService,
    ) {}

    //used
    public function getContestProblems(string $contestId): array
    {
        return AtCoderProblemMapper::fromNormalizedList(
            ResponseNormalizer::problems(
                $this->contestsService->tasks($contestId)
            )
        );
    }
}
