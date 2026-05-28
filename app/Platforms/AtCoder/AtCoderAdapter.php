<?php

namespace App\Platforms\AtCoder;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\UserDTO;
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

    /** @return UserDTO */
    public function getUser(string $username): UserDTO
    {
        return $this->userTransformer->fromApiUser($this->users->info($username));
    }

    /** @return \App\Core\DTOs\SubmissionDTO[] */
    public function getSubmissions(string $username, int $from = 1, int $count = 100): array
    {
        return $this->submissionTransformer->fromApiSubmissions(
            $this->users->submissions($username, $from, $count),
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function getRatingChanges(int $contestId): array
    {
        return $this->contests->ratingChanges((string) $contestId);
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

