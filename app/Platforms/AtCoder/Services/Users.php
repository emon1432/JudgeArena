<?php

namespace App\Platforms\AtCoder\Services;

use App\Models\Contest;
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
        private readonly Contest $contestModel,
    ) {}

    //used
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

    public function submissions(string $contestId, string $handle): array
    {
        return $this->contests->submissions($contestId, $handle);
    }

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
