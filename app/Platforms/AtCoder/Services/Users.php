<?php

namespace App\Platforms\AtCoder\Services;

use App\Platforms\AtCoder\Client\BaseClient;
use App\Platforms\AtCoder\DTOs\AtCoderUserDTO;
use App\Platforms\AtCoder\Mappers\AtCoderSubmissionMapper;
use App\Platforms\AtCoder\Mappers\AtCoderUserMapper;
use App\Platforms\AtCoder\Support\ResponseNormalizer;
use RuntimeException;

class Users
{
    public function __construct(
        private readonly BaseClient $client,
    ) {}

    /** @return AtCoderUserDTO */
    public function info(string $handle): AtCoderUserDTO
    {
        return AtCoderUserMapper::fromNormalized(
            ResponseNormalizer::user($this->client->requestApi("user/info/{$handle}"))
        );
    }

    /** @return \App\Platforms\AtCoder\DTOs\AtCoderSubmissionDTO[] */
    public function submissions(string $handle, int $from = 1, int $count = 0): array
    {
        $submissions = AtCoderSubmissionMapper::fromNormalizedList(
            ResponseNormalizer::submissions($this->client->requestApi("user/{$handle}/submissions"))
        );

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
            $this->client->requestApi("user/{$handle}/rating-history")
        );
    }

    public function exists(string $handle): bool
    {
        try {
            $this->info($handle);

            return true;
        } catch (RuntimeException $e) {
            return false;
        }
    }
}

