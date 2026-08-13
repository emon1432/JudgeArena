<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Importers;

use App\Core\Contracts\Importers\UserRatingHistoryImporter as UserRatingHistoryImporterContract;
use App\Core\DTOs\RatingChangeDTO;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Platforms\Codeforces\CodeforcesAdapter;
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
        private readonly CodeforcesAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {}

    public function import(?string $handle = null): ImportResult
    {
        $result = new ImportResult();

        $platform = $this->platformModel->newQuery()
            ->where('slug', 'codeforces')
            ->first();

        if ($platform === null) {
            app(ApplicationLogger::class)->error(
                'User rating history import failed: platform not found',
                [
                    'category' => 'import',
                    'platform' => 'codeforces',
                    'source' => self::class,
                    'message' => 'Platform "codeforces" not found in database',
                ]
            );

            return $result;
        }

        $query = $this->platformProfileModel
            ->newQuery()
            ->where('platform_id', $platform->id)
            ->active();

        if ($handle !== null && trim($handle) !== '') {
            $query->whereRaw(
                'LOWER(handle) = ?',
                [mb_strtolower(trim($handle))]
            );
        }

        $profiles = $query->get();
        $result->incrementChecked($profiles->count());

        // Pre-index all contests by platform_contest_id to eliminate N+1 SQL queries
        $contestsByPlatformId = $this->contestModel->newQuery()
            ->where('platform_id', $platform->id)
            ->whereNotNull('platform_contest_id')
            ->get()
            ->keyBy(fn(Contest $c): string => (string) $c->platform_contest_id);

        $platformProfilesByHandle = $this->platformProfilesByHandle((int) $platform->id);

        foreach ($profiles as $profile) {
            $normalizedHandle = mb_strtolower(trim((string) $profile->handle));

            if ($normalizedHandle === '') {
                $result->incrementSkipped();
                continue;
            }

            $isSynced = $this->platformSyncStateService->isSynced(
                $platform,
                PlatformSyncEntityType::UserRatingHistory,
                $normalizedHandle
            );

            // If handle is not explicitly specified and profile rating history was already synced, skip!
            if ($handle === null && $isSynced) {
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
                    'platform_slug' => 'codeforces',
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

                foreach ($ratingChanges as $ratingChange) {
                    if (! ($ratingChange instanceof RatingChangeDTO)) {
                        continue;
                    }

                    $ratingChangeHandle = trim((string) ($ratingChange->handle ?? ''));
                    if ($ratingChangeHandle === '') {
                        $ratingChangeHandle = $profile->handle;
                    }

                    $contestPlatformId = (string) ($ratingChange->contestPlatformId ?? '');
                    $contest = $contestsByPlatformId->get($contestPlatformId);

                    if ($contest === null) {
                        app(ApplicationLogger::class)->warning('Skipping user rating change: contest not found in DB', [
                            'category' => 'import',
                            'platform' => 'codeforces',
                            'source' => self::class,
                            'platform_contest_id' => $contestPlatformId,
                            'handle' => $ratingChangeHandle,
                        ]);
                        continue;
                    }

                    $platformProfile = $platformProfilesByHandle[mb_strtolower($ratingChangeHandle)] ?? $profile;

                    $contestRatingChange = $this->contestRatingChangeModel->newQuery()->updateOrCreate(
                        [
                            'contest_id' => $contest->id,
                            'handle' => $ratingChangeHandle,
                        ],
                        [
                            'platform_id' => $platform->id,
                            'platform_profile_id' => $platformProfile?->id,
                            'is_rated' => $ratingChange->isRated ?? true,
                            'rank' => $ratingChange->rank ?? null,
                            'old_rating' => $ratingChange->oldRating ?? null,
                            'new_rating' => $ratingChange->newRating ?? null,
                            'rating_change' => $ratingChange->ratingChange ?? null,
                            'performance' => $ratingChange->performance ?? null,
                            'last_synced_at' => now(),
                            'metadata' => array_merge(
                                [
                                    'source' => 'user-rating-history-import',
                                    'platform' => 'codeforces',
                                    'contest_platform_id' => $contestPlatformId,
                                    'contest_name' => $contest->name,
                                    'handle' => $ratingChangeHandle,
                                    'synced_at' => now(),
                                ],
                                $ratingChange->metadata ?? []
                            ),
                            'raw' => $ratingChange->raw ?? [],
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
                    'platform_slug' => 'codeforces',
                    'rating_changes_count' => count($ratingChanges),
                ]);
            } catch (Throwable $e) {
                $result->incrementFailed();

                $this->platformSyncStateService->markFailed($syncState, $e, [
                    'profile_id' => $profile->id,
                    'handle' => $profile->handle,
                    'platform_slug' => 'codeforces',
                ]);

                app(ApplicationLogger::class)->error('User rating history sync failed', [
                    'category' => 'import',
                    'platform' => 'codeforces',
                    'source' => self::class,
                    'profile_id' => $profile->id,
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
                'platform' => 'codeforces',
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

