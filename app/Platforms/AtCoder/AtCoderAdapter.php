<?php

namespace App\Platforms\AtCoder;

use App\Core\Contracts\Importers\ContestImporter as ContestImporterContract;
use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\RatingChangeDTO;
use App\Core\DTOs\UserDTO;
use App\Platforms\AtCoder\Importers\ContestImporter;
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
    /** @return \App\Core\DTOs\ContestDTO[] */
    public function getContests(): array
    {
        return $this->contestTransformer->fromApiContests(
            $this->contests->list(),
        );
    }

    public function contestImporter(): ContestImporterContract
    {
        return app(ContestImporter::class);
    }

    /** @return ContestStandingsDTO */
    public function getContest(string $id): ContestStandingsDTO
    {
        return $this->standingsTransformer
            ->fromApiStandings($this->contests->standings($id));
    }

    /**
     * @return array{problems: \App\Core\DTOs\ProblemDTO[], problemStatistics: array<int, array<string, mixed>>}
     */
    public function getProblems(): array
    {
        $result = $this->problems->list();

        return [
            'problems' => $this->problemTransformer->fromApiProblems($result['problems'] ?? []),
            'problemStatistics' => $result['problemStatistics'] ?? [],
        ];
    }

    /**
     * @return array{problems: \App\Core\DTOs\ProblemDTO[], problemStatistics: array<int, array<string, mixed>>}
     */
    public function getContestProblems(string $contestId): array
    {
        $tasks = $this->contests->tasks($contestId);

        return [
            'problems' => $this->problemTransformer->fromApiProblems(
                \App\Platforms\AtCoder\Mappers\AtCoderProblemMapper::fromNormalizedList(
                    \App\Platforms\AtCoder\Support\ResponseNormalizer::problems($tasks)
                )
            ),
            'problemStatistics' => [],
        ];
    }

    /** @return UserDTO */
    public function getUser(string $username): UserDTO
    {
        return $this->userTransformer->fromApiUser($this->users->info($username));
    }

    /** @return \App\Core\DTOs\SubmissionDTO[] */
    public function getSubmissions(string $contestId, string $username): array
    {
        return $this->submissionTransformer->fromApiSubmissions(
            $this->users->submissions($contestId, $username)
        );
    }

    /** @return RatingChangeDTO[] */
    public function getRatingChanges(string $contestId): array
    {
        return $this->contests->ratingChanges((string) $contestId);
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
