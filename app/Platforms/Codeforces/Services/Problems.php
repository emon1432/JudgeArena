<?php

namespace App\Platforms\Codeforces\Services;

use App\Platforms\Codeforces\Client\BaseClient;
use App\Platforms\Codeforces\DTOs\CodeforcesProblemDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesSubmissionDTO;
use App\Platforms\Codeforces\Mappers\CodeforcesSubmissionMapper;
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
        $normalizedProblems = ResponseNormalizer::problems(Arr::get($result, 'problems', []));

        return [
            'problems' => array_map(function (array $problem): CodeforcesProblemDTO {
                return new CodeforcesProblemDTO(
                    contestId: isset($problem['contestId']) ? (string) $problem['contestId'] : null,
                    problemsetName: $problem['problemsetName'] ?? null,
                    index: isset($problem['index']) ? (string) $problem['index'] : null,
                    name: $problem['name'] ?? null,
                    type: $problem['type'] ?? null,
                    points: isset($problem['points']) ? (int) $problem['points'] : null,
                    rating: isset($problem['rating']) ? (int) $problem['rating'] : null,
                    tags: is_array($problem['tags'] ?? null) ? $problem['tags'] : [],
                    raw: $problem,
                );
            }, $normalizedProblems),
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

        return CodeforcesSubmissionMapper::fromNormalizedList(
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

    /**
     * @param CodeforcesSubmissionDTO[] $submissions
     */
    public function acceptedProblemIds(array $submissions): array
    {
        return collect($submissions)
            ->filter(fn (CodeforcesSubmissionDTO $submission): bool => $submission->verdict === 'OK')
            ->map(function (CodeforcesSubmissionDTO $submission): ?string {
                $problem = $submission->problem;

                if ($problem === null || $problem->contestId === null || $problem->index === null) {
                    return null;
                }

                return $this->buildProblemId((int) $problem->contestId, (string) $problem->index);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
