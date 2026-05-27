<?php

namespace App\Platforms\Codeforces\Services;

use App\Platforms\Codeforces\Client\BaseClient;
use App\Platforms\Codeforces\DTOs\CodeforcesProblemDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesSubmissionDTO;
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

        return array_map(
            fn (array $submission): CodeforcesSubmissionDTO => new CodeforcesSubmissionDTO(
                id: isset($submission['id']) ? (string) $submission['id'] : null,
                contestId: isset($submission['contestId']) ? (int) $submission['contestId'] : null,
                creationTimeSeconds: isset($submission['creationTimeSeconds']) ? (int) $submission['creationTimeSeconds'] : null,
                relativeTimeSeconds: isset($submission['relativeTimeSeconds']) ? (int) $submission['relativeTimeSeconds'] : null,
                problem: is_array($submission['problem'] ?? null) ? $submission['problem'] : null,
                author: is_array($submission['author'] ?? null) ? $submission['author'] : null,
                programmingLanguage: $submission['programmingLanguage'] ?? null,
                verdict: $submission['verdict'] ?? null,
                testset: $submission['testset'] ?? null,
                passedTestCount: isset($submission['passedTestCount']) ? (int) $submission['passedTestCount'] : null,
                timeConsumedMillis: isset($submission['timeConsumedMillis']) ? (int) $submission['timeConsumedMillis'] : null,
                memoryConsumedBytes: isset($submission['memoryConsumedBytes']) ? (int) $submission['memoryConsumedBytes'] : null,
                points: isset($submission['points']) ? (float) $submission['points'] : null,
                raw: $submission,
            ),
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

                if (! is_array($problem)) {
                    return null;
                }

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
