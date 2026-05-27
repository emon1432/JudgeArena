<?php

namespace App\Platforms\Codeforces\DTOs;

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

    public static function fromNormalized(array $submission): self
    {
        return new self(
            id: isset($submission['id']) ? (string) $submission['id'] : null,
            contestId: isset($submission['contestId']) ? (int) $submission['contestId'] : null,
            creationTimeSeconds: isset($submission['creationTimeSeconds']) ? (int) $submission['creationTimeSeconds'] : null,
            relativeTimeSeconds: isset($submission['relativeTimeSeconds']) ? (int) $submission['relativeTimeSeconds'] : null,
            problem: is_array($submission['problem'] ?? null) ? $submission['problem'] : null,
            author: is_array($submission['author'] ?? null) ? $submission['author'] : null,
            programmingLanguage: $submission['programmingLanguage'] ?? null,
            verdict: $submission['verdict'] ?? null,
            testset: $submission['testset'] ?? null,
            passedTestCount: isset($submission['passedTestCount']) ? (int) $submission['passedTestCount'] : null,
            timeConsumedMillis: isset($submission['timeConsumedMillis']) ? (int) $submission['timeConsumedMillis'] : null,
            memoryConsumedBytes: isset($submission['memoryConsumedBytes']) ? (int) $submission['memoryConsumedBytes'] : null,
            points: isset($submission['points']) ? (float) $submission['points'] : null,
            raw: $submission,
        );
    }
}
