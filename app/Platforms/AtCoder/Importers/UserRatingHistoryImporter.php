<?php

namespace App\Platforms\AtCoder\Importers;

use App\Core\Contracts\Importers\UserRatingHistoryImporter as UserRatingHistoryImporterContract;
use App\Core\DTOs\RatingChangeDTO;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Throwable;

class UserRatingHistoryImporter implements UserRatingHistoryImporterContract
{
    public function __construct(
        private readonly Contest $contestModel,
        private readonly ContestRatingChange $contestRatingChangeModel,
        private readonly Platform $platformModel,
        private readonly PlatformProfile $platformProfileModel,
        private readonly AtCoderAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {}

    public function import(?string $handle = null): ImportResult
    {
        $result = new ImportResult();

        $platform = $this->platformModel->newQuery()
            ->where('slug', 'atcoder')
            ->first();

        if ($platform === null) {
            app(ApplicationLogger::class)->error(
                'User rating history import failed: platform not found',
                [
                    'category' => 'import',
                    'platform' => 'atcoder',
                    'source' => self::class,
                    'message' => 'Platform "atcoder" not found in database',
                ]
            );

            return $result;
        }

        $query = $this->platformProfileModel
            ->newQuery()
            ->where('platform_id', $platform->id)
            ->active();

        if ($handle !== null) {
            $query->whereRaw(
                'LOWER(handle) = ?',
                [mb_strtolower(trim($handle))]
            );
        }

        $profiles = $query->get();

        $result->incrementChecked($profiles->count());

        foreach ($profiles as $profile) {
            $normalizedHandle = mb_strtolower(
                trim((string) $profile->handle)
            );

            if ($normalizedHandle === '') {
                $result->incrementSkipped();
                continue;
            }

            $syncState = $this->platformSyncStateService->markSyncing(
                $platform,
                PlatformSyncEntityType::UserRatingHistory,
                $normalizedHandle,
                [
                    'profile_id' => $profile->id,
                    'handle' => $profile->handle,
                    'platform_slug' => 'atcoder',
                ]
            );

            if ($syncState === null) {
                $result->incrementSkipped();
                continue;
            }

            try {
                $ratingChanges = $this->adapter->getUserRatingHistory($normalizedHandle);

                if (!is_array($ratingChanges)) {
                    $ratingChanges = [];
                }

                $result->incrementFetched(count($ratingChanges));

                $platformProfilesByHandle = $this->platformProfilesByHandle((int) $platform->id);

                foreach ($ratingChanges as $ratingChange) {
                    if (!($ratingChange instanceof RatingChangeDTO)) {
                        continue;
                    }

                    $itemHandle = trim($ratingChange->handle);
                    if ($itemHandle === '') {
                        $itemHandle = $normalizedHandle;
                    }

                    $contest = $this->contestModel->newQuery()
                        ->where('platform_id', $platform->id)
                        ->where('platform_contest_id', $ratingChange->contestPlatformId)
                        ->first();

                    if ($contest === null) {
                        app(ApplicationLogger::class)->warning('Skipping rating change: contest not found in DB', [
                            'category' => 'import',
                            'platform' => 'atcoder',
                            'source' => self::class,
                            'contest_platform_id' => $ratingChange->contestPlatformId,
                            'handle' => $itemHandle,
                        ]);

                        continue;
                    }

                    $platformProfile = $platformProfilesByHandle[mb_strtolower($itemHandle)] ?? $profile;

                    $contestRatingChange = $this->contestRatingChangeModel->newQuery()->updateOrCreate(
                        [
                            'contest_id' => $contest->id,
                            'handle' => $itemHandle,
                        ],
                        [
                            'platform_id' => $platform->id,
                            'platform_profile_id' => $platformProfile?->id,
                            'is_rated' => $ratingChange->isRated,
                            'rank' => $ratingChange->rank,
                            'old_rating' => $ratingChange->oldRating,
                            'new_rating' => $ratingChange->newRating,
                            'rating_change' => $ratingChange->ratingChange,
                            'performance' => $ratingChange->performance,
                            'last_synced_at' => now(),
                            'metadata' => array_merge(
                                [
                                    'source' => 'rating-change-import',
                                    'platform' => 'atcoder',
                                    'contest_platform_id' => $contest->platform_contest_id,
                                    'contest_name' => $contest->name,
                                    'handle' => $itemHandle,
                                    'synced_at' => now(),
                                ],
                                $ratingChange->metadata
                            ),
                            'raw' => $ratingChange->raw,
                            'status' => 'Active',
                        ]
                    );

                    if ($contestRatingChange->wasRecentlyCreated) {
                        $result->incrementCreated();
                    } else {
                        $result->incrementUpdated();
                    }
                }

                $this->platformSyncStateService->markSynced($syncState, [
                    'profile_id' => $profile->id,
                    'handle' => $profile->handle,
                    'platform_slug' => 'atcoder',
                    'rating_changes_fetched' => count($ratingChanges),
                ]);
            } catch (Throwable $e) {
                $result->incrementFailed();

                $this->platformSyncStateService->markFailed($syncState, $e, [
                    'profile_id' => $profile->id,
                    'handle' => $profile->handle,
                    'platform_slug' => 'atcoder',
                ]);

                app(ApplicationLogger::class)->error('Rating change sync failed', [
                    'category' => 'import',
                    'platform' => 'atcoder',
                    'source' => self::class,
                    'handle' => $profile->handle,
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], $e);
            }
        }

        $result->metadata = array_merge(
            $result->metadata,
            [
                'platform' => 'atcoder',
                'entity' => 'user_rating_history',
            ]
        );

        return $result;
    }

    private function platformProfilesByHandle(int $platformId): array
    {
        $profiles = $this->platformProfileModel->newQuery()
            ->where('platform_id', $platformId)
            ->get();

        $indexedProfiles = [];

        foreach ($profiles as $profile) {
            $handle = mb_strtolower(trim((string) $profile->handle));

            if ($handle === '') {
                continue;
            }

            $indexedProfiles[$handle] = $profile;
        }

        return $indexedProfiles;
    }
}
