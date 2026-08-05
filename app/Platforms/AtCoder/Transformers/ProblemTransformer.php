<?php

namespace App\Platforms\AtCoder\Transformers;

use App\Core\DTOs\ProblemDTO;
use App\Platforms\AtCoder\DTOs\AtCoderProblemDTO;

class ProblemTransformer
{
    public function fromApiProblem(AtCoderProblemDTO $problem): ProblemDTO
    {
        return new ProblemDTO(
            platform: 'atcoder',
            platformProblemId: (string) ($problem->id ?? ''),
            title: (string) ($problem->title ?? ''),
            contestPlatformId: $problem->contestId,
            code: $problem->position,
            points: $problem->points,
            timeLimit: isset($problem->timeLimit) ? $this->parseTimeLimit($problem->timeLimit) : null,
            memoryLimit: isset($problem->memoryLimit) ? $this->parseMemoryLimit($problem->memoryLimit) : null,
            url: $problem->url,
            tags: [],
            raw: $problem->raw,
        );
    }

    public function fromApiProblems(array $problems): array
    {
        return array_map(fn(AtCoderProblemDTO $problem): ProblemDTO => $this->fromApiProblem($problem), $problems);
    }

    private function parseTimeLimit(string $timeLimit): ?int
    {
        //timeLimit is a string like "2 sec" or "1000 ms"
        //we convert it to milliseconds in integer format
        if (str_ends_with($timeLimit, 'sec')) {
            $seconds = floatval(str_replace('sec', '', $timeLimit));
            return (int) ($seconds * 1000);
        } elseif (str_ends_with($timeLimit, 'ms')) {
            return (int) str_replace('ms', '', $timeLimit);
        }

        return null;
    }

    private function parseMemoryLimit(string $memoryLimit): ?int
    {
        //memoryLimit is a string like "256 MiB"
        //we convert it to megabytes in integer format
        if (str_ends_with($memoryLimit, 'MiB')) {
            return (int) str_replace('MiB', '', $memoryLimit);
        }

        return null;
    }
}

