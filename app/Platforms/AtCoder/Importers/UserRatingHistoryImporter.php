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

    private object $contest;

    public function __construct(
        private readonly Contest $contestModel,
        private readonly ContestRatingChange $contestRatingChangeModel,
        private readonly Platform $platformModel,
        private readonly PlatformProfile $platformProfileModel,
        private readonly AtCoderAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {
        $this->contest = new Contest();
    }

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
                PlatformSyncEntityType::User,
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

                if (! is_array($ratingChanges)) {
                    $ratingChanges = [];
                }

                $result->incrementFetched(count($ratingChanges));

                $platformProfilesByHandle = $this->platformProfilesByHandle((int) $platform->id);

                foreach ($ratingChanges as $ratingChange) {

                    if (! ($ratingChange instanceof RatingChangeDTO)) {
                        continue;
                    }

                    $handle = trim($ratingChange->handle);

                    $this->contest = $this->contestModel->newQuery()->where('platform_contest_id', $ratingChange->contestPlatformId)->first();

                    if ($handle === '') {
                        app(ApplicationLogger::class)->warning('Skipping rating change with missing handle', [
                            'category' => 'import',
                            'platform' => 'atcoder',
                            'source' => self::class,
                            'contest_id' => $this->contest->id,
                            'platform_contest_id' => $this->contest->platform_contest_id,
                            'contest_name' => $this->contest->name,
                            'raw' => $ratingChange->raw,
                        ]);

                        continue;
                    }

                    $platformProfile = $platformProfilesByHandle[mb_strtolower($handle)] ?? null;

                    $contestRatingChange = $this->contestRatingChangeModel->newQuery()->updateOrCreate(
                        [
                            'contest_id' => $this->contest->id,
                            'handle' => $handle,
                        ],
                        [
                            'platform_id' => $this->contest->platform_id,
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
                                    'contest_platform_id' => $this->contest->platform_contest_id,
                                    'contest_name' => $this->contest->name,
                                    'handle' => $handle,
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
                        continue;
                    }

                    $result->incrementUpdated();
                }

                $this->platformSyncStateService->markSynced($syncState, [
                    'contest_id' => $this->contest->id,
                    'contest_name' => $this->contest->name,
                    'platform_slug' => 'atcoder',
                    'platform_contest_id' => $this->contest->platform_contest_id,
                    'rating_changes_fetched' => count($ratingChanges),
                ]);
            } catch (Throwable $e) {
                $result->incrementFailed();

                $this->platformSyncStateService->markFailed($syncState, $e, [
                    'contest_id' => $this->contest->id,
                    'contest_name' => $this->contest->name,
                    'platform_slug' => 'atcoder',
                    'platform_contest_id' => $this->contest->platform_contest_id,
                ]);

                app(ApplicationLogger::class)->error('Rating change sync failed', [
                    'category' => 'import',
                    'platform' => 'atcoder',
                    'source' => self::class,
                    'contest_id' => $this->contest->id,
                    'platform_contest_id' => $this->contest->platform_contest_id,
                    'contest_name' => $this->contest->name,
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
