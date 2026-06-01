<?php

namespace App\Platforms\AtCoder\Services;

use App\Core\DTOs\RatingChangeDTO;
use App\Platforms\AtCoder\Services\AtCoderHtmlScraper;
use App\Platforms\AtCoder\DTOs\AtCoderUserDTO;
use App\Platforms\AtCoder\Mappers\AtCoderRatingChangeMapper;
use App\Platforms\AtCoder\Mappers\AtCoderUserMapper;
use App\Platforms\AtCoder\Support\ResponseNormalizer;
use App\Platforms\AtCoder\Transformers\AtCoderRatingChangeTransformer;
use App\Services\ApplicationLogger;
use RuntimeException;

class Users
{
    public function __construct(
        private readonly AtCoderHtmlScraper $scraper,
        private readonly Contests $contests,
    ) {}

    /** @return AtCoderUserDTO */
    public function info(string $handle): AtCoderUserDTO
    {
        $payload = $this->scraper->getUserProfile($handle);

        if (! is_array($payload['result'] ?? null)) {
            throw new RuntimeException('AtCoder user request failed.');
        }

        return AtCoderUserMapper::fromNormalized(
            ResponseNormalizer::user($payload)
        );
    }

    /** @return \App\Platforms\AtCoder\DTOs\AtCoderSubmissionDTO[] */
    public function submissions(string $handle, int $from = 1, int $count = 0): array
    {
        $submissions = [];

        foreach ($this->contests->list() as $contest) {
            $submissions = array_merge(
                $submissions,
                $this->contests->submissions((string) $contest->id, $handle)
            );
        }

        $offset = max(0, $from - 1);
        if ($count > 0) {
            return array_slice($submissions, $offset, $count);
        }

        if ($offset > 0) {
            return array_slice($submissions, $offset);
        }

        return $submissions;
    }

    /** @return RatingChangeDTO[] */
    public function ratingHistory(string $handle): array
    {
        return AtCoderRatingChangeTransformer::fromApiRatingChanges(
            AtCoderRatingChangeMapper::fromNormalizedList(
                ResponseNormalizer::ratingChanges(
                    $this->scraper->getUserRatingHistory($handle)
                )
            ),
            $handle,
        );
    }

    public function exists(string $handle): bool
    {
        try {
            $this->info($handle);

            return true;
        } catch (RuntimeException $e) {
            app(ApplicationLogger::class)->warning('AtCoder user lookup failed', [
                'category' => 'api',
                'platform' => 'atcoder',
                'source' => self::class,
                'user_handle' => $handle,
            ], $e);

            return false;
        }
    }
}
