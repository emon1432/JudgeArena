<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Importers;

use App\Core\Contracts\Importers\ProblemImporter as ProblemImporterContract;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\Problem;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Throwable;

class ProblemImporter implements ProblemImporterContract
{
    public function __construct(
        private readonly Contest $contestModel,
        private readonly Problem $problemModel,
        private readonly Platform $platformModel,
        private readonly CodeforcesAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {}

    public function import(): ImportResult
    {
        $result = new ImportResult();

        $platform = $this->platformModel->newQuery()
            ->where('slug', 'codeforces')
            ->first();

        if ($platform === null) {
            app(ApplicationLogger::class)->error(
                'Problem import failed: platform not found',
                [
                    'category' => 'import',
                    'platform' => 'codeforces',
                    'source' => self::class,
                    'message' => 'Platform "codeforces" not found in database',
                ]
            );

            return $result;
        }

        $contests = $this->contestModel->newQuery()
            ->where('platform_id', $platform->id)
            ->whereNotNull('platform_contest_id')
            ->with('platform')
            ->get();

        $result->incrementChecked($contests->count());

        $contestsByPlatform = $contests->groupBy(function (Contest $contest): string {
            return (string) ($contest->platform?->slug ?? '');
        });

        foreach ($contestsByPlatform as $platformSlugKey => $platformContests) {
            foreach ($platformContests as $contest) {
                $contestPlatformId = (string) ($contest->platform_contest_id ?? '');

                $isSynced = $this->platformSyncStateService->isSynced(
                    $contest->platform,
                    PlatformSyncEntityType::ContestProblems,
                    $contestPlatformId
                );

                // Skip only if contest is FINISHED and its problems are already marked Synced
                if (strtoupper((string) $contest->phase) === 'FINISHED' && $isSynced) {
                    $result->incrementSkipped();
                    continue;
                }

                $syncState = $this->platformSyncStateService->markSyncing(
                    $contest->platform,
                    PlatformSyncEntityType::ContestProblems,
                    $contestPlatformId,
                    [
                        'contest_id' => $contest->id,
                        'contest_name' => $contest->name,
                        'platform_slug' => $platformSlugKey,
                    ]
                );

                if ($syncState === null) {
                    $result->incrementSkipped();
                    continue;
                }

                try {
                    $standings = $this->adapter->getStandings($contestPlatformId);
                    $problems = $standings->problems ?? [];
                    $participantCount = count($standings->rows ?? []);

                    if ($participantCount > 0) {
                        $contest->update([
                            'participant_count' => $participantCount,
                        ]);
                    }

                    if (! is_array($problems)) {
                        $problems = [];
                    }

                    $result->incrementFetched(count($problems));

                    foreach ($problems as $problemDto) {
                        $problem = $this->problemModel->newQuery()->updateOrCreate(
                            [
                                'platform_id' => $contest->platform_id,
                                'platform_problem_id' => (string) ($problemDto->platformProblemId ?? ''),
                            ],
                            [
                                'contest_id' => $contest->id,
                                'slug' => \Illuminate\Support\Str::slug(($problemDto->title ?? 'problem') . '-' . ($problemDto->platformProblemId ?? '')),
                                'name' => $problemDto->title ?? '',
                                'code' => $problemDto->code ?? null,
                                'points' => $problemDto->points ?? null,
                                'rating' => $problemDto->rating ?? null,
                                'time_limit_ms' => $problemDto->timeLimit ?? null,
                                'memory_limit_mb' => $problemDto->memoryLimit ?? null,
                                'solved_count' => $problemDto->solvedCount ?? 0,
                                'tags' => $problemDto->tags ?? [],
                                'url' => $problemDto->url ?? null,
                                'last_synced_at' => now(),
                                'metadata' => [
                                    'source' => 'contest-scoped-sync',
                                    'platform' => $problemDto->platform ?? 'codeforces',
                                    'contest_platform_id' => $contestPlatformId,
                                ],
                                'raw' => $problemDto->raw ?? [],
                                'status' => 'Active',
                            ]
                        );

                        if ($problem->wasRecentlyCreated) {
                            $result->incrementCreated();
                            continue;
                        }

                        $result->incrementUpdated();
                    }

                    if (strtoupper((string) $contest->phase) === 'FINISHED') {
                        $this->platformSyncStateService->markSynced($syncState, [
                            'problem_count' => count($problems),
                        ]);
                    } else {
                        $this->platformSyncStateService->resetForRetry($syncState, [
                            'problem_count' => count($problems),
                        ]);
                    }
                } catch (Throwable $e) {
                    $result->incrementFailed();

                    $this->platformSyncStateService->markFailed($syncState, $e, [
                        'contest_id' => $contest->id,
                        'contest_name' => $contest->name,
                    ]);

                    app(ApplicationLogger::class)->error('Problem sync failed', [
                        'category' => 'sync',
                        'platform' => $platformSlugKey,
                        'source' => self::class,
                        'contest_id' => $contest->id,
                        'platform_contest_id' => $contest->platform_contest_id,
                        'contest_name' => $contest->name,
                        'message' => $e->getMessage(),
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ], $e);
                }
            }
        }

        $result->metadata = array_merge(
            $result->metadata,
            [
                'platform' => 'codeforces',
                'entity' => 'problem',
            ]
        );

        return $result;
    }
}

