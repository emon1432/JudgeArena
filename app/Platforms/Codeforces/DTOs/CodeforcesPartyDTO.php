<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\DTOs;

readonly class CodeforcesPartyDTO
{
    /**
     * @param array<int, array{handle: ?string, name: ?string}> $members
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public ?int $contestId,
        public array $members,
        public ?string $participantType,
        public ?int $teamId,
        public ?string $teamName,
        public ?bool $ghost,
        public ?int $room,
        public ?int $startTimeSeconds,
        public array $raw,
    ) {}
}

