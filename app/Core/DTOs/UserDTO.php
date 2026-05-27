<?php

namespace App\Core\DTOs;

readonly class UserDTO
{
	public function __construct(
		public string $platform,
		public string $platformHandle,
		public ?string $firstName = null,
		public ?string $lastName = null,
		public ?int $rating = null,
		public ?string $country = null,
		public array $raw = [],
	) {
	}
}

