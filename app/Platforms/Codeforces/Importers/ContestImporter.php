<?php

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
    ) {}

    public function import(): ImportResult
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

        if (! is_array($contests)) {
            $contests = [];
        }

        $result->incrementFetched(count($contests));
        $result->incrementChecked(count($contests));

        foreach ($contests as $contestDto) {
            if ($this->platformSyncStateService->isSynced(
                $platform,
                PlatformSyncEntityType::Contest,
                (string) $contestDto->platformContestId
            )) {
                $result->incrementSkipped();
                continue;
            }
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
                $result->incrementSkipped();
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

                $this->platformSyncStateService->markSynced($syncState, [
                    'contest_id' => $contest->id,
                    'contest_platform_id' => $contestDto->platformContestId,
                ]);

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
