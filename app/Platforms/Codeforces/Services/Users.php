<?php

namespace App\Platforms\Codeforces\Services;

use App\Platforms\Codeforces\Client\BaseClient;
use App\Platforms\Codeforces\DTOs\CodeforcesUserDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesSubmissionDTO;
use App\Platforms\Codeforces\Support\ResponseNormalizer;
use Illuminate\Support\Arr;
use RuntimeException;

class Users
{
    public function __construct(
        private readonly BaseClient $client,
    ) {}

    public function info(string $handle, bool $checkHistoricHandles = true): CodeforcesUserDTO
    {
        $result = $this->client->requestApi('user.info', [
            'handles' => $handle,
            'checkHistoricHandles' => $checkHistoricHandles ? 'true' : 'false',
        ]);

        return CodeforcesUserDTO::fromApiResponse(Arr::first($result, null, []));
    }

    public function infos(array $handles, bool $checkHistoricHandles = true): array
    {
        $results = [];

        foreach ($this->chunkHandlesForUserInfo($handles) as $chunk) {
            $batchResult = $this->client->requestApi('user.info', [
                'handles' => implode(';', $chunk),
                'checkHistoricHandles' => $checkHistoricHandles ? 'true' : 'false',
            ]);

            array_push($results, ...CodeforcesUserDTO::fromApiResponses(ResponseNormalizer::users($batchResult)));
        }

        return $results;
    }

    public function status(string $handle, int $from = 1, int $count = 0): array
    {
        $query = [
            'handle' => $handle,
            'from' => max(1, $from),
        ];

        if ($count !== 0) {
            $query['count'] = max(1, $count);
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
            ResponseNormalizer::submissions($this->client->requestApi('user.status', $query))
        );
    }

    public function rating(string $handle): array
    {
        return ResponseNormalizer::ratingChanges($this->client->requestApi('user.rating', [
            'handle' => $handle,
        ]));
    }

    public function ratedList(bool $activeOnly = true, bool $includeRetired = false, ?int $contestId = null): array
    {
        $query = [
            'activeOnly' => $activeOnly ? 'true' : 'false',
            'includeRetired' => $includeRetired ? 'true' : 'false',
        ];

        if ($contestId !== null) {
            $query['contestId'] = $contestId;
        }

        return ResponseNormalizer::users($this->client->requestApi('user.ratedList', $query));
    }

    public function blogEntries(string $handle): array
    {
        return ResponseNormalizer::blogEntries($this->client->requestApi('user.blogEntries', [
            'handle' => $handle,
        ]));
    }

    public function friends(bool $onlyOnline = false): array
    {
        return $this->client->requestApi('user.friends', [
            'onlyOnline' => $onlyOnline ? 'true' : 'false',
        ]);
    }

    public function exists(string $handle): bool
    {
        try {
            $this->info($handle);

            return true;
        } catch (RuntimeException $e) {
            return ! str_contains(strtolower($e->getMessage()), 'not found');
        }
    }

    public function rankByRating(string $handle, bool $activeOnly = true, bool $includeRetired = false): ?int
    {
        $target = strtolower($handle);

        foreach ($this->ratedList($activeOnly, $includeRetired) as $index => $user) {
            if (strtolower((string) ($user['handle'] ?? '')) === $target) {
                return $index + 1;
            }
        }

        return null;
    }

    public function profileUrl(string $handle): string
    {
        return $this->client->webBaseUrl() . '/profile/' . urlencode($handle);
    }

    public function normalize(array $user): array
    {
        return ResponseNormalizer::user($user);
    }

    public function calculateMaxRating(array $userInfo): ?int
    {
        return $userInfo['maxRating'] ?? $userInfo['rating'] ?? null;
    }

    private function chunkHandlesForUserInfo(array $handles): array
    {
        $chunks = [];
        $currentChunk = [];
        $currentLength = 0;

        foreach ($handles as $handle) {
            $handle = trim((string) $handle);
            if ($handle === '') {
                continue;
            }

            $addition = $handle . ';';
            $length = strlen($addition);

            if ($currentChunk !== [] && ($currentLength + $length) > $this->client->userInfoBatchCharLimit()) {
                $chunks[] = $currentChunk;
                $currentChunk = [];
                $currentLength = 0;
            }

            $currentChunk[] = $handle;
            $currentLength += $length;
        }

        if ($currentChunk !== []) {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }
}
