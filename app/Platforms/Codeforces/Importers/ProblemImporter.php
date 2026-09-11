<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Importers;

use App\Core\Contracts\Importers\ProblemImporter as ProblemImporterContract;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Enums\PlatformSyncStatus;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\Problem;
use App\Models\Submission;
use App\Platforms\Codeforces\CodeforcesAdapter;
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
        private readonly CodeforcesAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
        private readonly Submission $submissionModel,
    ) {}

    public function import(?int $limit = null, ?callable $onProgress = null): ImportResult
    {
        $result = new ImportResult;

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
            $onProgress($totalContests, 0, 'Starting Codeforces problems sync...');
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
                    $standings = $this->adapter->getUserStandings($contestPlatformId);
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
                                'slug' => Str::slug(($problemDto->title ?? 'problem').'-'.($problemDto->platformProblemId ?? '')),
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

                        // Self-healing backlink: link any orphaned submissions that had problem_id = null
                        $this->submissionModel->newQuery()
                            ->where('platform_id', $contest->platform_id)
                            ->whereNull('problem_id')
                            ->where(function ($q) use ($contest) {
                                $q->where('contest_id', $contest->id)
                                    ->orWhere('metadata->contest_platform_id', $contest->platform_contest_id);
                            })
                            ->where(function ($q) use ($problem, $problemDto) {
                                $q->where('metadata->problem_platform_id', $problem->platform_problem_id);
                                if (! empty($problemDto->code)) {
                                    $q->orWhere('metadata->problem_platform_id', $problemDto->code);
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
                    $isFinished = strtoupper((string) $contest->phase) === 'FINISHED';
                    $message = $e->getMessage();
                    $isStandingsUnavailable = str_contains($message, 'not found')
                        || str_contains($message, '400')
                        || str_contains($message, 'has not started')
                        || str_contains($message, 'Standings are not available');

                    $this->platformSyncStateService->markFailed($syncState, $e, [
                        'contest_id' => $contest->id,
                        'contest_name' => $contest->name,
                    ]);
                    if ($isFinished && $isStandingsUnavailable) {
                        $this->platformSyncStateService->markSynced($syncState, [
                            'has_public_standings' => false,
                            'note' => 'No public standings available on Codeforces',
                            'error' => $message,
                        ]);
                        $result->incrementSkipped();

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
                        app(ApplicationLogger::class)->info('Finished contest has no public standings, marked synced', [
                            'category' => 'sync',
                            'platform' => 'codeforces',
                            'contest_id' => $contest->id,
                            'platform_contest_id' => $contestPlatformId,
                            'contest_name' => $contest->name,
                            'message' => $message,
                        ]);
                    } else {
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
