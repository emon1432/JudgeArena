<?php

namespace App\Platforms\Codeforces\Services;

use App\Platforms\Codeforces\Client\BaseClient;
use App\Platforms\Codeforces\DTOs\CodeforcesContestDTO;
use App\Platforms\Codeforces\Support\ResponseNormalizer;

class Contests
{
    public function __construct(
        private ?BaseClient $client = null,
    ) {
        $this->client = $this->client ?? new BaseClient();
    }
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

        return \App\Platforms\Codeforces\DTOs\CodeforcesSubmissionDTO::fromApiResponses(
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
