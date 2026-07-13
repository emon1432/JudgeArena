<?php

namespace App\Platforms\Codeforces;

use App\Core\Contracts\Importers\ContestImporter as ContestImporterContract;
use App\Core\Contracts\Importers\ProblemImporter as ProblemImporterContract;
use App\Core\Contracts\Importers\SubmissionImporter as SubmissionImporterContract;
use App\Core\Contracts\Importers\RatingChangeImporter as RatingChangeImporterContract;
use App\Core\Contracts\Importers\StandingsImporter as StandingsImporterContract;
use App\Core\Contracts\Importers\UserImporter as UserImporterContract;
use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\RatingChangeDTO;
use App\Core\DTOs\UserDTO;
use App\Platforms\Codeforces\Importers\ContestImporter;
use App\Platforms\Codeforces\Importers\RatingChangeImporter;
use App\Platforms\Codeforces\Importers\ProblemImporter;
use App\Platforms\Codeforces\Importers\SubmissionImporter;
use App\Platforms\Codeforces\Importers\StandingsImporter;
use App\Platforms\Codeforces\Importers\UserImporter;
use App\Platforms\Codeforces\Services\Contests;
use App\Platforms\Codeforces\Services\Problems;
use App\Platforms\Codeforces\Services\Users;
use App\Platforms\Codeforces\Transformers\ContestTransformer;
use App\Platforms\Codeforces\Transformers\ProblemTransformer;
use App\Platforms\Codeforces\Transformers\SubmissionTransformer;
use App\Platforms\Codeforces\Transformers\UserTransformer;
use App\Platforms\Codeforces\Transformers\StandingsTransformer;

class CodeforcesAdapter implements PlatformAdapter
{
    //used
    public function getContests(): array
    {
        return $this->contestTransformer->fromApiContests(
            $this->contests->list(),
        );
    }

    //used
    public function contestImporter(): ContestImporterContract
    {
        return app(ContestImporter::class);
    }

    //used
    public function getContestProblems(string $contestId): array
    {
        $contest = $this->contests->standings((int) $contestId);
        return $this->problemTransformer->fromApiProblems($contest->problems);
    }

    //used
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






    /**
     * @return \App\Core\DTOs\ContestStandingsDTO
     */
    public function getContest(string $id): ContestStandingsDTO
    {
        return $this->standingsTransformer
            ->fromApiStandings($this->contests->standings((int) $id));
    }

    /**
     * @return \App\Core\DTOs\ProblemDTO[]
     */
    public function getProblems(): array
    {
        return $this->problemTransformer->fromApiProblems($this->problems->list());
    }

    /** @return UserDTO */
    public function getUser(string $username): UserDTO
    {
        return $this->userTransformer->fromApiUser($this->users->info($username));
    }

    public function userImporter(): UserImporterContract
    {
        return app(UserImporter::class);
    }

    /** @return \App\Core\DTOs\SubmissionDTO[] */
    public function getSubmissions(string $contestId): array
    {
        return $this->submissionTransformer->fromApiSubmissions(
            $this->contests->status((int) $contestId)
        );
    }

    /** @return RatingChangeDTO[] */
    public function getRatingChanges(string $contestId): array
    {
        return $this->contests->ratingChanges($contestId);
    }

    /** @return RatingChangeDTO[] */
    public function getUserRatingHistory(string $handle): array
    {
        return $this->users->ratingHistory($handle);
    }

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
}
