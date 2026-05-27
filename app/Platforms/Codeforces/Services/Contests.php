<?php

namespace App\Platforms\Codeforces\Services;

use App\Platforms\Codeforces\Client\BaseClient;
use App\Platforms\Codeforces\DTOs\CodeforcesContestDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesSubmissionDTO;
use App\Platforms\Codeforces\Support\ResponseNormalizer;

class Contests
{
    public function __construct(
        private readonly BaseClient $client,
    ) {}

    public function list(bool $gym = false, ?string $groupCode = null): array
    {
        $query = [
            'gym' => $gym ? 'true' : 'false',
        ];

        if ($groupCode !== null && $groupCode !== '') {
            $query['groupCode'] = $groupCode;
        }

        return CodeforcesContestDTO::fromApiResponses(
            ResponseNormalizer::contests($this->client->requestApi('contest.list', $query))
        );
    }

    public function standings(int $contestId, array $options = []): array
    {
        $isGymContest = $contestId > 90000;

        if (! $isGymContest) {
            return ResponseNormalizer::standings($this->client->requestApi('contest.standings', [
                'contestId' => $contestId,
            ]));
        }

        $isSigned = $this->client->requiresSignedRequestPublic($options);
        $query = array_merge([
            'contestId' => $contestId,
        ], $options);

        return ResponseNormalizer::standings($this->client->requestApi('contest.standings', $query, $isSigned));
    }

    public function status(int $contestId, array $options = []): array
    {
        $query = array_merge([
            'contestId' => $contestId,
        ], $options);

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
            ResponseNormalizer::submissions($this->client->requestApi('contest.status', $query, $this->client->requiresSignedRequestPublic($options)))
        );
    }

    public function ratingChanges(int $contestId): array
    {
        return ResponseNormalizer::ratingChanges($this->client->requestApi('contest.ratingChanges', [
            'contestId' => $contestId,
        ]));
    }

    public function hacks(int $contestId, bool $asManager = false): array
    {
        return ResponseNormalizer::hacks($this->client->requestApi('contest.hacks', [
            'contestId' => $contestId,
            'asManager' => $asManager ? 'true' : 'false',
        ]));
    }
}
