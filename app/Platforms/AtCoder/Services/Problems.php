<?php

namespace App\Platforms\AtCoder\Services;

use App\Platforms\AtCoder\Client\BaseClient;
use App\Platforms\AtCoder\Mappers\AtCoderProblemMapper;
use App\Platforms\AtCoder\Support\ResponseNormalizer;

class Problems
{
    public function __construct(
        private readonly BaseClient $client,
    ) {}

    /**
     * @return array{problems: \App\Platforms\AtCoder\DTOs\AtCoderProblemDTO[], problemStatistics: array<int, array<string, mixed>>}
     */
    public function list(): array
    {
        $problems = AtCoderProblemMapper::fromNormalizedList(
            ResponseNormalizer::problems($this->client->requestApi('problems'))
        );

        return [
            'problems' => $problems,
            'problemStatistics' => [],
        ];
    }
}

