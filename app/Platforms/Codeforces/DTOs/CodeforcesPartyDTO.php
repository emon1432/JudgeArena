<?php

namespace App\Platforms\Codeforces\DTOs;

readonly class CodeforcesPartyDTO
{
    /**
     * @param array<int, array<string, mixed>> $members
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
