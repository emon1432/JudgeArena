<?php

namespace App\Platforms\Codeforces\Importers;

use App\Core\Contracts\Importers\ContestImporter as ContestImporterContract;
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
                'Contest import failed: platform not found',
                [
                    'category' => 'import',
                    'platform' => 'codeforces',
                    'source' => self::class,
                    'message' => 'Platform "codeforces" not found in database',
                ]
            );

            return $stats;
        }

        $contests = $this->adapter->getContests();

        if (! is_array($contests)) {
            $contests = [];
        }

        $pendingContests = collect($contests)->filter(function ($contestDto) use ($platform): bool {
            $syncState = $this->platformSyncStateService->findState(
                $platform,
                PlatformSyncEntityType::Contest,
                (string) $contestDto->platformContestId
            );

            return $this->platformSyncStateService->canBeRetried($syncState);
        });

        $stats = [
            'contests_checked' => count($contests),
            'contests_synced' => 0,
            'contests_already_synced' => collect($contests)->filter(function ($contestDto) use ($platform): bool {
                return $this->platformSyncStateService->isSynced(
                    $platform,
                    PlatformSyncEntityType::Contest,
                    (string) $contestDto->platformContestId
                );
            })->count(),
            'contests_failed' => 0,
            'fetched' => count($contests),
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
        ];

        foreach ($pendingContests as $contestDto) {
            $syncState = $this->platformSyncStateService->markSyncing(
                $platform,
                PlatformSyncEntityType::Contest,
                (string) $contestDto->platformContestId,
                [
                    'contest_platform_id' => $contestDto->platformContestId,
                    'contest_title' => $contestDto->title,
                    'platform_slug' => $platform->slug,
                ]
            );

            if ($syncState === null) {
                continue;
            }

            try {
                $contest = $this->contestModel->newQuery()->updateOrCreate(
                    [
                        'platform_id' => $platform->id,
                        'platform_contest_id' => $contestDto->platformContestId,
                    ],
                    [
                        'name' => $contestDto->title,
                        'phase' => $contestDto->phase,
                        'duration_seconds' => $contestDto->durationSeconds,
                        'start_time' => $contestDto->startedAt,
                        'end_time' => $contestDto->endedAt,
                        'metadata' => [
                            'source' => 'adapter',
                            'imported_at' => now(),
                        ],
                        'raw' => $contestDto->raw,
                    ],
                );

                if ($contest->wasRecentlyCreated) {
                    $stats['created']++;
                } else {
                    $stats['updated']++;
                }

                $this->platformSyncStateService->markSynced($syncState, [
                    'contest_id' => $contest->id,
                    'contest_platform_id' => $contestDto->platformContestId,
                ]);

                $stats['contests_synced']++;
            } catch (Throwable $e) {
                $stats['failed']++;
                $stats['contests_failed']++;

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

        return $stats;
    }
}
