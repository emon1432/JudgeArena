<?php

namespace App\Core\DTOs;

readonly class ProblemDTO
{
	public function __construct(
		public string $platform,
		public string $platformProblemId,
		public string $title,
		public ?string $contestPlatformId = null,
        public ?string $code = null,
        public ?float $points = null,
		public ?int $rating = null,
		public array $tags = [],
		public array $raw = [],
	) {
	}
}

