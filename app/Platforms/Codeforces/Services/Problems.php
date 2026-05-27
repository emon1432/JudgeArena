<?php

namespace App\Platforms\Codeforces\Services;

use App\Platforms\Codeforces\Client\BaseClient;
use App\Platforms\Codeforces\DTOs\CodeforcesProblemDTO;
use App\Platforms\Codeforces\Support\ResponseNormalizer;
use Illuminate\Support\Arr;

class Problems
{
    public function __construct(
        private readonly BaseClient $client,
    ) {}

    public function list(?array $tags = null, ?string $problemsetName = null): array
    {
        $query = [];

        if (! empty($tags)) {
            $query['tags'] = implode(';', $tags);
        }

        if ($problemsetName !== null && $problemsetName !== '') {
            $query['problemsetName'] = $problemsetName;
        }

        $result = $this->client->requestApi('problemset.problems', $query);

        return [
            'problems' => CodeforcesProblemDTO::fromApiResponses(ResponseNormalizer::problems(Arr::get($result, 'problems', []))),
            'problemStatistics' => ResponseNormalizer::problemStatisticsList(Arr::get($result, 'problemStatistics', [])),
        ];
    }

    public function recentStatus(int $count, ?string $problemsetName = null): array
    {
        $query = [
            'count' => max(1, min(1000, $count)),
        ];

        if ($problemsetName !== null && $problemsetName !== '') {
            $query['problemsetName'] = $problemsetName;
        }

        return \App\Platforms\Codeforces\DTOs\CodeforcesSubmissionDTO::fromApiResponses(
            ResponseNormalizer::submissions($this->client->requestApi('problemset.recentStatus', $query))
        );
    }

    public function buildProblemId(int $contestId, string $index): string
    {
        return $contestId . strtoupper(trim($index));
    }

    public function problemUrl(int $contestId, string $index): string
    {
        $section = $contestId > 90000 ? 'gymProblem' : 'problem';

        return $this->client->webBaseUrl() . '/problemset/' . $section . '/' . $contestId . '/' . strtoupper(trim($index));
    }

    public function acceptedProblemIds(array $submissions): array
    {
        return collect($submissions)
            ->filter(fn(array $submission): bool => ($submission['verdict'] ?? null) === 'OK')
            ->map(function (array $submission): ?string {
                $problem = $submission['problem'] ?? [];
                $contestId = Arr::get($problem, 'contestId');
                $index = Arr::get($problem, 'index');

                if ($contestId === null || $index === null) {
                    return null;
                }

                return $this->buildProblemId((int) $contestId, (string) $index);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
