<?php

declare(strict_types=1);

namespace App\Core\Contracts\Platforms;

use App\Core\Contracts\Importers\ContestImporter;
use App\Core\Contracts\Importers\ProblemImporter;
use App\Core\Contracts\Importers\UserImporter;
use App\Core\Contracts\Importers\UserRatingHistoryImporter;
use App\Core\Contracts\Importers\UserStandingImporter;
use App\Core\Contracts\Importers\UserSubmissionImporter;
use App\Core\DTOs\ContestDTO;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ProblemDTO;
use App\Core\DTOs\RatingChangeDTO;
use App\Core\DTOs\SubmissionDTO;
use App\Core\DTOs\UserDTO;

interface PlatformAdapter
{
    //===============================Getters==================================

    /**
     * @param (callable(array): bool)|null $pageProcessor
     * @param bool $fullSync
     * @return ContestDTO[]
     */
    public function getContests(?callable $pageProcessor = null, bool $fullSync = false): array;

    /**
     * @param string $contestId
     * @return ProblemDTO[]
     */
    public function getContestProblems(string $contestId): array;

    /**
     * @param string $handle
     * @return RatingChangeDTO[]
     */
    public function getUserRatingHistory(string $handle): array;

    /**
     * @param array{
     *     handle:string,
     *     contestId?:string,
     *     from?:int,
     *     count?:int,
     *     stopSubmissionId?:string
     * } $params
     *
     * @return array{
     *     submissions: SubmissionDTO[],
     *     reached_stop: bool
     * }
     */
    public function getUserSubmissions(array $params): array;

    /**
     * @param string $id
     * @return ContestStandingsDTO
     */
    public function getUserStandings(string $id): ContestStandingsDTO;

    /**
     * @param string $username
     * @return UserDTO
     */
    public function getUser(string $username): UserDTO;


    //==============================Importers==================================
    public function contestImporter(): ContestImporter;
    public function problemImporter(): ProblemImporter;
    public function userRatingHistoryImporter(): UserRatingHistoryImporter;
    public function userSubmissionImporter(): UserSubmissionImporter;
    public function userStandingImporter(): UserStandingImporter;
    public function userImporter(): UserImporter;
}
