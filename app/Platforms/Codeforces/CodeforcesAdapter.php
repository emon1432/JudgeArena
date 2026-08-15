<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces;

use App\Core\Contracts\Importers\ContestImporter as ContestImporterContract;
use App\Core\Contracts\Importers\ProblemImporter as ProblemImporterContract;
use App\Core\Contracts\Importers\UserImporter as UserImporterContract;
use App\Core\Contracts\Importers\UserRatingHistoryImporter as UserRatingHistoryImporterContract;
use App\Core\Contracts\Importers\UserStandingImporter as UserStandingImporterContract;
use App\Core\Contracts\Importers\UserSubmissionImporter as UserSubmissionImporterContract;
use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\UserDTO;
use App\Platforms\Codeforces\Importers\ContestImporter;
use App\Platforms\Codeforces\Importers\ProblemImporter;
use App\Platforms\Codeforces\Importers\UserImporter;
use App\Platforms\Codeforces\Importers\UserRatingHistoryImporter;
use App\Platforms\Codeforces\Importers\UserStandingImporter;
use App\Platforms\Codeforces\Importers\UserSubmissionImporter;
use App\Platforms\Codeforces\Services\Contests;
use App\Platforms\Codeforces\Services\Problems;
use App\Platforms\Codeforces\Services\Users;
use App\Platforms\Codeforces\Transformers\ContestTransformer;
use App\Platforms\Codeforces\Transformers\ProblemTransformer;
use App\Platforms\Codeforces\Transformers\StandingsTransformer;
use App\Platforms\Codeforces\Transformers\SubmissionTransformer;
use App\Platforms\Codeforces\Transformers\UserTransformer;

class CodeforcesAdapter implements PlatformAdapter
{
    public function __construct(
        private readonly Contests $contests,
        private readonly Problems $problems,
        private readonly Users $users,
        private readonly ContestTransformer $contestTransformer,
        private readonly ProblemTransformer $problemTransformer,
        private readonly UserTransformer $userTransformer,
        private readonly SubmissionTransformer $submissionTransformer,
        private readonly StandingsTransformer $standingsTransformer,
    ) {
    }

    //==================================Used==================================

    //===============================Getters==================================
    public function getContests(): array
    {
        return $this->contestTransformer->fromApiContests(
            $this->contests->list(),
        );
    }

    public function getContestProblems(string $contestId): array
    {
        $contest = $this->contests->standings((int) $contestId);
        return $this->problemTransformer->fromApiProblems($contest->problems);
    }

    public function getSubmissions(string $contestId): array
    {
        return $this->submissionTransformer->fromApiSubmissions(
            $this->contests->status((int) $contestId)
        );
    }

    public function getRatingChanges(string $contestId): array
    {
        return $this->contests->ratingChanges($contestId);
    }

    public function getStandings(string $id): ContestStandingsDTO
    {
        return $this->standingsTransformer
            ->fromApiStandings($this->contests->standings((int) $id));
    }

    public function getUserRatingHistory(string $handle): array
    {
        return $this->users->ratingHistory($handle);
    }

    public function getUserSubmissions(array $params): array
    {
        return $this->submissionTransformer->fromApiSubmissions(
            $this->users->submissions($params['handle'], $params['from'], $params['count'])
        );
    }

    public function getUser(string $username): UserDTO
    {
        return $this->userTransformer->fromApiUser($this->users->info($username));
    }

    //===============================Importers==================================
    public function contestImporter(): ContestImporterContract
    {
        return app(ContestImporter::class);
    }

    public function problemImporter(): ProblemImporterContract
    {
        return app(ProblemImporter::class);
    }

    public function userRatingHistoryImporter(): UserRatingHistoryImporterContract
    {
        return app(UserRatingHistoryImporter::class);
    }

    public function userSubmissionImporter(): UserSubmissionImporterContract
    {
        return app(UserSubmissionImporter::class);
    }

    public function userStandingImporter(): UserStandingImporterContract
    {
        return app(UserStandingImporter::class);
    }

    public function userImporter(): UserImporterContract
    {
        return app(UserImporter::class);
    }
}

