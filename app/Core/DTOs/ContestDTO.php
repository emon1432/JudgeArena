<?php

namespace App\Core\DTOs;

use DateTimeImmutable;

readonly class ContestDTO
{
	public function __construct(
		public string $platform,
		public string $platformContestId,
		public string $title,
		public ?string $phase = null,
		public ?DateTimeImmutable $startedAt = null,
		public ?int $durationSeconds = null,
		public array $raw = [],
	) {
	}
}

