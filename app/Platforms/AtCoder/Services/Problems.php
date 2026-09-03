<?php

declare(strict_types=1);

namespace App\Platforms\AtCoder\Services;

use App\Platforms\AtCoder\Client\BaseClient;
use App\Platforms\AtCoder\DTOs\AtCoderProblemDTO;
use App\Platforms\AtCoder\Mappers\AtCoderProblemMapper;
use App\Platforms\AtCoder\Support\ResponseNormalizer;

class Problems
{
    /**
     * In-memory cache of indexed datasets during command execution
     */
    private ?array $contestProblemGroupMap = null;
    private ?array $mergedProblemsMap = null;
    private ?array $problemModelsMap = null;

    public function __construct(
        private readonly BaseClient $client,
    ) {}

    /**
     * Get all contest-problem mappings grouped by contest ID from Kenkoooo.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getContestProblemMap(): array
    {
        if ($this->contestProblemGroupMap !== null) {
            return $this->contestProblemGroupMap;
        }

        $contestProblemList = $this->client->requestResource('contest-problem.json');
        $groups = [];

        if (is_array($contestProblemList)) {
            foreach ($contestProblemList as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $contestId = (string) ($item['contest_id'] ?? '');
                if ($contestId === '') {
                    continue;
                }

                $groups[$contestId][] = $item;
            }
        }

        $this->contestProblemGroupMap = $groups;

        return $this->contestProblemGroupMap;
    }

    /**
     * Get merged problems dataset indexed by problem ID.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getMergedProblemsMap(): array
    {
        if ($this->mergedProblemsMap !== null) {
            return $this->mergedProblemsMap;
        }

        $mergedList = $this->client->requestResource('merged-problems.json');
        $indexed = [];

        if (is_array($mergedList)) {
            foreach ($mergedList as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $id = (string) ($item['id'] ?? '');
                if ($id !== '') {
                    $indexed[$id] = $item;
                }
            }
        }

        $this->mergedProblemsMap = $indexed;

        return $this->mergedProblemsMap;
    }

    /**
     * Get problem models (difficulty ratings) indexed by problem ID.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getProblemModelsMap(): array
    {
        if ($this->problemModelsMap !== null) {
            return $this->problemModelsMap;
        }

        $models = $this->client->requestResource('problem-models.json');
        $this->problemModelsMap = is_array($models) ? $models : [];

        return $this->problemModelsMap;
    }

    /**
     * Fetch all problems for a specific contest using Kenkoooo datasets.
     *
     * @return AtCoderProblemDTO[]
     */
    public function getContestProblems(string $contestId): array
    {
        $contestMap = $this->getContestProblemMap();
        $mergedMap = $this->getMergedProblemsMap();
        $modelsMap = $this->getProblemModelsMap();

        $pairs = $contestMap[$contestId] ?? [];
        $problems = [];

        foreach ($pairs as $cp) {
            $problemId = (string) ($cp['problem_id'] ?? '');
            $position = (string) ($cp['problem_index'] ?? '');

            $meta = $mergedMap[$problemId] ?? [];
            $model = $modelsMap[$problemId] ?? [];

            $rawTitle = (string) ($meta['title'] ?? ($meta['name'] ?? $problemId));
            $cleanTitle = preg_replace('/^[A-Z1-9]\.\s*/', '', $rawTitle);

            $score = $meta['point'] ?? ($model['raw_point'] ?? null);
            $difficulty = $model['difficulty'] ?? null;
            $execTimeMs = isset($meta['execution_time']) ? (int) $meta['execution_time'] : null;
            $solverCount = isset($meta['solver_count']) ? (int) $meta['solver_count'] : null;

            $problems[] = [
                'id' => $problemId,
                'contest_id' => $contestId,
                'title' => $cleanTitle,
                'position' => $position,
                'score' => $score,
                'rating' => $difficulty,
                'time_limit' => $execTimeMs !== null ? $execTimeMs . ' ms' : null,
                'memory_limit' => '1024 MB',
                'solver_count' => $solverCount,
                'url' => 'https://atcoder.jp/contests/' . $contestId . '/tasks/' . $problemId,
            ];
        }

        return AtCoderProblemMapper::fromNormalizedList(
            ResponseNormalizer::problems($problems)
        );
    }
}
