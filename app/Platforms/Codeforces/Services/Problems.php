<?php

namespace App\Platforms\Codeforces\Services;

use App\Platforms\Codeforces\Client\BaseClient;
use App\Platforms\Codeforces\DTOs\CodeforcesSubmissionDTO;
use App\Platforms\Codeforces\Mappers\CodeforcesProblemMapper;
use App\Platforms\Codeforces\Mappers\CodeforcesSubmissionMapper;
use App\Platforms\Codeforces\Support\ResponseNormalizer;
use Illuminate\Support\Arr;

class Problems
{
    public function __construct(
        private readonly BaseClient $client,
    ) {}

    //used
    public function list(?array $tags = null): array
    {
        $query = [];

        if (! empty($tags)) {
            $query['tags'] = implode(';', $tags);
        }

        $result = $this->client->requestApi('problemset.problems', $query);
        $normalizedProblems = ResponseNormalizer::problems(Arr::get($result, 'problems', []));
        $normalizedStatistics = ResponseNormalizer::problemStatisticsList(Arr::get($result, 'problemStatistics', []));

        //merge problems and statistics by contestId and index, add new key 'solvedCount' to problem
        $problemsWithStatistics = collect($normalizedProblems)->map(function (array $problem) use ($normalizedStatistics) {
            $contestId = Arr::get($problem, 'contestId');
            $index = Arr::get($problem, 'index');

            if ($contestId === null || $index === null) {
                return $problem;
            }

            $matchingStatistics = collect($normalizedStatistics)->firstWhere(function (array $statistic) use ($contestId, $index) {
                return Arr::get($statistic, 'contestId') === $contestId && Arr::get($statistic, 'index') === $index;
            });

            if ($matchingStatistics !== null) {
                $problem['solvedCount'] = Arr::get($matchingStatistics, 'solvedCount', 0);
            } else {
                $problem['solvedCount'] = 0;
            }

            return $problem;
        })->all();

        return CodeforcesProblemMapper::fromNormalizedList($problemsWithStatistics);
    }

    public function recentStatus(int $count): array
    {
        $query = [
            'count' => max(1, min(1000, $count)),
        ];

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

    public function acceptedProblemIds(array $submissions): array
    {
        return collect($submissions)
            ->filter(fn(CodeforcesSubmissionDTO $submission): bool => $submission->verdict === 'OK')
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
