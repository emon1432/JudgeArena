<?php

namespace App\Core\Contracts\Platforms;

use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\RatingChangeDTO;
use App\Core\DTOs\UserDTO;

interface PlatformAdapter
{
    /** @return \App\Core\DTOs\ContestDTO[] */
    public function getContests(): array;

    /** @return ContestStandingsDTO */
    public function getContest(string $id): ContestStandingsDTO;

    /**
     * @return array{problems: \App\Core\DTOs\ProblemDTO[], problemStatistics: array<int, array<string, mixed>>}
     */
    public function getProblems(): array;

    /** @return UserDTO */
    public function getUser(string $username): UserDTO;

    /** @return \App\Core\DTOs\SubmissionDTO[] */
    public function getSubmissions(string $username, int $from = 1, int $count = 100): array;

    /** @return RatingChangeDTO[] */
    public function getRatingChanges(string $contestId): array;

    /** @return RatingChangeDTO[] */
    public function getUserRatingHistory(string $handle): array;
}
