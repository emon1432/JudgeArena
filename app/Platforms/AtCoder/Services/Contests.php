<?php

namespace App\Platforms\AtCoder\Services;

use App\Platforms\AtCoder\Client\BaseClient;
use App\Platforms\AtCoder\DTOs\AtCoderStandingsDTO;
use App\Platforms\AtCoder\Mappers\AtCoderContestMapper;
use App\Platforms\AtCoder\Mappers\AtCoderStandingsMapper;
use App\Platforms\AtCoder\Mappers\AtCoderSubmissionMapper;
use App\Platforms\AtCoder\Support\ResponseNormalizer;

class Contests
{
    public function __construct(
        private readonly BaseClient $client,
    ) {}

    /** @return \App\Platforms\AtCoder\DTOs\AtCoderContestDTO[] */
    public function list(): array
    {
        return AtCoderContestMapper::fromNormalizedList(
            ResponseNormalizer::contests($this->client->requestApi('contests'))
        );
    }

    public function standings(string $contestId, bool $virtual = false): AtCoderStandingsDTO
    {
        $path = $virtual
            ? "contests/{$contestId}/standings/virtual"
            : "contests/{$contestId}/standings";

        $contest = $this->findContestNormalized($contestId);
        $normalized = ResponseNormalizer::standings($this->client->requestApi($path), $contest, $contestId);

        return AtCoderStandingsMapper::fromApiResponse($normalized);
    }

    /** @return \App\Platforms\AtCoder\DTOs\AtCoderSubmissionDTO[] */
    public function submissions(string $contestId): array
    {
        return AtCoderSubmissionMapper::fromNormalizedList(
            ResponseNormalizer::submissions($this->client->requestApi("contests/{$contestId}/submissions"))
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function ratingChanges(string $contestId): array
    {
        return ResponseNormalizer::ratingChanges(
            $this->client->requestApi("contests/{$contestId}/results")
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function findContestNormalized(string $contestId): array
    {
        $contests = ResponseNormalizer::contests($this->client->requestApi('contests'));

        foreach ($contests as $contest) {
            if (isset($contest['id']) && (string) $contest['id'] === $contestId) {
                return $contest;
            }
        }

        return [
            'id' => $contestId,
            'title' => $contestId,
        ];
    }
}

