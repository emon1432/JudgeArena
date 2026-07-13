<?php

namespace App\Platforms\AtCoder\Services;

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

    //used
    public function list(): array
    {
        return AtCoderContestMapper::fromNormalizedList(
            ResponseNormalizer::contests($this->scraper->getContests())
        );
    }

    //used
    public function standings(string $contestId, bool $virtual = false): AtCoderStandingsDTO
    {
        $normalized = ResponseNormalizer::standings(
            $virtual ? $this->scraper->getStandingsVirtual($contestId) : $this->scraper->getStandings($contestId),
        );

        return AtCoderStandingsMapper::fromApiResponse($normalized);
    }

    //used
    public function submissions(string $contestId, ?string $user = null): array
    {
        return AtCoderSubmissionMapper::fromNormalizedList(
            ResponseNormalizer::submissions($this->scraper->getSubmissions($contestId, $user))
        );
    }

    //used
    public function ratingChanges(string $contestId): array
    {
        return AtCoderRatingChangeTransformer::fromApiRatingChanges(
            AtCoderRatingChangeMapper::fromNormalizedList(
                ResponseNormalizer::ratingChanges(
                    $this->scraper->getResults($contestId)
                )
            ),
            $contestId,
            null
        );
    }

    //used
    public function tasks(string $contestId): array
    {
        return $this->scraper->getTasks($contestId);
    }
}
