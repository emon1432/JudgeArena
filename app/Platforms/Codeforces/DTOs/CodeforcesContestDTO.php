<?php

namespace App\Platforms\Codeforces\DTOs;

use App\Platforms\Codeforces\Support\ResponseNormalizer;

readonly class CodeforcesContestDTO
{
    public function __construct(
        public ?string $id,
        public ?string $name,
        public ?string $type,
        public ?string $phase,
        public ?bool $frozen,
        public ?int $durationSeconds,
        public ?int $freezeDurationSeconds,
        public ?int $startTimeSeconds,
        public ?int $relativeTimeSeconds,
        public ?string $preparedBy,
        public ?string $websiteUrl,
        public ?string $description,
        public ?string $difficulty,
        public ?string $kind,
        public ?string $icpcRegion,
        public ?string $country,
        public ?string $city,
        public ?string $season,
        public array $raw,
    ) {}

    public static function fromApiResponse(array $contest): self
    {
        $raw = $contest;
        $normalized = ResponseNormalizer::contest($contest);

        return new self(
            id: isset($normalized['id']) ? (string) $normalized['id'] : null,
            name: $normalized['name'] ?? null,
            type: $normalized['type'] ?? null,
            phase: $normalized['phase'] ?? null,
            frozen: array_key_exists('frozen', $normalized) ? (bool) $normalized['frozen'] : null,
            durationSeconds: isset($normalized['durationSeconds']) ? (int) $normalized['durationSeconds'] : null,
            freezeDurationSeconds: isset($normalized['freezeDurationSeconds']) ? (int) $normalized['freezeDurationSeconds'] : null,
            startTimeSeconds: isset($normalized['startTimeSeconds']) ? (int) $normalized['startTimeSeconds'] : null,
            relativeTimeSeconds: isset($normalized['relativeTimeSeconds']) ? (int) $normalized['relativeTimeSeconds'] : null,
            preparedBy: $normalized['preparedBy'] ?? null,
            websiteUrl: $normalized['websiteUrl'] ?? null,
            description: $normalized['description'] ?? null,
            difficulty: isset($normalized['difficulty']) ? (string) $normalized['difficulty'] : null,
            kind: $normalized['kind'] ?? null,
            icpcRegion: $normalized['icpcRegion'] ?? null,
            country: $normalized['country'] ?? null,
            city: $normalized['city'] ?? null,
            season: $normalized['season'] ?? null,
            raw: $raw,
        );
    }

    /** @return array<int, self> */
    public static function fromApiResponses(array $contests): array
    {
        return array_map(fn(array $contest): self => self::fromApiResponse($contest), $contests);
    }
}
