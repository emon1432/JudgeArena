<?php

namespace App\Platforms\AtCoder\Services;

use App\Core\DTOs\RatingChangeDTO;
use App\Platforms\AtCoder\Services\AtCoderHtmlScraper;
use App\Platforms\AtCoder\DTOs\AtCoderStandingsDTO;
use App\Platforms\AtCoder\Mappers\AtCoderContestMapper;
use App\Platforms\AtCoder\Mappers\AtCoderRatingChangeMapper;
use App\Platforms\AtCoder\Mappers\AtCoderStandingsMapper;
use App\Platforms\AtCoder\Mappers\AtCoderSubmissionMapper;
use App\Platforms\AtCoder\Support\ResponseNormalizer;
use App\Platforms\AtCoder\Transformers\AtCoderRatingChangeTransformer;

class Contests
{
    public function __construct(
        private readonly AtCoderHtmlScraper $scraper,
    ) {}

    /** @return \App\Platforms\AtCoder\DTOs\AtCoderContestDTO[] */
    public function list(): array
    {
        return AtCoderContestMapper::fromNormalizedList(
            ResponseNormalizer::contests($this->scraper->getContests())
        );
    }

    public function standings(string $contestId, bool $virtual = false): AtCoderStandingsDTO
    {
        $contest = $this->findContestNormalized($contestId);
        $normalized = ResponseNormalizer::standings(
            $virtual ? $this->scraper->getStandingsVirtual($contestId) : $this->scraper->getStandings($contestId),
            $contest,
            $contestId,
        );

        return AtCoderStandingsMapper::fromApiResponse($normalized);
    }

    /** @return \App\Platforms\AtCoder\DTOs\AtCoderSubmissionDTO[] */
    public function submissions(string $contestId, ?string $user = null): array
    {
        return AtCoderSubmissionMapper::fromNormalizedList(
            ResponseNormalizer::submissions($this->scraper->getSubmissions($contestId, $user))
        );
    }

    /** @return RatingChangeDTO[] */
    public function ratingChanges(string $contestId): array
    {
        $normalized = ResponseNormalizer::ratingChanges(
            $this->scraper->getResults($contestId)
        );

        $platformDtos = AtCoderRatingChangeMapper::fromNormalizedList($normalized);

        return AtCoderRatingChangeTransformer::fromApiRatingChanges($platformDtos, $contestId);
    }

    /** @return array<string, mixed> */
    public function tasks(string $contestId): array
    {
        return $this->scraper->getTasks($contestId);
    }

    /**
     * @return array<string, mixed>
     */
    private function findContestNormalized(string $contestId): array
    {
        $contests = ResponseNormalizer::contests($this->scraper->getContests());

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

