<?php

namespace App\Services\PlatformSync;

use App\Models\Platform;
use App\Platforms\Codeforces\CodeforcesClient;
use App\Platforms\LeetCode\LeetCodeClient;
use App\Repositories\Global\ContestRepository;
use App\Repositories\Global\ProblemRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CatalogSyncService
{
    public function __construct(
        private readonly ContestRepository $contestRepository,
        private readonly ProblemRepository $problemRepository,
        private readonly CodeforcesClient $codeforcesClient,
        private readonly LeetCodeClient $leetCodeClient,
    ) {
    }

    public function syncPlatform(Platform $platform, int $maxAttempts = 3): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxAttempts) {
            $attempt++;

            try {
                return match ($platform->name) {
                    'codeforces' => $this->syncCodeforces($platform),
                    'leetcode' => $this->syncLeetCode($platform),
                    default => [
                        'contests_synced' => 0,
                        'problems_synced' => 0,
                        'skipped' => true,
                        'reason' => 'Catalog sync not implemented for this platform yet.',
                    ],
                };
            } catch (\Throwable $exception) {
                $lastException = $exception;

                if ($attempt >= $maxAttempts) {
                    break;
                }

                $delayMs = 500 * $attempt;
                usleep($delayMs * 1000);
            }
        }

        throw $lastException ?? new \RuntimeException('Catalog sync failed with unknown error.');
    }

    private function syncCodeforces(Platform $platform): array
    {
        $contestRows = [];
        $contests = $this->codeforcesClient->fetchContestList();

        foreach ($contests as $contest) {
            $contestId = (string) ($contest['id'] ?? '');
            if ($contestId === '') {
                continue;
            }

            $name = trim((string) ($contest['name'] ?? 'Codeforces Contest'));
            $phase = Str::lower((string) ($contest['phase'] ?? ''));
            $duration = Arr::get($contest, 'durationSeconds');
            $startTimeSeconds = Arr::get($contest, 'startTimeSeconds');

            $startTime = $startTimeSeconds ? now()->setTimestamp((int) $startTimeSeconds) : null;
            $endTime = ($startTime && $duration) ? (clone $startTime)->addSeconds((int) $duration) : null;

            $contestRows[] = [
                'platform_contest_id' => $contestId,
                'slug' => Str::slug($name) . '-' . $contestId,
                'name' => $name,
                'description' => null,
                'type' => 'contest',
                'phase' => $phase !== '' ? $phase : null,
                'duration_seconds' => $duration,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'url' => 'https://codeforces.com/contest/' . $contestId,
                'participant_count' => null,
                'is_rated' => false,
                'tags' => null,
                'raw' => $contest,
                'status' => 'Active',
            ];
        }

        $this->contestRepository->upsertMany($platform->id, $contestRows);

        $contestMap = $platform->contests()
            ->pluck('id', 'platform_contest_id')
            ->toArray();

        $problemRows = [];
        $problemTags = $this->codeforcesClient->fetchProblemTags();

        foreach ($problemTags as $platformProblemId => $problemData) {
            if (! preg_match('/^(\d+)([A-Za-z0-9]+)$/', (string) $platformProblemId, $matches)) {
                continue;
            }

            $contestId = $matches[1];
            $problemIndex = $matches[2];
            $name = trim((string) ($problemData['name'] ?? ''));
            $slug = $name !== '' ? Str::slug($name) . '-' . $platformProblemId : null;

            $problemRows[] = [
                'contest_id' => $contestMap[$contestId] ?? null,
                'platform_problem_id' => (string) $platformProblemId,
                'slug' => $slug,
                'name' => $name !== '' ? $name : ('Problem ' . $platformProblemId),
                'code' => $problemIndex,
                'description' => null,
                'difficulty' => null,
                'rating' => Arr::get($problemData, 'rating'),
                'points' => null,
                'accuracy' => null,
                'acceptance_rate' => null,
                'time_limit_ms' => null,
                'memory_limit_mb' => null,
                'total_submissions' => 0,
                'accepted_submissions' => 0,
                'solved_count' => 0,
                'tags' => Arr::get($problemData, 'tags', []),
                'topics' => Arr::get($problemData, 'tags', []),
                'url' => $this->codeforcesClient->getProblemUrl((int) $contestId, $problemIndex),
                'editorial_url' => null,
                'raw' => $problemData,
                'status' => 'Active',
                'is_premium' => false,
            ];
        }

        $this->problemRepository->upsertMany($platform->id, $problemRows);

        Log::info('Codeforces catalog synced', [
            'platform_id' => $platform->id,
            'contests' => count($contestRows),
            'problems' => count($problemRows),
        ]);

        return [
            'contests_synced' => count($contestRows),
            'problems_synced' => count($problemRows),
            'skipped' => false,
        ];
    }

    private function syncLeetCode(Platform $platform): array
    {
        $contestRows = [];
        $contests = $this->leetCodeClient->fetchContestList();

        foreach ($contests as $contest) {
            $slug = (string) ($contest['titleSlug'] ?? $contest['title_slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $name = trim((string) ($contest['title'] ?? $contest['name'] ?? 'LeetCode Contest'));
            $startTimestamp = (int) ($contest['startTime'] ?? $contest['start_time'] ?? 0);
            $durationSeconds = (int) ($contest['duration'] ?? $contest['durationSeconds'] ?? 0);

            $startTime = $startTimestamp > 0 ? now()->setTimestamp($startTimestamp) : null;
            $endTime = ($startTime && $durationSeconds > 0) ? (clone $startTime)->addSeconds($durationSeconds) : null;

            $contestRows[] = [
                'platform_contest_id' => $slug,
                'slug' => $slug,
                'name' => $name,
                'description' => $contest['description'] ?? null,
                'type' => 'contest',
                'phase' => $this->resolvePhase($startTime, $endTime),
                'duration_seconds' => $durationSeconds > 0 ? $durationSeconds : null,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'url' => 'https://leetcode.com/contest/' . $slug,
                'participant_count' => null,
                'is_rated' => ! (bool) ($contest['isVirtual'] ?? false),
                'tags' => ['leetcode', ((bool) ($contest['containsPremium'] ?? false) ? 'contains-premium' : 'no-premium')],
                'raw' => $contest,
                'status' => 'Active',
            ];
        }

        $this->contestRepository->upsertMany($platform->id, $contestRows);

        $problemRows = [];
        $problems = $this->leetCodeClient->fetchProblemCatalog();

        foreach ($problems as $problem) {
            $titleSlug = (string) ($problem['titleSlug'] ?? '');
            $frontendId = trim((string) ($problem['questionFrontendId'] ?? ''));
            $internalId = trim((string) ($problem['questionId'] ?? ''));

            if ($titleSlug === '' && $frontendId === '' && $internalId === '') {
                continue;
            }

            $name = trim((string) ($problem['title'] ?? ''));
            $slug = $titleSlug !== ''
                ? $titleSlug
                : (($internalId !== '' ? 'leetcode-' . $internalId : 'leetcode-' . ($frontendId !== '' ? $frontendId : Str::slug($name))));
            $platformProblemId = $slug;
            $difficulty = Str::lower((string) ($problem['difficulty'] ?? ''));
            $acRate = is_numeric($problem['acRate'] ?? null) ? round((float) $problem['acRate'], 2) : null;

            $topicTags = collect($problem['topicTags'] ?? [])
                ->pluck('name')
                ->filter()
                ->values()
                ->all();

            $problemRows[] = [
                'contest_id' => null,
                'platform_problem_id' => (string) $platformProblemId,
                'slug' => $slug,
                'name' => $name !== '' ? $name : ('Problem ' . $platformProblemId),
                'code' => $frontendId !== '' ? $frontendId : null,
                'description' => null,
                'difficulty' => $difficulty !== '' ? $difficulty : null,
                'rating' => null,
                'points' => null,
                'accuracy' => $acRate,
                'acceptance_rate' => $acRate,
                'time_limit_ms' => null,
                'memory_limit_mb' => null,
                'total_submissions' => 0,
                'accepted_submissions' => 0,
                'solved_count' => 0,
                'tags' => $topicTags,
                'topics' => $topicTags,
                'url' => $titleSlug !== ''
                    ? ('https://leetcode.com/problems/' . $titleSlug . '/')
                    : ('https://leetcode.com/problemset/all/?search=' . urlencode($name !== '' ? $name : $platformProblemId)),
                'editorial_url' => $titleSlug !== ''
                    ? ('https://leetcode.com/problems/' . $titleSlug . '/editorial/')
                    : null,
                'raw' => $problem,
                'status' => isset($problem['status']) && $problem['status'] ? 'Solved' : 'Active',
                'is_premium' => (bool) ($problem['isPaidOnly'] ?? false),
            ];
        }

        $this->problemRepository->upsertMany($platform->id, $problemRows);

        Log::info('LeetCode catalog synced', [
            'platform_id' => $platform->id,
            'contests' => count($contestRows),
            'problems' => count($problemRows),
        ]);

        return [
            'contests_synced' => count($contestRows),
            'problems_synced' => count($problemRows),
            'skipped' => false,
        ];
    }

    private function resolvePhase($startTime, $endTime): ?string
    {
        if (! $startTime) {
            return null;
        }

        $now = now();

        if ($now->lt($startTime)) {
            return 'before';
        }

        if ($endTime && $now->gt($endTime)) {
            return 'finished';
        }

        return 'coding';
    }
}
