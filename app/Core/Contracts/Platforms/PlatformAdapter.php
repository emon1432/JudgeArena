<?php

namespace App\Core\Contracts\Platforms;

use App\Core\Contracts\Importers\ContestImporter;
use App\Core\Contracts\Importers\ProblemImporter;
use App\Core\Contracts\Importers\SubmissionImporter;
use App\Core\Contracts\Importers\RatingChangeImporter;
use App\Core\Contracts\Importers\StandingsImporter;
use App\Core\Contracts\Importers\UserImporter;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\UserDTO;

interface PlatformAdapter
{
    //==================================Used==================================
    // Getters
    public function getContests(): array;
    public function getContestProblems(string $contestId): array;
    public function getSubmissions(string $contestId): array;
    public function getRatingChanges(string $contestId): array;
    public function getStandings(string $id): ContestStandingsDTO;
    public function getUser(string $username): UserDTO;

    // Importers
    public function contestImporter(): ContestImporter;
    public function problemImporter(): ProblemImporter;
    public function submissionImporter(): SubmissionImporter;
    public function ratingChangeImporter(): RatingChangeImporter;
    public function standingsImporter(): StandingsImporter;
    public function userImporter(): UserImporter;


    //==================================Unused==================================
    public function getContest(string $id): ContestStandingsDTO;
    public function getProblems(): array;
    public function getUserRatingHistory(string $handle): array;
}
