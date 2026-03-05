<?php

namespace App\Services\PlatformSync;

use App\Models\Platform;
use App\Platforms\Codeforces\CodeforcesClient;
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
}
