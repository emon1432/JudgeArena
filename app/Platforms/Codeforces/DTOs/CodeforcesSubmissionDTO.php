<?php

namespace App\Platforms\Codeforces\DTOs;

readonly class CodeforcesSubmissionDTO
{
    public function __construct(
        public ?string $id,
        public ?int $contestId,
        public ?int $creationTimeSeconds,
        public ?int $relativeTimeSeconds,
        public ?CodeforcesProblemDTO $problem,
        public ?CodeforcesPartyDTO $author,
        public ?string $programmingLanguage,
        public ?string $verdict,
        public ?string $testset,
        public ?int $passedTestCount,
        public ?int $timeConsumedMillis,
        public ?int $memoryConsumedBytes,
        public ?float $points,
        public array $raw,
    ) {}
}
