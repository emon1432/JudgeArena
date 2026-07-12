<?php

namespace App\Platforms\Codeforces\Importers;

use App\Core\Contracts\Importers\RatingChangeImporter as RatingChangeImporterContract;
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

class RatingChangeImporter implements RatingChangeImporterContract
{
    public function __construct(
        private readonly Contest $contestModel,
        private readonly ContestRatingChange $contestRatingChangeModel,
        private readonly PlatformProfile $platformProfileModel,
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
                'Rating change import failed: platform not found',
                [
                    'category' => 'import',
                    'platform' => 'codeforces',
                    'source' => self::class,
                    'message' => 'Platform "codeforces" not found in database',
                ]
            );

            return $result;
        }

        $contests = $this->contestModel->newQuery()
            ->with('platform')
            ->where('platform_id', $platform->id)
            ->whereNotNull('platform_contest_id')
            ->get();

        $result->incrementChecked($contests->count());

        foreach ($contests as $contest) {
            $syncState = $this->platformSyncStateService->markSyncing(
                $contest->platform,
                PlatformSyncEntityType::RatingChange,
                (string) $contest->platform_contest_id,
                [
                    'contest_id' => $contest->id,
                    'contest_name' => $contest->name,
                    'platform_slug' => 'codeforces',
                    'platform_contest_id' => $contest->platform_contest_id,
                ]
            );

            if ($syncState === null) {
                $result->incrementSkipped();
                continue;
            }

            try {
                $ratingChanges = $this->adapter->getRatingChanges((string) $contest->platform_contest_id);

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

                    if ($handle === '') {
                        app(ApplicationLogger::class)->warning('Skipping rating change with missing handle', [
                            'category' => 'import',
                            'platform' => 'codeforces',
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
                                    'platform' => 'codeforces',
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
                        $result->incrementCreated();
                        continue;
                    }

                    $result->incrementUpdated();
                }

                $this->platformSyncStateService->markSynced($syncState, [
                    'contest_id' => $contest->id,
                    'contest_name' => $contest->name,
                    'platform_slug' => 'codeforces',
                    'platform_contest_id' => $contest->platform_contest_id,
                    'rating_changes_fetched' => count($ratingChanges),
                ]);
            } catch (Throwable $e) {
                $result->incrementFailed();

                $this->platformSyncStateService->markFailed($syncState, $e, [
                    'contest_id' => $contest->id,
                    'contest_name' => $contest->name,
                    'platform_slug' => 'codeforces',
                    'platform_contest_id' => $contest->platform_contest_id,
                ]);

                app(ApplicationLogger::class)->error('Rating change sync failed', [
                    'category' => 'import',
                    'platform' => 'codeforces',
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

        $result->metadata = array_merge(
            $result->metadata,
            [
                'platform' => 'codeforces',
                'entity' => 'rating_change',
            ]
        );

        return $result;
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
