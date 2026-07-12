<?php

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
                $syncState = $this->platformSyncStateService->markSyncing(
                    $contest->platform,
                    PlatformSyncEntityType::ContestProblems,
                    (string) $contest->platform_contest_id,
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
                    $problems = $this->adapter->getContestProblems((string) $contest->platform_contest_id);

                    if (! is_array($problems)) {
                        $problems = [];
                    }

                    $result->incrementFetched(count($problems));

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
                                'code' => $problemDto->code,
                                'points' => $problemDto->points,
                                'rating' => $problemDto->rating,
                                'time_limit_ms' => $problemDto->timeLimit,
                                'memory_limit_mb' => $problemDto->memoryLimit,
                                'tags' => $problemDto->tags,
                                'url' => $problemDto->url,
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
                            $result->incrementCreated();
                            continue;
                        }

                        $result->incrementUpdated();
                    }

                    $this->platformSyncStateService->markSynced($syncState, [
                        'problem_count' => count($problems),
                    ]);
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
