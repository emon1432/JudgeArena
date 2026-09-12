<?php

declare(strict_types=1);

namespace App\Platforms\AtCoder;

use App\Core\Contracts\Importers\ContestImporter as ContestImporterContract;
use App\Core\Contracts\Importers\ProblemImporter as ProblemImporterContract;
use App\Core\Contracts\Importers\UserImporter as UserImporterContract;
use App\Core\Contracts\Importers\UserRatingHistoryImporter as UserRatingHistoryImporterContract;
use App\Core\Contracts\Importers\UserStandingImporter as UserStandingImporterContract;
use App\Core\Contracts\Importers\UserSubmissionImporter as UserSubmissionImporterContract;
use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\UserDTO;
use App\Platforms\AtCoder\Importers\ContestImporter;
use App\Platforms\AtCoder\Importers\ProblemImporter;
use App\Platforms\AtCoder\Importers\UserImporter;
use App\Platforms\AtCoder\Importers\UserRatingHistoryImporter;
use App\Platforms\AtCoder\Importers\UserStandingImporter;
use App\Platforms\AtCoder\Importers\UserSubmissionImporter;
use App\Platforms\AtCoder\Services\Contests;
use App\Platforms\AtCoder\Services\Problems;
use App\Platforms\AtCoder\Services\Users;
use App\Platforms\AtCoder\Transformers\ContestTransformer;
use App\Platforms\AtCoder\Transformers\ProblemTransformer;
use App\Platforms\AtCoder\Transformers\StandingsTransformer;
use App\Platforms\AtCoder\Transformers\SubmissionTransformer;
use App\Platforms\AtCoder\Transformers\UserTransformer;
use App\Services\StandingsCacheService;

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
        private readonly StandingsCacheService $standingsCacheService,
    ) {}

    // ================================Used==================================

    // ================================Getters==================================
    public function getContests(): array
    {
        return $this->contestTransformer->fromApiContests(
            $this->contests->all()
        );
    }

    public function getContestProblems(string $contestId): array
    {
        return $this->problemTransformer->fromApiProblems(
            $this->problems->getContestProblems($contestId)
        );
    }

    public function getUserRatingHistory(string $handle): array
    {
        return $this->users->ratingHistory($handle);
    }

    /**
     * @param array{
     *     handle:string,
     *     contestId?:string,
     *     from?:int,
     *     count?:int,
     *     stopSubmissionId?:string
     * } $params
     * @return array{
     *     submissions: SubmissionDTO[],
     *     reached_stop: bool
     * }
     */
    public function getUserSubmissions(array $params): array
    {
        $response = $this->users->submissions($params);

        return [
            'submissions' => $this->submissionTransformer
                ->fromApiSubmissions($response['submissions']),

            'reached_stop' => $response['reached_stop'],
        ];
    }

    public function getUserStandings(string $id): ContestStandingsDTO
    {
        if ($this->standingsCacheService->has('atcoder', $id)) {
            $cached = $this->standingsCacheService->get('atcoder', $id);
            if ($cached instanceof ContestStandingsDTO) {
                return $cached;
            }
        }

        $standings = $this->standingsTransformer
            ->fromApiStandings($this->contests->standings($id));

        $phase = strtoupper((string) ($standings->contest->phase ?? ''));
        if ($phase !== 'BEFORE') {
            $this->standingsCacheService->put('atcoder', $id, $standings);
        }

        return $standings;
    }

    public function getUser(string $username): UserDTO
    {
        return $this->userTransformer->fromApiUser($this->users->info($username));
    }

    // ===============================Importers==================================
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
