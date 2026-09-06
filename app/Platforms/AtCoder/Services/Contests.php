<?php

declare(strict_types=1);

namespace App\Platforms\AtCoder\Services;

use App\Platforms\AtCoder\Client\BaseClient;
use App\Platforms\AtCoder\DTOs\AtCoderStandingsDTO;
use App\Platforms\AtCoder\Mappers\AtCoderContestMapper;
use App\Platforms\AtCoder\Mappers\AtCoderStandingsMapper;
use App\Platforms\AtCoder\Support\ResponseNormalizer;

class Contests
{
    public function __construct(
        private readonly BaseClient $client,
    ) {}

    public function all(): array
    {
        $rawContests = $this->client->requestResource('contests.json');

        return AtCoderContestMapper::fromNormalizedList(
            ResponseNormalizer::contests($rawContests)
        );
    }

    public function list(): array
    {
        return $this->all();
    }

    /**
     * Fetch contest standings from AtCoder native web JSON endpoint.
     */
    public function standings(string $contestId): AtCoderStandingsDTO
    {
        $response = $this->client->requestWebJson("contests/{$contestId}/standings/json");

        return AtCoderStandingsMapper::fromApiResponse(
            ResponseNormalizer::standings($response, null, $contestId)
        );
    }

    /**
     * Get tasks for a contest from the standings TaskInfo JSON.
     *
     * @return array<string, mixed>
     */
    public function tasks(string $contestId): array
    {
        $standings = $this->client->requestWebJson("contests/{$contestId}/standings/json");
        $taskInfoList = $standings['TaskInfo'] ?? [];
        $tasks = [];

        foreach ($taskInfoList as $task) {
            $taskId = $task['TaskScreenName'] ?? $task['TaskName'] ?? null;
            if ($taskId === null || $taskId === '') {
                continue;
            }

            $tasks[] = [
                'id' => (string) $taskId,
                'contest_id' => $contestId,
                'title' => (string) ($task['TaskName'] ?? $taskId),
                'position' => (string) ($task['Assignment'] ?? ''),
                'score' => isset($task['MaximumScore']) && is_numeric($task['MaximumScore'])
                    ? ((float) $task['MaximumScore']) / 100
                    : null,
                'time_limit' => '',
                'memory_limit' => '',
                'url' => "https://atcoder.jp/contests/{$contestId}/tasks/{$taskId}",
            ];
        }

        return ['result' => $tasks];
    }
}
