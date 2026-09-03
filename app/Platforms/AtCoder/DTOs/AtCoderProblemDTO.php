<?php

declare(strict_types=1);

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
        public ?float $points,
        public ?int $rating,
        public ?string $timeLimit,
        public ?string $memoryLimit,
        public ?int $solverCount,
        public ?string $url,
        public array $raw,
    ) {}
}
