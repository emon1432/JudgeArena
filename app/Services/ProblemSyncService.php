<?php

namespace App\Services;

use App\Models\Contest;
use App\Models\Problem;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProblemSyncService
{
    public function __construct(
        private readonly Contest $contestModel,
        private readonly Problem $problemModel,
        private readonly CodeforcesAdapter $codeforcesAdapter,
        private readonly AtCoderAdapter $atCoderAdapter,
    ) {}

    /**
     * Sync problems only for contests that do not already have imported problems.
     *
     * @return array{contests_checked:int, contests_synced:int, contests_skipped:int, problems_fetched:int, problems_created:int, problems_updated:int}
     */
    public function sync(?string $platformSlug = null): array
    {
        $query = $this->contestModel->newQuery()
            ->with('platform')
            ->withCount('problems');

        $normalizedPlatformSlug = $this->normalizePlatformSlug($platformSlug);
        if ($normalizedPlatformSlug !== null) {
            $query->whereHas('platform', function ($platformQuery) use ($normalizedPlatformSlug): void {
                $platformQuery->where('slug', $normalizedPlatformSlug);
            });
        }

        $contests = $query->get();
        $pendingContests = $contests->filter(
            fn (Contest $contest): bool => (int) ($contest->problems_count ?? 0) === 0
        );

        $stats = [
            'contests_checked' => $contests->count(),
            'contests_synced' => 0,
            'contests_skipped' => $contests->filter(
                fn (Contest $contest): bool => (int) ($contest->problems_count ?? 0) > 0
            )->count(),
            'problems_fetched' => 0,
            'problems_created' => 0,
            'problems_updated' => 0,
        ];

        /** @var Collection<string, Collection<int, Contest>> $contestsByPlatform */
        $contestsByPlatform = $pendingContests->groupBy(function (Contest $contest): string {
            return (string) ($contest->platform?->slug ?? '');
        });

        foreach ($contestsByPlatform as $platformSlugKey => $platformContests) {
            $adapter = $this->resolveAdapter($platformSlugKey);

            if ($adapter === null) {
                $stats['contests_skipped'] += $platformContests->count();

                continue;
            }

            foreach ($platformContests as $contest) {
                try {
                    $result = $adapter->getContestProblems((string) $contest->platform_contest_id);
                    $problems = $result['problems'] ?? [];

                    if (! is_array($problems)) {
                        $problems = [];
                    }

                    $stats['problems_fetched'] += count($problems);

                    foreach ($problems as $problemDto) {
                        $problem = $this->problemModel->newQuery()->updateOrCreate(
                            [
                                'platform_id' => $contest->platform_id,
                                'platform_problem_id' => $problemDto->platformProblemId,
                            ],
                            [
                                'contest_id' => $contest->id,
                                'slug' => slugify($problemDto->title),
                                'name' => $problemDto->title,
                                'code' => $this->extractCode($problemDto->platformProblemId),
                                'points' => $problemDto->points,
                                'rating' => $problemDto->rating,
                                'tags' => $problemDto->tags,
                                'last_synced_at' => now(),
                                'metadata' => [
                                    'source' => 'contest-scoped-sync',
                                    'platform' => $problemDto->platform,
                                    'contest_platform_id' => $contest->platform_contest_id,
                                ],
                                'raw' => $problemDto->raw,
                                'status' => 'Active',
                            ]
                        );

                        if ($problem->wasRecentlyCreated) {
                            $stats['problems_created']++;

                            continue;
                        }

                        $stats['problems_updated']++;
                    }

                    $stats['contests_synced']++;
                } catch (Throwable) {
                    $stats['contests_skipped']++;
                    Log::error('Failed to sync problems for contest ID ' . $contest->id . ': ' . $contest->name);
                }
            }
        }

        return $stats;
    }

    private function resolveAdapter(string $platformSlug): CodeforcesAdapter|AtCoderAdapter|null
    {
        return match (strtolower(trim($platformSlug))) {
            'codeforces' => $this->codeforcesAdapter,
            'atcoder' => $this->atCoderAdapter,
            default => null,
        };
    }

    private function normalizePlatformSlug(?string $platformSlug): ?string
    {
        $platformSlug = trim((string) $platformSlug);

        return $platformSlug === '' ? null : strtolower($platformSlug);
    }

    private function extractCode(string $platformProblemId): ?string
    {
        $platformProblemId = trim($platformProblemId);
        if ($platformProblemId === '') {
            return null;
        }

        if (preg_match('/([A-Za-z0-9]+)$/', $platformProblemId, $matches)) {
            return strtoupper($matches[1]);
        }

        return null;
    }
}
