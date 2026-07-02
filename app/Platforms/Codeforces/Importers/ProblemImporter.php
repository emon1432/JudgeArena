<?php

namespace App\Platforms\Codeforces\Importers;

use App\Core\Contracts\Importers\ProblemImporter as ProblemImporterContract;
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
        private readonly Problem $problemModel,
        private readonly Contest $contestModel,
        private readonly Platform $platformModel,
        private readonly CodeforcesAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {}

    public function import(): array
    {
        $stats = [
            'fetched' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
        ];

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

            return $stats;
        }

        $problems = $this->adapter->getProblems();

        if (! is_array($problems)) {
            $problems = [];
        }

        $pendingProblems = collect($problems)->filter(function ($problemDto) use ($platform): bool {
            $syncState = $this->platformSyncStateService->findState(
                $platform,
                PlatformSyncEntityType::ContestProblems,
                (string) $problemDto->platformProblemId
            );

            return $this->platformSyncStateService->canBeRetried($syncState);
        });

        $stats = [
            'problems_checked' => count($problems),
            'problems_synced' => 0,
            'problems_already_synced' => collect($problems)->filter(function ($problemDto) use ($platform): bool {
                return $this->platformSyncStateService->isSynced(
                    $platform,
                    PlatformSyncEntityType::ContestProblems,
                    (string) $problemDto->platformProblemId
                );
            })->count(),
            'problems_failed' => 0,
            'fetched' => count($problems),
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
        ];

        foreach ($pendingProblems as $problemDto) {
            $syncState = $this->platformSyncStateService->markSyncing(
                $platform,
                PlatformSyncEntityType::ContestProblems,
                (string) $problemDto->platformProblemId,
                [
                    'platform_slug' => $platform->slug,
                    'problem_title' => $problemDto->title,
                    'contest_platform_id' => $problemDto->contestPlatformId,
                ]
            );

            if ($syncState === null) {
                continue;
            }

            try {
                $problem = $this->problemModel->newQuery()->updateOrCreate(
                    [
                        'platform_id' => $platform->id,
                        'platform_problem_id' => $problemDto->platformProblemId,
                    ],
                    [
                        'contest_id' => $problemDto->contestPlatformId === null || $problemDto->contestPlatformId === ''
                            ? null
                            : $this->contestModel->newQuery()
                                ->where('platform_id', $platform->id)
                                ->where('platform_contest_id', $problemDto->contestPlatformId)
                                ->first()?->id,
                        'slug' => slugify($problemDto->title),
                        'name' => $problemDto->title,
                        'code' => $problemDto->code,
                        'points' => $problemDto->points,
                        'rating' => $problemDto->rating,
                        'solved_count' => $problemDto->solvedCount,
                        'tags' => $problemDto->tags,
                        'url' => $problemDto->url,
                        'last_synced_at' => now(),
                        'metadata' => [
                            'source' => 'contest-scoped-sync',
                            'platform' => $problemDto->platform,
                            'contest_platform_id' => $problemDto->contestPlatformId,
                        ],
                        'raw' => $problemDto->raw,
                        'status' => 'Active',
                    ]
                );

                if ($problem->wasRecentlyCreated) {
                    $stats['created']++;
                } else {
                    $stats['updated']++;
                }

                $this->platformSyncStateService->markSynced($syncState, [
                    'problem_id' => $problem->id ?? null,
                    'problem_platform_id' => $problemDto->platformProblemId,
                    'contest_platform_id' => $problemDto->contestPlatformId,
                ]);

                $stats['problems_synced']++;
            } catch (Throwable $e) {
                $stats['failed']++;

                $stats['problems_failed']++;

                $this->platformSyncStateService->markFailed($syncState, $e, [
                    'problem_platform_id' => $problemDto->platformProblemId,
                    'contest_platform_id' => $problemDto->contestPlatformId,
                ]);

                app(ApplicationLogger::class)->error(
                    'Problem import failed',
                    [
                        'category' => 'import',
                        'platform' => 'codeforces',
                        'source' => self::class,
                        'message' => $e->getMessage(),
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                    $e
                );
            }
        }

        return $stats;
    }
}
