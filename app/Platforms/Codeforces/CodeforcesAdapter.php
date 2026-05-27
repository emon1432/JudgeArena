<?php

namespace App\Platforms\Codeforces;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\UserDTO;
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
		return app(StandingsTransformer::class)
			->fromApiStandings($this->contests->standings((int) $id));
	}

	/**
	 * @return array{problems: \App\Core\DTOs\ProblemDTO[], problemStatistics: array<int, array<string, mixed>>}
	 *
	 * Consider extracting a dedicated DTO for this mixed-shape response in a future pass.
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
			$this->users->status($username, $from, $count),
		);
	}

	/** @return array<int, array<string, mixed>> */
	public function getRatingChanges(int $contestId): array
	{
		return $this->contests->ratingChanges($contestId);
	}
	public function __construct(
		private readonly Contests $contests,
		private readonly Problems $problems,
		private readonly Users $users,
		private readonly ContestTransformer $contestTransformer,
		private readonly ProblemTransformer $problemTransformer,
		private readonly UserTransformer $userTransformer,
		private readonly SubmissionTransformer $submissionTransformer,
	) {}
}

