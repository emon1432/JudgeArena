<?php

namespace App\Platforms\Codeforces\Services;

use App\Core\DTOs\RatingChangeDTO;
use App\Platforms\Codeforces\Client\BaseClient;
use App\Platforms\Codeforces\DTOs\CodeforcesStandingsDTO;
use App\Platforms\Codeforces\Mappers\CodeforcesContestMapper;
use App\Platforms\Codeforces\Mappers\CodeforcesRatingChangeMapper;
use App\Platforms\Codeforces\Mappers\CodeforcesStandingsMapper;
use App\Platforms\Codeforces\Mappers\CodeforcesSubmissionMapper;
use App\Platforms\Codeforces\Support\ResponseNormalizer;
use App\Platforms\Codeforces\Transformers\CodeforcesRatingChangeTransformer;

class Contests
{
    public function __construct(
        private readonly BaseClient $client,
    ) {}

    //used
    public function list(bool $gym = false, ?string $groupCode = null): array
    {
        $query = [
            'gym' => $gym ? 'true' : 'false',
        ];

        if ($groupCode !== null && $groupCode !== '') {
            $query['groupCode'] = $groupCode;
        }

        return CodeforcesContestMapper::fromNormalizedList(
            ResponseNormalizer::contests($this->client->requestApi('contest.list', $query))
        );
    }

    public function standings(int $contestId, array $options = []): CodeforcesStandingsDTO
    {
        $isGymContest = $contestId > 90000;

        if (! $isGymContest) {
            return CodeforcesStandingsMapper::fromApiResponse(ResponseNormalizer::standings($this->client->requestApi('contest.standings', [
                'contestId' => $contestId,
            ])));
        }

        $isSigned = $this->client->requiresSignedRequestPublic($options);
        $query = array_merge([
            'contestId' => $contestId,
        ], $options);

        return CodeforcesStandingsMapper::fromApiResponse(ResponseNormalizer::standings($this->client->requestApi('contest.standings', $query, $isSigned)));
    }

    /** @return \App\Platforms\Codeforces\DTOs\CodeforcesSubmissionDTO[] */
    public function status(int $contestId, array $options = []): array
    {
        $query = array_merge([
            'contestId' => $contestId,
        ], $options);

        return CodeforcesSubmissionMapper::fromNormalizedList(
            ResponseNormalizer::submissions($this->client->requestApi('contest.status', $query, $this->client->requiresSignedRequestPublic($options)))
        );
    }

    /** @return RatingChangeDTO[] */
    public function ratingChanges(string $contestId): array
    {
        $normalized = ResponseNormalizer::ratingChanges($this->client->requestApi('contest.ratingChanges', [
            'contestId' => (int) $contestId,
        ]));

        $platformDtos = CodeforcesRatingChangeMapper::fromNormalizedList($normalized);

        return CodeforcesRatingChangeTransformer::fromApiRatingChanges($platformDtos, $contestId);
    }

    /** @return array<int, array<string, mixed>> */
    public function hacks(int $contestId, bool $asManager = false): array
    {
        return ResponseNormalizer::hacks($this->client->requestApi('contest.hacks', [
            'contestId' => $contestId,
            'asManager' => $asManager ? 'true' : 'false',
        ]));
    }
}
