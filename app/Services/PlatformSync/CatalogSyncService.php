<?php

namespace App\Services\PlatformSync;

use App\Models\Problem;
use App\Models\Platform;
use App\Platforms\AtCoder\AtCoderClient;
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
        private readonly AtCoderClient $atCoderClient,
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
                    'atcoder' => $this->syncAtCoder($platform),
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

    private function syncAtCoder(Platform $platform): array
    {
        $archivePages = max(1, (int) config('platforms.atcoder.catalog.archive_pages', 2));
        $maxContestsForProblems = max(1, (int) config('platforms.atcoder.catalog.max_contests_for_problems', 120));
        $taskDelayMs = max(0, (int) config('platforms.atcoder.catalog.task_delay_ms', 120));
        $resourceModeEnabled = (bool) config('platforms.atcoder.catalog.resource_mode_enabled', true);

        $contests = [];
        $resourceProblems = [];
        $resourcePairs = [];

        if ($resourceModeEnabled) {
            $resourceContests = $this->atCoderClient->fetchResourceContests();
            $resourceProblems = $this->atCoderClient->fetchResourceMergedProblems();
            $resourcePairs = $this->atCoderClient->fetchResourceContestProblemPairs();

            if (! empty($resourceContests)) {
                $contests = collect($resourceContests)
                    ->map(function ($contest) {
                        $contestId = (string) ($contest['id'] ?? '');
                        if ($contestId === '') {
                            return null;
                        }

                        $startEpoch = isset($contest['start_epoch_second']) ? (int) $contest['start_epoch_second'] : null;
                        $durationSecond = isset($contest['duration_second']) ? (int) $contest['duration_second'] : null;
                        $startTime = $startEpoch ? now()->setTimestamp($startEpoch) : null;
                        $endTime = ($startTime && $durationSecond) ? (clone $startTime)->addSeconds($durationSecond) : null;

                        return [
                            'contest_id' => (string) $contestId,
                            'name' => (string) ($contest['title'] ?? $contestId),
                            'start_time' => $startTime?->toDateTimeString(),
                            'end_time' => $endTime?->toDateTimeString(),
                            'duration_seconds' => $durationSecond,
                            'type' => 'contest',
                            'url' => 'https://atcoder.jp/contests/' . $contestId,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();
            }
        }

        if (empty($contests)) {
            $contests = $this->atCoderClient->fetchContestArchive($archivePages);
        }
        $contestRows = [];

        foreach ($contests as $contest) {
            $contestId = (string) ($contest['contest_id'] ?? '');
            if ($contestId === '') {
                continue;
            }

            $name = trim((string) ($contest['name'] ?? 'AtCoder Contest'));

            $contestRows[] = [
                'platform_contest_id' => $contestId,
                'slug' => $contestId,
                'name' => $name,
                'description' => null,
                'type' => $this->normalizeContestType((string) ($contest['type'] ?? 'contest')),
                'phase' => 'finished',
                'duration_seconds' => $contest['duration_seconds'] ?? null,
                'start_time' => $contest['start_time'] ?? null,
                'end_time' => $contest['end_time'] ?? null,
                'url' => (string) ($contest['url'] ?? ('https://atcoder.jp/contests/' . $contestId)),
                'participant_count' => null,
                'is_rated' => true,
                'tags' => ['atcoder', (string) ($contest['type'] ?? 'contest')],
                'raw' => $contest,
                'status' => 'Active',
            ];
        }

        $this->contestRepository->upsertMany($platform->id, $contestRows);

        $contestMap = $platform->contests()->pluck('id', 'platform_contest_id')->toArray();

        $problemRowsById = [];
        if ($resourceModeEnabled && ! empty($resourcePairs)) {
            $problemToContest = [];
            foreach ($resourcePairs as $pair) {
                $pid = (string) ($pair['problem_id'] ?? '');
                $cid = (string) ($pair['contest_id'] ?? '');
                if ($pid !== '' && $cid !== '' && ! isset($problemToContest[$pid])) {
                    $problemToContest[$pid] = $cid;
                }
            }

            $problemDetailsById = collect($resourceProblems)
                ->filter(fn ($row) => isset($row['id']))
                ->keyBy('id');

            $problemIds = array_keys($problemToContest);

            foreach ($problemIds as $problemId) {
                $detail = $problemDetailsById->get($problemId, []);
                $contestId = (string) ($problemToContest[$problemId] ?? '');
                $title = trim((string) ($detail['title'] ?? ''));
                $problemIndex = trim((string) ($detail['problem_index'] ?? $this->extractAtCoderProblemIndex($problemId) ?? ''));

                $problemRowsById[$problemId] = [
                    'contest_id' => ($contestId !== '' && isset($contestMap[$contestId])) ? $contestMap[$contestId] : null,
                    'platform_problem_id' => $problemId,
                    'slug' => $problemId,
                    'name' => $title !== '' ? $title : ('Task ' . strtoupper($problemIndex !== '' ? $problemIndex : $problemId)),
                    'code' => $problemIndex !== '' ? $problemIndex : null,
                    'description' => null,
                    'difficulty' => null,
                    'rating' => isset($detail['difficulty']) && is_numeric($detail['difficulty']) ? (int) round((float) $detail['difficulty']) : null,
                    'points' => null,
                    'accuracy' => null,
                    'acceptance_rate' => null,
                    'time_limit_ms' => null,
                    'memory_limit_mb' => null,
                    'total_submissions' => 0,
                    'accepted_submissions' => 0,
                    'solved_count' => 0,
                    'tags' => ['atcoder'],
                    'topics' => ['atcoder'],
                    'url' => 'https://atcoder.jp/contests/' . ($contestId !== '' ? $contestId : 'archive') . '/tasks/' . $problemId,
                    'editorial_url' => $contestId !== '' ? ('https://atcoder.jp/contests/' . $contestId . '/editorial') : null,
                    'raw' => [
                        'problem' => $detail,
                        'contest_id' => $contestId,
                    ],
                    'status' => 'Active',
                    'is_premium' => false,
                ];
            }

            Problem::where('platform_id', $platform->id)
                ->whereNotIn('platform_problem_id', $problemIds)
                ->delete();
        }

        if (empty($problemRowsById)) {
            $contestModels = $platform->contests()
                ->orderByDesc('start_time')
                ->take($maxContestsForProblems)
                ->get(['id', 'platform_contest_id', 'name'])
                ->values();

            foreach ($contestModels as $contestModel) {
                $contestId = (string) $contestModel->platform_contest_id;
                $tasks = $this->atCoderClient->fetchContestTasks($contestId);

                foreach ($tasks as $task) {
                    $taskId = (string) ($task['task_id'] ?? '');
                    if ($taskId === '') {
                        continue;
                    }

                    $name = trim((string) ($task['name'] ?? ''));
                    $code = trim((string) ($task['code'] ?? ''));
                    $platformProblemId = $taskId;

                    $problemRowsById[$platformProblemId] = [
                        'contest_id' => $contestModel->id,
                        'platform_problem_id' => $platformProblemId,
                        'slug' => $taskId,
                        'name' => $name !== '' ? $name : ('Task ' . $taskId),
                        'code' => $code !== '' ? $code : null,
                        'description' => null,
                        'difficulty' => null,
                        'rating' => null,
                        'points' => null,
                        'accuracy' => null,
                        'acceptance_rate' => null,
                        'time_limit_ms' => null,
                        'memory_limit_mb' => null,
                        'total_submissions' => 0,
                        'accepted_submissions' => 0,
                        'solved_count' => 0,
                        'tags' => ['atcoder'],
                        'topics' => ['atcoder'],
                        'url' => (string) ($task['url'] ?? ('https://atcoder.jp/contests/' . $contestId . '/tasks/' . $taskId)),
                        'editorial_url' => (string) ($task['editorial_url'] ?? ('https://atcoder.jp/contests/' . $contestId . '/editorial')),
                        'raw' => array_merge($task, ['contest_id' => $contestId]),
                        'status' => 'Active',
                        'is_premium' => false,
                    ];
                }

                if ($taskDelayMs > 0) {
                    usleep($taskDelayMs * 1000);
                }
            }
        }

        $problemRows = array_values($problemRowsById);

        $this->problemRepository->upsertMany($platform->id, $problemRows);

        Log::info('AtCoder catalog synced', [
            'platform_id' => $platform->id,
            'contests' => count($contestRows),
            'problems' => count($problemRows),
            'archive_pages' => $archivePages,
            'max_contests_for_problems' => $maxContestsForProblems,
            'resource_mode' => $resourceModeEnabled,
        ]);

        return [
            'contests_synced' => count($contestRows),
            'problems_synced' => count($problemRows),
            'skipped' => false,
        ];
    }

    private function normalizeContestType(string $type): string
    {
        $normalized = strtolower(trim($type));

        return match ($normalized) {
            'algorithm' => 'contest',
            'heuristic' => 'challenge',
            'virtual' => 'virtual',
            'rated' => 'rated',
            'unrated' => 'unrated',
            'practice' => 'practice',
            'challenge' => 'challenge',
            default => 'contest',
        };
    }

    private function extractAtCoderProblemIndex(string $problemId): ?string
    {
        if (preg_match('/_([a-z0-9]+)$/i', $problemId, $matches)) {
            return strtoupper($matches[1]);
        }

        return null;
    }
}
