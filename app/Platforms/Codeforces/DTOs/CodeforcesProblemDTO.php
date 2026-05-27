<?php

namespace App\Platforms\Codeforces\DTOs;

use App\Platforms\Codeforces\Support\ResponseNormalizer;

readonly class CodeforcesProblemDTO
{
    public function __construct(
        public ?string $contestId,
        public ?string $problemsetName,
        public ?string $index,
        public ?string $name,
        public ?string $type,
        public ?int $points,
        public ?int $rating,
        public array $tags,
        public array $raw,
    ) {}

    public static function fromApiResponse(array $problem): self
    {
        $raw = $problem;
        $normalized = ResponseNormalizer::problem($problem);

        return new self(
            contestId: isset($normalized['contestId']) ? (string) $normalized['contestId'] : null,
            problemsetName: $normalized['problemsetName'] ?? null,
            index: isset($normalized['index']) ? (string) $normalized['index'] : null,
            name: $normalized['name'] ?? null,
            type: $normalized['type'] ?? null,
            points: isset($normalized['points']) ? (int) $normalized['points'] : null,
            rating: isset($normalized['rating']) ? (int) $normalized['rating'] : null,
            tags: is_array($normalized['tags'] ?? null) ? $normalized['tags'] : [],
            raw: $raw,
        );
    }

    /** @return array<int, self> */
    public static function fromApiResponses(array $problems): array
    {
        return array_map(fn (array $problem): self => self::fromApiResponse($problem), $problems);
    }
}
