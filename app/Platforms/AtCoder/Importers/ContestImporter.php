<?php

declare(strict_types=1);

namespace App\Platforms\AtCoder\Importers;

use App\Core\Contracts\Importers\ContestImporter as ContestImporterContract;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\Platform;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Illuminate\Support\Str;
use Throwable;

class ContestImporter implements ContestImporterContract
{
    public function __construct(
        private readonly Contest $contestModel,
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

            // Skip if contest exists, its phase is 'FINISHED', and it is already synced
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
                $rateSpec = is_array($contestDto->raw['rate_change_spec'] ?? null)
                    ? $contestDto->raw['rate_change_spec']
                    : [];

                $isRated = (bool) ($rateSpec['is_rated'] ?? ($contestDto->raw['is_rated'] ?? false));

                $contest = $this->contestModel->newQuery()->updateOrCreate(
                    [
                        'platform_id' => $platform->id,
                        'platform_contest_id' => $contestPlatformId,
                    ],
                    [
                        'name' => $contestDto->title,
                        'slug' => $contestDto->slug ?? Str::slug($contestPlatformId . '-' . $contestDto->title),
                        'type' => $contestDto->type,
                        'phase' => $contestDto->phase,
                        'is_rated' => $isRated,
                        'duration_seconds' => $contestDto->durationSeconds,
                        'start_time' => $contestDto->startedAt,
                        'end_time' => $contestDto->endedAt,
                        'url' => $contestDto->url,
                        'metadata' => [
                            'source' => 'kenkoooo-api',
                            'rate_change' => $rateSpec['raw'] ?? null,
                            'rate_change_label' => $rateSpec['label'] ?? null,
                            'min_rated_rating' => $rateSpec['min_rating'] ?? null,
                            'max_rated_rating' => $rateSpec['max_rating'] ?? null,
                            'imported_at' => now(),
                        ],
                        'raw' => $contestDto->raw,
                    ],
                );

                if ($contestDto->phase === 'FINISHED' || $contestDto->type === 'permanent') {
                    $this->platformSyncStateService->markSynced($syncState, [
                        'contest_id' => $contest->id,
                        'contest_platform_id' => $contestPlatformId,
                    ]);
                } else {
                    $this->platformSyncStateService->resetForRetry($syncState, [
                        'contest_id' => $contest->id,
                        'contest_platform_id' => $contestPlatformId,
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
                    'contest_platform_id' => $contestPlatformId,
                    'contest_title' => $contestDto->title,
                ]);

                app(ApplicationLogger::class)->error(
                    'Contest import failed',
                    [
                        'category' => 'import',
                        'platform' => 'atcoder',
                        'source' => self::class,
                        'contest_platform_id' => $contestPlatformId,
                        'message' => $e->getMessage(),
                    ],
                    $e
                );
            }
        }

        return $result;
    }
}
