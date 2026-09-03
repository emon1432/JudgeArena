<?php

declare(strict_types=1);

namespace App\Platforms\AtCoder\Mappers;

use App\Platforms\AtCoder\DTOs\AtCoderProblemDTO;

final class AtCoderProblemMapper
{
    public static function fromNormalized(?array $problem): ?AtCoderProblemDTO
    {
        if ($problem === null) {
            return null;
        }

        $id = isset($problem['id']) ? (string) $problem['id'] : null;
        $contestId = isset($problem['contestId']) ? (string) $problem['contestId'] : (isset($problem['contest_id']) ? (string) $problem['contest_id'] : null);
        $url = $problem['url'] ?? (($contestId !== null && $id !== null) ? "https://atcoder.jp/contests/{$contestId}/tasks/{$id}" : null);

        $points = null;
        if (isset($problem['score']) && is_numeric($problem['score'])) {
            $points = (float) $problem['score'];
        } elseif (isset($problem['point']) && is_numeric($problem['point'])) {
            $points = (float) $problem['point'];
        } elseif (isset($problem['points']) && is_numeric($problem['points'])) {
            $points = (float) $problem['points'];
        }

        $rating = null;
        if (isset($problem['rating']) && is_numeric($problem['rating'])) {
            $rating = (int) $problem['rating'];
        } elseif (isset($problem['difficulty']) && is_numeric($problem['difficulty'])) {
            $rating = (int) $problem['difficulty'];
        }

        $solverCount = null;
        if (isset($problem['solverCount']) && is_numeric($problem['solverCount'])) {
            $solverCount = (int) $problem['solverCount'];
        } elseif (isset($problem['solver_count']) && is_numeric($problem['solver_count'])) {
            $solverCount = (int) $problem['solver_count'];
        }

        return new AtCoderProblemDTO(
            id: $id,
            contestId: $contestId,
            title: $problem['title'] ?? ($problem['name'] ?? null),
            position: $problem['position'] ?? ($problem['problem_index'] ?? null),
            points: $points,
            rating: $rating,
            timeLimit: isset($problem['timeLimit']) ? (string) $problem['timeLimit'] : (isset($problem['time_limit']) ? (string) $problem['time_limit'] : (isset($problem['execution_time']) ? $problem['execution_time'] . ' ms' : null)),
            memoryLimit: isset($problem['memoryLimit']) ? (string) $problem['memoryLimit'] : (isset($problem['memory_limit']) ? (string) $problem['memory_limit'] : '1024 MB'),
            solverCount: $solverCount,
            url: $url,
            raw: $problem,
        );
    }

    /** @return array<int, AtCoderProblemDTO> */
    public static function fromNormalizedList(array $problems): array
    {
        return array_map(fn(array $problem): AtCoderProblemDTO => self::fromNormalized($problem), $problems);
    }
}
