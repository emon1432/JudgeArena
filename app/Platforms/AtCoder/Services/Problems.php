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
     * @return array{problems: \App\Platforms\AtCoder\DTOs\AtCoderProblemDTO[], problemStatistics: array<int, array<string, mixed>>}
     */
    public function list(): array
    {
        $problems = [];

        foreach ($this->contests->list() as $contest) {
            $tasks = $this->contests->tasks((string) $contest->id);
            $problems = array_merge(
                $problems,
                AtCoderProblemMapper::fromNormalizedList(
                    ResponseNormalizer::problems($tasks)
                )
            );
        }

        return [
            'problems' => $problems,
            'problemStatistics' => [],
        ];
    }
}

