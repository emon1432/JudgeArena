<?php

namespace App\Platforms\AtCoder\DTOs;

readonly class AtCoderPartyDTO
{
    /**
     * @param array<int, array{handle: ?string, name: ?string}> $members
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public ?string $contestId,
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

