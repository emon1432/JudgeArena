<?php

declare(strict_types=1);

namespace App\Platforms\AtCoder\Importers;

use App\Core\Contracts\Importers\ProblemImporter as ProblemImporterContract;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\Problem;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Illuminate\Support\Str;
use Throwable;

class ProblemImporter implements ProblemImporterContract
{
    public function __construct(
        private readonly Contest $contestModel,
        private readonly Problem $problemModel,
        private readonly Platform $platformModel,
        private readonly AtCoderAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {}

    public function import(): ImportResult
    {
        $result = new ImportResult();

        $platform = $this->platformModel->newQuery()
            ->where('slug', 'atcoder')
            ->first();

        if ($platform === null) {
            app(ApplicationLogger::class)->error(
                'Problem import failed: platform not found',
                [
                    'category' => 'import',
                    'platform' => 'atcoder',
                    'source' => self::class,
                    'message' => 'Platform "atcoder" not found in database',
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
                    $problems = $this->adapter->getContestProblems($contestPlatformId);

                    if (! is_array($problems)) {
                        $problems = [];
                    }

                    $result->incrementFetched(count($problems));

                    foreach ($problems as $problemDto) {
                        $problemPlatformId = (string) ($problemDto->platformProblemId ?? '');
                        $code = (string) ($problemDto->code ?? '');
                        $title = (string) ($problemDto->title ?? '');

                        $slug = Str::slug($contestPlatformId . '-' . strtolower($code) . '-' . $title);
                        if ($slug === '' || $slug === '-') {
                            $slug = Str::slug($problemPlatformId . '-' . $title);
                        }

                        $problem = $this->problemModel->newQuery()->updateOrCreate(
                            [
                                'platform_id' => $contest->platform_id,
                                'platform_problem_id' => $problemPlatformId,
                            ],
                            [
                                'contest_id' => $contest->id,
                                'slug' => $slug,
                                'name' => $title,
                                'code' => $code !== '' ? $code : null,
                                'points' => $problemDto->points,
                                'rating' => $problemDto->rating,
                                'time_limit_ms' => $problemDto->timeLimit,
                                'memory_limit_mb' => $problemDto->memoryLimit,
                                'solved_count' => $problemDto->solvedCount ?? 0,
                                'tags' => $problemDto->tags,
                                'url' => $problemDto->url,
                                'last_synced_at' => now(),
                                'metadata' => [
                                    'source' => 'kenkoooo-api',
                                    'platform' => 'atcoder',
                                    'contest_platform_id' => $contestPlatformId,
                                ],
                                'raw' => $problemDto->raw,
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
                'platform' => 'atcoder',
                'entity' => 'problem',
            ]
        );

        return $result;
    }
}
