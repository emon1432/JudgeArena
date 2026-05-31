<?php

namespace App\Platforms\AtCoder\Services;

use App\Platforms\AtCoder\Services\AtCoderHtmlScraper;
use App\Platforms\AtCoder\DTOs\AtCoderUserDTO;
use App\Platforms\AtCoder\Mappers\AtCoderUserMapper;
use App\Platforms\AtCoder\Support\ResponseNormalizer;
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

    /** @return array<int, array<string, mixed>> */
    public function ratingHistory(string $handle): array
    {
        return ResponseNormalizer::ratingChanges(
            $this->scraper->getUserRatingHistory($handle)
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

