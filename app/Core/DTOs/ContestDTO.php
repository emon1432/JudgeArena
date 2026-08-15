<?php

namespace App\Core\DTOs;

use DateTimeImmutable;

readonly class ContestDTO
{
	public function __construct(
		public string $platform,
		public string $platformContestId,
		public string $title,
		public ?string $slug = null,
		public ?string $type = null,
		public ?string $phase = null,
		public ?DateTimeImmutable $startedAt = null,
		public ?int $durationSeconds = null,
		public ?DateTimeImmutable $endedAt = null,
		public ?string $url = null,
		public array $raw = [],
	) {
	}
}

