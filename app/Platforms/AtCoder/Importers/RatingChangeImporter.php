<?php

namespace App\Platforms\AtCoder\Importers;

use App\Core\Contracts\Importers\RatingChangeImporter as RatingChangeImporterContract;
use App\Core\DTOs\RatingChangeDTO;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Throwable;

class RatingChangeImporter implements RatingChangeImporterContract
{
    public function __construct(
        private readonly Contest $contestModel,
        private readonly ContestRatingChange $contestRatingChangeModel,
        private readonly PlatformProfile $platformProfileModel,
        private readonly Platform $platformModel,
        private readonly AtCoderAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {}

    public function import(): array
    {
        $stats = [
            'contests_checked' => 0,
            'contests_synced' => 0,
            'contests_already_synced' => 0,
            'contests_failed' => 0,
            'contests_unsupported_platform' => 0,
            'rating_changes_fetched' => 0,
            'rating_changes_created' => 0,
            'rating_changes_updated' => 0,
        ];

        $platform = $this->platformModel->newQuery()
            ->where('slug', 'atcoder')
            ->first();

        if ($platform === null) {
            app(ApplicationLogger::class)->error(
                'Rating change import failed: platform not found',
                [
                    'category' => 'import',
                    'platform' => 'atcoder',
                    'source' => self::class,
                    'message' => 'Platform "atcoder" not found in database',
                ]
            );

            return $stats;
        }

        $contests = $this->contestModel->newQuery()
            ->with('platform')
            ->where('platform_id', $platform->id)
            ->whereNotNull('platform_contest_id')
            ->get();

        $pendingContests = $contests->filter(function (Contest $contest): bool {
            $platform = $contest->platform;

            if ($platform === null || $contest->platform_contest_id === null || $contest->platform_contest_id === '') {
                return false;
            }

            $syncState = $this->platformSyncStateService->findState(
                $platform,
                PlatformSyncEntityType::RatingChange,
                (string) $contest->platform_contest_id
            );

            return $this->platformSyncStateService->canBeRetried($syncState);
        });

        $stats['contests_checked'] = $contests->count();
        $stats['contests_already_synced'] = $contests->filter(function (Contest $contest): bool {
            $platform = $contest->platform;

            if ($platform === null || $contest->platform_contest_id === null || $contest->platform_contest_id === '') {
                return false;
            }

            return $this->platformSyncStateService->isSynced(
                $platform,
                PlatformSyncEntityType::RatingChange,
                (string) $contest->platform_contest_id
            );
        })->count();

        foreach ($pendingContests as $contest) {
            $syncState = $this->platformSyncStateService->markSyncing(
                $contest->platform,
                PlatformSyncEntityType::RatingChange,
                (string) $contest->platform_contest_id,
                [
                    'contest_id' => $contest->id,
                    'contest_name' => $contest->name,
                    'platform_slug' => 'atcoder',
                    'platform_contest_id' => $contest->platform_contest_id,
                ]
            );

            if ($syncState === null) {
                continue;
            }

            try {
                $ratingChanges = $this->adapter->getRatingChanges((string) $contest->platform_contest_id);

                if (! is_array($ratingChanges)) {
                    $ratingChanges = [];
                }

                $stats['rating_changes_fetched'] += count($ratingChanges);

                $platformProfilesByHandle = $this->platformProfilesByHandle((int) $contest->platform_id);

                foreach ($ratingChanges as $ratingChange) {
                    if (! ($ratingChange instanceof RatingChangeDTO)) {
                        continue;
                    }

                    $handle = trim($ratingChange->handle);

                    if ($handle === '') {
                        app(ApplicationLogger::class)->warning('Skipping rating change with missing handle', [
                            'category' => 'import',
                            'platform' => 'atcoder',
                            'source' => self::class,
                            'contest_id' => $contest->id,
                            'platform_contest_id' => $contest->platform_contest_id,
                            'contest_name' => $contest->name,
                            'raw' => $ratingChange->raw,
                        ]);

                        continue;
                    }

                    $platformProfile = $platformProfilesByHandle[mb_strtolower($handle)] ?? null;

                    $contestRatingChange = $this->contestRatingChangeModel->newQuery()->updateOrCreate(
                        [
                            'contest_id' => $contest->id,
                            'handle' => $handle,
                        ],
                        [
                            'platform_id' => $contest->platform_id,
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
                        $stats['rating_changes_created']++;

                        continue;
                    }

                    $stats['rating_changes_updated']++;
                }

                $this->platformSyncStateService->markSynced($syncState, [
                    'contest_id' => $contest->id,
                    'contest_name' => $contest->name,
                    'platform_slug' => 'atcoder',
                    'platform_contest_id' => $contest->platform_contest_id,
                    'rating_changes_fetched' => count($ratingChanges),
                ]);
                $stats['contests_synced']++;
            } catch (Throwable $e) {
                $stats['contests_failed']++;

                $this->platformSyncStateService->markFailed($syncState, $e, [
                    'contest_id' => $contest->id,
                    'contest_name' => $contest->name,
                    'platform_slug' => 'atcoder',
                    'platform_contest_id' => $contest->platform_contest_id,
                ]);

                app(ApplicationLogger::class)->error('Rating change sync failed', [
                    'category' => 'import',
                    'platform' => 'atcoder',
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

        return $stats;
    }

    /**
     * @return array<string, PlatformProfile>
     */
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
