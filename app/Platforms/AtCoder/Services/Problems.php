<?php

namespace App\Platforms\AtCoder\Services;

use App\Platforms\AtCoder\Mappers\AtCoderProblemMapper;
use App\Platforms\AtCoder\Support\ResponseNormalizer;

class Problems
{
    public function __construct(
        private readonly Contests $contestsService,
        private readonly AtCoderKenkooooService $kenkooooService,
        private readonly AtCoderReachabilityService $reachabilityService,
    ) {}

    //used
    public function getContestProblems(string $contestId): array
    {
        if (!$this->reachabilityService->isReachable()) {
            return AtCoderProblemMapper::fromNormalizedList(
                ResponseNormalizer::problems(
                    $this->kenkooooService->getContestProblems($contestId)
                )
            );
        }

        $tasks = $this->contestsService->tasks($contestId);
        if (empty($tasks)) {
            $tasks = $this->kenkooooService->getContestProblems($contestId);
        }

        return AtCoderProblemMapper::fromNormalizedList(
            ResponseNormalizer::problems($tasks)
        );
    }
}
