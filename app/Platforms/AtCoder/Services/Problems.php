<?php

namespace App\Platforms\AtCoder\Services;

use App\Models\Contest;
use App\Platforms\AtCoder\Mappers\AtCoderProblemMapper;
use App\Platforms\AtCoder\Support\ResponseNormalizer;
use Illuminate\Support\Facades\Log;

class Problems
{
    public function __construct(
        private readonly Contest $contestModel,
        private readonly Contests $contestsService,
    ) {}

    /**
     * Legacy whole-platform problem crawl.
      *
      * Expensive validation-only operation.
      * Not intended for production synchronization.
     *
     * Prefer {@see getContestProblems()} for contest-scoped synchronization.
     * This method remains for backward compatibility and should not be used
     * for large-scale sync runs.
     *
     * @return array{problems: \App\Platforms\AtCoder\DTOs\AtCoderProblemDTO[], problemStatistics: array<int, array<string, mixed>>}
     */
    public function list(): array
    {
        Log::warning('AtCoder whole-platform problem crawl invoked', [
            'purpose' => 'validation-only',
        ]);

        $problems = [];
        $contests = $this->contestModel->newQuery()
            ->whereHas('platform', function ($platformQuery): void {
                $platformQuery->where('slug', 'atcoder');
            })
            ->get();

        foreach ($contests as $contest) {
            $contestProblems = $this->getContestProblems((string) $contest->platform_contest_id);
            $problems = array_merge($problems, $contestProblems['problems']);
        }

        Log::info('AtCoder whole-platform problem crawl completed', [
            'contest_count' => $contests->count(),
            'problem_count' => count($problems),
        ]);

        return [
            'problems' => $problems,
            'problemStatistics' => [],
        ];
    }

    /**
     * Contest-scoped problem synchronization path.
     *
     * @return array{problems: \App\Platforms\AtCoder\DTOs\AtCoderProblemDTO[], problemStatistics: array<int, array<string, mixed>>}
     */
    public function getContestProblems(string $contestId): array
    {
        $tasks = $this->contestsService->tasks($contestId);

        return [
            'problems' => AtCoderProblemMapper::fromNormalizedList(
                ResponseNormalizer::problems($tasks)
            ),
            'problemStatistics' => [],
        ];
    }
}
