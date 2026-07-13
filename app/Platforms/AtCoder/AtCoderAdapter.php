<?php

namespace App\Platforms\AtCoder;

use App\Core\Contracts\Importers\ContestImporter as ContestImporterContract;
use App\Core\Contracts\Importers\ProblemImporter as ProblemImporterContract;
use App\Core\Contracts\Importers\SubmissionImporter as SubmissionImporterContract;
use App\Core\Contracts\Importers\RatingChangeImporter as RatingChangeImporterContract;
use App\Core\Contracts\Importers\StandingsImporter as StandingsImporterContract;
use App\Core\Contracts\Importers\UserImporter as UserImporterContract;
use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\UserDTO;
use App\Platforms\AtCoder\Importers\ContestImporter;
use App\Platforms\AtCoder\Importers\RatingChangeImporter;
use App\Platforms\AtCoder\Importers\ProblemImporter;
use App\Platforms\AtCoder\Importers\SubmissionImporter;
use App\Platforms\AtCoder\Importers\StandingsImporter;
use App\Platforms\AtCoder\Importers\UserImporter;
use App\Platforms\AtCoder\Services\Contests;
use App\Platforms\AtCoder\Services\Problems;
use App\Platforms\AtCoder\Services\Users;
use App\Platforms\AtCoder\Transformers\ContestTransformer;
use App\Platforms\AtCoder\Transformers\ProblemTransformer;
use App\Platforms\AtCoder\Transformers\StandingsTransformer;
use App\Platforms\AtCoder\Transformers\SubmissionTransformer;
use App\Platforms\AtCoder\Transformers\UserTransformer;

class AtCoderAdapter implements PlatformAdapter
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
    ) {}

    //================================Used==================================

    //================================Getters==================================
    public function getContests(): array
    {
        return $this->contestTransformer->fromApiContests(
            $this->contests->list(),
        );
    }

    public function getContestProblems(string $contestId): array
    {
        return $this->problemTransformer->fromApiProblems(
            $this->problems->getContestProblems($contestId)
        );
    }

    public function getSubmissions(string $contestId): array
    {
        return $this->submissionTransformer->fromApiSubmissions(
            $this->contests->submissions($contestId)
        );
    }

    public function getRatingChanges(string $contestId): array
    {
        return $this->contests->ratingChanges((string) $contestId);
    }

    public function getStandings(string $id): ContestStandingsDTO
    {
        return $this->standingsTransformer
            ->fromApiStandings($this->contests->standings($id));
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

    public function submissionImporter(): SubmissionImporterContract
    {
        return app(SubmissionImporter::class);
    }

    public function ratingChangeImporter(): RatingChangeImporterContract
    {
        return app(RatingChangeImporter::class);
    }

    public function standingsImporter(): StandingsImporterContract
    {
        return app(StandingsImporter::class);
    }

    public function userImporter(): UserImporterContract
    {
        return app(UserImporter::class);
    }

    //================================Unused==================================
    public function getContest(string $id): ContestStandingsDTO
    {
        return $this->standingsTransformer
            ->fromApiStandings($this->contests->standings($id));
    }

    public function getProblems(): array
    {
        return [];
    }

    public function getUserRatingHistory(string $handle): array
    {
        return $this->users->ratingHistory($handle);
    }
}
