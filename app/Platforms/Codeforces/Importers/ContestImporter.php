<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Importers;

use App\Core\Contracts\Importers\ContestImporter as ContestImporterContract;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\Platform;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Throwable;


class ContestImporter implements ContestImporterContract
{
    public function __construct(
        private readonly Contest $contestModel,
        private readonly Platform $platformModel,
        private readonly CodeforcesAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {
    }

    public function import(bool $fullSync = false): ImportResult
    {
        $result = new ImportResult();

        $platform = $this->platformModel->newQuery()
            ->where('slug', 'codeforces')
            ->first();

        if ($platform === null) {
            app(ApplicationLogger::class)->error(
                'Contest import failed: platform not found',
                [
                    'category' => 'import',
                    'platform' => 'codeforces',
                    'source' => self::class,
                    'message' => 'Platform "codeforces" not found in database',
                ]
            );

            return $result;
        }

        $contests = $this->adapter->getContests();

        if (!is_array($contests)) {
            $contests = [];
        }

        $result->incrementFetched(count($contests));
        $result->incrementChecked(count($contests));

        foreach ($contests as $contestDto) {
            $contestPlatformId = (string) ($contestDto->platformContestId ?? '');

            $existingContest = $this->contestModel->newQuery()
                ->where('platform_id', $platform->id)
                ->where('platform_contest_id', $contestPlatformId)
                ->first();

            $isSynced = $this->platformSyncStateService->isSynced(
                $platform,
                PlatformSyncEntityType::Contest,
                $contestPlatformId
            );

            // Skip only if contest exists and its phase is already 'FINISHED'
            if (
                $existingContest !== null &&
                strtoupper((string) $existingContest->phase) === 'FINISHED' &&
                $isSynced
            ) {
                $result->incrementSkipped();
                continue;
            }

            $syncState = $this->platformSyncStateService->markSyncing(
                $platform,
                PlatformSyncEntityType::Contest,
                $contestPlatformId,
                [
                    'contest_platform_id' => $contestPlatformId,
                    'contest_title' => $contestDto->title,
                    'platform_slug' => $platform->slug,
                ]
            );

            if ($syncState === null) {
                $result->incrementSkipped();
                continue;
            }

            try {
                $contest = $this->contestModel->newQuery()->updateOrCreate(
                    [
                        'platform_id' => $platform->id,
                        'platform_contest_id' => (string) ($contestDto->platformContestId ?? ''),
                    ],
                    [
                        'name' => $contestDto->title ?? '',
                        'slug' => \Illuminate\Support\Str::slug(($contestDto->title ?? 'contest') . '-' . ($contestDto->platformContestId ?? '')),
                        'type' => $contestDto->raw['type'] ?? null,
                        'phase' => $contestDto->phase ?? null,
                        'duration_seconds' => $contestDto->durationSeconds ?? null,
                        'start_time' => $contestDto->startedAt ?? null,
                        'end_time' => $contestDto->endedAt ?? null,
                        'url' => $contestDto->url ?? null,
                        'last_synced_at' => now(),
                        'metadata' => [
                            'source' => 'adapter',
                            'imported_at' => now(),
                        ],
                        'raw' => $contestDto->raw ?? [],
                    ],
                );

                if (strtoupper((string) $contest->phase) === 'FINISHED') {
                    $this->platformSyncStateService->markSynced($syncState, [
                        'contest_id' => $contest->id,
                        'contest_platform_id' => $contestDto->platformContestId,
                        'phase' => $contest->phase,
                    ]);
                } else {
                    $this->platformSyncStateService->resetForRetry($syncState, [
                        'contest_id' => $contest->id,
                        'contest_platform_id' => $contestDto->platformContestId,
                        'phase' => $contest->phase,
                    ]);
                }

                if ($contest->wasRecentlyCreated) {
                    $result->incrementCreated();
                } else {
                    $result->incrementUpdated();
                }
            } catch (Throwable $e) {
                $result->incrementFailed();

                $this->platformSyncStateService->markFailed($syncState, $e, [
                    'contest_platform_id' => $contestDto->platformContestId,
                    'contest_title' => $contestDto->title,
                ]);

                app(ApplicationLogger::class)->error(
                    'Contest import failed',
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

        $result->metadata = array_merge(
            $result->metadata,
            [
                'platform' => 'codeforces',
                'entity' => 'contest',
            ]
        );

        return $result;
    }
}

