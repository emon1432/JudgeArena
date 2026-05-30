<?php

namespace App\Platforms\AtCoder\DTOs;

readonly class AtCoderProblemDTO
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public ?string $id,
        public ?string $contestId,
        public ?string $title,
        public ?string $position,
        public ?string $fullTitle,
        public ?float $points,
        public ?string $timeLimit,
        public ?string $memoryLimit,
        public ?string $url,
        public array $raw,
    ) {}
}

