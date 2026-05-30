<?php

namespace App\Platforms\AtCoder\Services;

use App\Platforms\AtCoder\Mappers\AtCoderProblemMapper;
use App\Platforms\AtCoder\Support\ResponseNormalizer;

class Problems
{
    public function __construct(
        private readonly Contests $contests,
    ) {}

    /**
     * Legacy whole-platform problem crawl.
     *
     * Prefer {@see getContestProblems()} for contest-scoped synchronization.
     * This method remains for backward compatibility and should not be used
     * for large-scale sync runs.
     *
     * @return array{problems: \App\Platforms\AtCoder\DTOs\AtCoderProblemDTO[], problemStatistics: array<int, array<string, mixed>>}
     */
    public function list(): array
    {
        $problems = [];

        foreach ($this->contests->list() as $contest) {
            $contestProblems = $this->getContestProblems((string) $contest->id);
            $problems = array_merge($problems, $contestProblems['problems']);
        }

        return [
            'problems' => $problems,
            'problemStatistics' => [],
        ];
    }

    /**
     * Contest-scoped problem synchronization path.
     *
     * @return array{problems: \App\Platforms\AtCoder\DTOs\AtCoderProblemDTO[], problemStatistics: array<int, array<string, mixed>>}
     */
    public function getContestProblems(string $contestId): array
    {
        $tasks = $this->contests->tasks($contestId);

        return [
            'problems' => AtCoderProblemMapper::fromNormalizedList(
                ResponseNormalizer::problems($tasks)
            ),
            'problemStatistics' => [],
        ];
    }
}

