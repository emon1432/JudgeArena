<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\DTOs;

readonly class CodeforcesProblemDTO
{
    /**
     * @param array<int, string> $tags
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public ?string $contestId,
        public ?string $index,
        public ?string $name,
        public ?string $type,
        public ?int $points,
        public ?int $rating,
        public array $tags,
        public array $raw,
        public ?int $solvedCount = null,
    ) {}
}

