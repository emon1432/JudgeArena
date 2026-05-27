<?php

namespace App\Core\Contracts\Platforms;

use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\UserDTO;

interface PlatformAdapter
{
    public function getContests(): array;

    public function getContest(string $id): ContestStandingsDTO;

    public function getProblems(): array;

    public function getUser(string $username): UserDTO;

    public function getSubmissions(string $username, int $from = 1, int $count = 100): array;
}
