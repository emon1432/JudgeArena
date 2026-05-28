<?php

namespace App\Platforms\AtCoder\DTOs;

readonly class AtCoderSubmissionDTO
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public ?string $id,
        public ?string $contestId,
        public ?int $creationTimeSeconds,
        public ?int $relativeTimeSeconds,
        public ?AtCoderProblemDTO $problem,
        public ?AtCoderPartyDTO $author,
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

