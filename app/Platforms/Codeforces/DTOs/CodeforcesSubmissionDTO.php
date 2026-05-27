<?php

namespace App\Platforms\Codeforces\DTOs;

use App\Platforms\Codeforces\Support\ResponseNormalizer;

readonly class CodeforcesSubmissionDTO
{
    public function __construct(
        public ?string $id,
        public ?int $contestId,
        public ?int $creationTimeSeconds,
        public ?int $relativeTimeSeconds,
        public ?array $problem,
        public ?array $author,
        public ?string $programmingLanguage,
        public ?string $verdict,
        public ?string $testset,
        public ?int $passedTestCount,
        public ?int $timeConsumedMillis,
        public ?int $memoryConsumedBytes,
        public ?float $points,
        public array $raw,
    ) {}

    public static function fromApiResponse(array $submission): self
    {
        $raw = $submission;
        $normalized = ResponseNormalizer::submission($submission);

        return new self(
            id: isset($normalized['id']) ? (string) $normalized['id'] : null,
            contestId: isset($normalized['contestId']) ? (int) $normalized['contestId'] : null,
            creationTimeSeconds: isset($normalized['creationTimeSeconds']) ? (int) $normalized['creationTimeSeconds'] : null,
            relativeTimeSeconds: isset($normalized['relativeTimeSeconds']) ? (int) $normalized['relativeTimeSeconds'] : null,
            problem: is_array($normalized['problem'] ?? null) ? $normalized['problem'] : null,
            author: is_array($normalized['author'] ?? null) ? $normalized['author'] : null,
            programmingLanguage: $normalized['programmingLanguage'] ?? null,
            verdict: $normalized['verdict'] ?? null,
            testset: $normalized['testset'] ?? null,
            passedTestCount: isset($normalized['passedTestCount']) ? (int) $normalized['passedTestCount'] : null,
            timeConsumedMillis: isset($normalized['timeConsumedMillis']) ? (int) $normalized['timeConsumedMillis'] : null,
            memoryConsumedBytes: isset($normalized['memoryConsumedBytes']) ? (int) $normalized['memoryConsumedBytes'] : null,
            points: isset($normalized['points']) ? (float) $normalized['points'] : null,
            raw: $raw,
        );
    }

    /** @return array<int, self> */
    public static function fromApiResponses(array $submissions): array
    {
        return array_map(fn(array $s): self => self::fromApiResponse($s), $submissions);
    }
}
