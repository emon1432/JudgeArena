<?php

namespace App\Platforms\Codeforces;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Platforms\Codeforces\Transformers\ContestTransformer;
use App\Platforms\Codeforces\Transformers\ProblemTransformer;
use App\Platforms\Codeforces\Transformers\SubmissionTransformer;
use App\Platforms\Codeforces\Transformers\UserTransformer;
use App\Core\DTOs\UserDTO;
use App\Core\DTOs\ContestStandingsDTO;
use App\Platforms\Codeforces\Services\Contests;
use App\Platforms\Codeforces\Services\Problems;
use App\Platforms\Codeforces\Services\Users;

class CodeforcesAdapter implements PlatformAdapter
{
	public function __construct(
		private readonly Contests $contests = new Contests(),
		private readonly Problems $problems = new Problems(),
		private readonly Users $users = new Users(),
		private readonly ContestTransformer $contestTransformer = new ContestTransformer(),
		private readonly ProblemTransformer $problemTransformer = new ProblemTransformer(),
		private readonly UserTransformer $userTransformer = new UserTransformer(),
		private readonly SubmissionTransformer $submissionTransformer = new SubmissionTransformer(),
	) {
	}

	public function getContests(): array
	{
		return $this->contestTransformer->fromApiContests(
			$this->contests->list(),
		);
	}

	public function getContest(string $id): ContestStandingsDTO
	{
		return (new \App\Platforms\Codeforces\Transformers\StandingsTransformer())
			->fromApiStandings($this->contests->standings((int) $id));
	}

	public function getProblems(): array
	{
		$result = $this->problems->list();

		return [
			'problems' => $this->problemTransformer->fromApiProblems($result['problems'] ?? []),
			'problemStatistics' => $result['problemStatistics'] ?? [],
		];
	}

	public function getUser(string $username): UserDTO
	{
		return $this->userTransformer->fromApiUser($this->users->info($username));
	}

	public function getSubmissions(string $username, int $from = 1, int $count = 100): array
	{
		return $this->submissionTransformer->fromApiSubmissions(
			$this->users->status($username, $from, $count),
		);
	}

	public function getRatingChanges(int $contestId): array
	{
		return $this->contests->ratingChanges($contestId);
	}
}

