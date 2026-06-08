<?php

namespace App\Core\Contracts\Platforms;

use App\Core\Contracts\Importers\ContestImporter;
use App\Core\Contracts\Importers\ProblemImporter;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\RatingChangeDTO;
use App\Core\DTOs\UserDTO;

interface PlatformAdapter
{
    public function getContests(): array;
    public function getProblems(): array;
    public function getContestProblems(string $contestId): array;



    public function getContest(string $id): ContestStandingsDTO;

    /** @return UserDTO */
    public function getUser(string $username): UserDTO;

    /** @return \App\Core\DTOs\SubmissionDTO[] */
    public function getSubmissions(string $contestId, string $username): array;

    /** @return RatingChangeDTO[] */
    public function getRatingChanges(string $contestId): array;

    /** @return RatingChangeDTO[] */
    public function getUserRatingHistory(string $handle): array;


    // Importers
    public function contestImporter(): ContestImporter;
    public function problemImporter(): ProblemImporter;
}
