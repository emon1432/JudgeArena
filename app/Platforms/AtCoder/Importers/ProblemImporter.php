<?php

declare(strict_types=1);

namespace App\Platforms\AtCoder\Importers;

use App\Core\Contracts\Importers\ProblemImporter as ProblemImporterContract;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Enums\PlatformSyncStatus;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\Problem;
use App\Models\Submission;
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
        private readonly Submission $submissionModel,
    ) {}

    public function import(?int $limit = null, ?callable $onProgress = null): ImportResult
    {
        $result = new ImportResult;

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

        $query = $this->contestModel->newQuery()
            ->where('platform_id', $platform->id)
            ->whereNotNull('platform_contest_id')
            ->where(function ($q) {
                $q->whereNull('phase')
                    ->orWhere('phase', '!=', 'BEFORE');
            })
            ->where(function ($q) use ($platform) {
                $q->where('phase', 'CODING')
                    ->orWhere(function ($subQ) use ($platform) {
                        $subQ->whereNotIn('platform_contest_id', function ($sub) use ($platform) {
                            $sub->select('entity_platform_id')
                                ->from('platform_sync_states')
                                ->where('platform_id', $platform->id)
                                ->where('entity_type', PlatformSyncEntityType::ContestProblems->value)
                                ->where('sync_status', PlatformSyncStatus::Synced->value);
                        });
                    });
            })
            ->with('platform')
            ->orderBy('id', 'asc');

        $effectiveLimit = $limit === -1 ? null : ($limit ?? 30);
        if ($effectiveLimit !== null && $effectiveLimit > 0) {
            $query->limit($effectiveLimit);
        }

        $contests = $query->get();
        $totalContests = $contests->count();
        $result->incrementChecked($totalContests);

        if ($onProgress !== null) {
            $onProgress($totalContests, 0, 'Starting AtCoder problems sync...');
        }

        $contestsByPlatform = $contests->groupBy(function (Contest $contest): string {
            return (string) ($contest->platform?->slug ?? '');
        });

        $processedCount = 0;

        foreach ($contestsByPlatform as $platformSlugKey => $platformContests) {
            foreach ($platformContests as $contest) {
                $processedCount++;
                $contestPlatformId = (string) ($contest->platform_contest_id ?? '');
                $contestTitle = (string) ($contest->name ?? "Contest #{$contestPlatformId}");

                if ($onProgress !== null) {
                    $onProgress($totalContests, $processedCount, Str::limit($contestTitle, 45));
                }

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
                    // Proactively fetch & cache complete standings to Google Drive via StandingsCacheService
                    try {
                        $this->adapter->getUserStandings($contestPlatformId);
                    } catch (Throwable $e) {
                        app(ApplicationLogger::class)->warning('AtCoder standings caching skipped during problem import', [
                            'category' => 'import',
                            'platform' => 'atcoder',
                            'contest_platform_id' => $contestPlatformId,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    $problems = $this->adapter->getContestProblems($contestPlatformId);

                    if (! is_array($problems)) {
                        $problems = [];
                    }

                    // Fallback to cached standings problems if Kenkoooo returns empty
                    if (empty($problems)) {
                        try {
                            $standingsDto = $this->adapter->getUserStandings($contestPlatformId);
                            $problems = $standingsDto->problems ?? [];
                        } catch (Throwable) {
                            $problems = [];
                        }
                    }

                    $result->incrementFetched(count($problems));

                    foreach ($problems as $problemDto) {
                        $problemPlatformId = (string) ($problemDto->platformProblemId ?? '');
                        $code = (string) ($problemDto->code ?? '');
                        $title = (string) ($problemDto->title ?? '');

                        $slug = Str::slug($contestPlatformId.'-'.strtolower($code).'-'.$title);
                        if ($slug === '' || $slug === '-') {
                            $slug = Str::slug($problemPlatformId.'-'.$title);
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

                        // Self-healing backlink: link any orphaned submissions with null problem_id
                        $this->submissionModel->newQuery()
                            ->where('platform_id', $contest->platform_id)
                            ->whereNull('problem_id')
                            ->where(function ($q) use ($contest) {
                                $q->where('contest_id', $contest->id)
                                    ->orWhere('metadata->contest_platform_id', $contest->platform_contest_id);
                            })
                            ->where(function ($q) use ($problemPlatformId, $code) {
                                $q->where('metadata->problem_platform_id', $problemPlatformId)
                                    ->orWhere('metadata->problem_platform_id', strtolower($problemPlatformId))
                                    ->orWhere('metadata->problem_platform_id', str_replace('-', '_', $problemPlatformId))
                                    ->orWhere('metadata->problem_platform_id', str_replace('_', '-', $problemPlatformId));
                                if ($code !== '') {
                                    $q->orWhere('metadata->problem_platform_id', $code);
                                }
                            })
                            ->update([
                                'contest_id' => $contest->id,
                                'problem_id' => $problem->id,
                            ]);

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
