<?php

namespace App\Platforms\AtCoder\Importers;

use App\Core\Contracts\Importers\ContestImporter as ContestImporterContract;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\Platform;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Throwable;


class ContestImporter implements ContestImporterContract
{
    public function __construct(
        private readonly Contest $contestModel,
        private readonly Platform $platformModel,
        private readonly AtCoderAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {
    }

    public function import(bool $fullSync = false): ImportResult
    {
        $result = new ImportResult();

        $platform = $this->platformModel->newQuery()
            ->where('slug', 'atcoder')
            ->first();

        if ($platform === null) {
            app(ApplicationLogger::class)->error(
                'Contest import failed: platform not found',
                [
                    'category' => 'import',
                    'platform' => 'atcoder',
                    'source' => self::class,
                    'message' => 'Platform "atcoder" not found in database',
                ]
            );

            return $result;
        }

        $pageProcessor = function (array $pageRawContests) use ($platform, $fullSync): bool {
            $pageDtos = \App\Platforms\AtCoder\Mappers\AtCoderContestMapper::fromNormalizedList($pageRawContests);
            $pageContestDtos = (new \App\Platforms\AtCoder\Transformers\ContestTransformer())->fromApiContests($pageDtos);

            $allSyncedInDb = true;

            foreach ($pageContestDtos as $contestDto) {
                $existing = $this->contestModel->newQuery()
                    ->where('platform_id', $platform->id)
                    ->where('platform_contest_id', $contestDto->platformContestId)
                    ->first();

                if ($existing === null || strtoupper((string) $existing->phase) !== 'FINISHED') {
                    $allSyncedInDb = false;

                    break;
                }
            }

            if ($allSyncedInDb && !$fullSync && count($pageContestDtos) > 0) {
                return false;
            }

            return true;
        };

        $contests = $this->adapter->getContests($pageProcessor, $fullSync);

        if (!is_array($contests)) {
            $contests = [];
        }

        $result->incrementFetched(count($contests));
        $result->incrementChecked(count($contests));

        foreach ($contests as $contestDto) {
            if (
                $this->platformSyncStateService->isSynced(
                    $platform,
                    PlatformSyncEntityType::Contest,
                    (string) $contestDto->platformContestId
                )
            ) {
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
                        'slug' => $contestDto->slug ?? \Illuminate\Support\Str::slug($contestDto->platformContestId . '-' . $contestDto->title),
                        'type' => $contestDto->type,
                        'phase' => $contestDto->phase,
                        'duration_seconds' => $contestDto->durationSeconds,
                        'start_time' => $contestDto->startedAt,
                        'end_time' => $contestDto->endedAt,
                        'url' => $contestDto->url,
                        'metadata' => [
                            'source' => 'adapter',
                            'imported_at' => now(),
                        ],
                        'raw' => $contestDto->raw,
                    ],
                );

                if ($contestDto->phase === 'FINISHED' || $contestDto->type === 'permanent') {
                    $this->platformSyncStateService->markSynced($syncState, [
                        'contest_id' => $contest->id,
                        'contest_platform_id' => $contestDto->platformContestId,
                    ]);
                } else {
                    $this->platformSyncStateService->resetForRetry($syncState, [
                        'contest_id' => $contest->id,
                        'contest_platform_id' => $contestDto->platformContestId,
                        'phase' => $contestDto->phase,
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
                        'platform' => 'atcoder',
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
                'platform' => 'atcoder',
                'entity' => 'contest',
            ]
        );

        return $result;
    }
}
