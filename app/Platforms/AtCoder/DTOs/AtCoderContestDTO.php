<?php

namespace App\Platforms\AtCoder\DTOs;

readonly class AtCoderContestDTO
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public ?string $id,
        public ?string $title,
        public ?string $url,
        public ?string $date,
        public ?string $duration,
        public ?string $rateChange,
        public ?string $type,
        public array $raw,
    ) {}
}
