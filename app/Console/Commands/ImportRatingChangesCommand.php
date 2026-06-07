<?php

namespace App\Console\Commands;

use App\Core\DTOs\RatingChangeDTO;
use App\Core\Platforms\PlatformRegistry;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Models\PlatformProfile;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Throwable;

class ImportRatingChangesCommand extends Command
{
    protected $signature = 'judgearena:import-rating-changes {platform?}';

    protected $description = 'Import contest rating changes by delegating synchronization to the contest rating change table.';

    public function __construct(
        private readonly Contest $contestModel,
        private readonly ContestRatingChange $contestRatingChangeModel,
        private readonly PlatformProfile $platformProfileModel,
        private readonly PlatformSyncStateService $platformSyncStateService,
        private readonly PlatformRegistry $platformRegistry,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platform = $this->argument('platform');
        $platformSlug = is_string($platform) ? strtolower(trim($platform)) : null;
        $platformLabel = $platformSlug !== null && $platformSlug !== '' ? $platformSlug : 'all';

        app(ApplicationLogger::class)->info('Rating change import started', [
            'category' => 'import',
            'platform' => $platformLabel,
            'source' => self::class,
        ]);

        try {
            $stats = $this->sync(
                $platformLabel === 'all' ? null : $platformLabel
            );
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->error('Rating change import failed', [
                'category' => 'import',
                'platform' => $platformLabel,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('Rating change import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }

        $this->line('Platform: ' . $platformLabel);
        $this->line('Contests Checked: ' . ($stats['contests_checked'] ?? 0));
        $this->line('Contests Synced: ' . ($stats['contests_synced'] ?? 0));
        $this->line('Contests Already Synced: ' . ($stats['contests_already_synced'] ?? 0));
        $this->line('Contests Failed: ' . ($stats['contests_failed'] ?? 0));
        $this->line('Unsupported Platform Contests: ' . ($stats['contests_unsupported_platform'] ?? 0));
        $this->line('Rating Changes Fetched: ' . ($stats['rating_changes_fetched'] ?? 0));
        $this->line('Rating Changes Created: ' . ($stats['rating_changes_created'] ?? 0));
        $this->line('Rating Changes Updated: ' . ($stats['rating_changes_updated'] ?? 0));
        $this->info('Rating change import completed successfully.');

        app(ApplicationLogger::class)->info('Rating change import completed', [
            'category' => 'import',
            'platform' => $platformLabel,
            'source' => self::class,
            'contests_checked' => $stats['contests_checked'] ?? 0,
            'contests_synced' => $stats['contests_synced'] ?? 0,
            'contests_already_synced' => $stats['contests_already_synced'] ?? 0,
            'contests_failed' => $stats['contests_failed'] ?? 0,
            'contests_unsupported_platform' => $stats['contests_unsupported_platform'] ?? 0,
            'rating_changes_fetched' => $stats['rating_changes_fetched'] ?? 0,
            'rating_changes_created' => $stats['rating_changes_created'] ?? 0,
            'rating_changes_updated' => $stats['rating_changes_updated'] ?? 0,
        ]);

        return self::SUCCESS;
    }

    private function sync(?string $platformSlug = null): array
    {
        $query = $this->contestModel->newQuery()
            ->with('platform');

        $normalizedPlatformSlug = $this->normalizePlatformSlug($platformSlug);
        if ($normalizedPlatformSlug !== null) {
            $query->whereHas('platform', function ($platformQuery) use ($normalizedPlatformSlug): void {
                $platformQuery->where('slug', $normalizedPlatformSlug);
            });
        }

        $contests = $query->get();

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

        $pendingContestCount = $pendingContests->count();

        $progressBar = null;
        if ($pendingContestCount > 0) {
            $progressBar = $this->output->createProgressBar($pendingContestCount);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
            $progressBar->setMessage('Preparing rating change sync');
            $progressBar->start();
        } else {
            $this->info('No contests require rating change sync.');
        }

        $stats = [
            'contests_checked' => $contests->count(),
            'contests_synced' => 0,
            'contests_already_synced' => $contests->filter(
                function (Contest $contest): bool {
                    $platform = $contest->platform;

                    if ($platform === null || $contest->platform_contest_id === null || $contest->platform_contest_id === '') {
                        return false;
                    }

                    return $this->platformSyncStateService->isSynced(
                        $platform,
                        PlatformSyncEntityType::RatingChange,
                        (string) $contest->platform_contest_id
                    );
                }
            )->count(),
            'contests_failed' => 0,
            'contests_unsupported_platform' => 0,
            'rating_changes_fetched' => 0,
            'rating_changes_created' => 0,
            'rating_changes_updated' => 0,
        ];

        /** @var Collection<string, Collection<int, Contest>> $contestsByPlatform */
        $contestsByPlatform = $pendingContests->groupBy(function (Contest $contest): string {
            return (string) ($contest->platform?->slug ?? '');
        });

        foreach ($contestsByPlatform as $platformSlugKey => $platformContests) {
            $adapter = $this->platformRegistry->resolve($platformSlugKey);

            if ($adapter === null) {
                $stats['contests_unsupported_platform'] += $platformContests->count();

                app(ApplicationLogger::class)->warning('Skipping rating changes for unsupported platform', [
                    'category' => 'import',
                    'platform' => $platformSlugKey,
                    'source' => self::class,
                    'contest_count' => $platformContests->count(),
                ]);

                if ($progressBar !== null) {
                    $progressBar->setMessage('Skipping unsupported platform ' . $platformSlugKey);
                    $progressBar->advance($platformContests->count());
                }

                continue;
            }

            foreach ($platformContests as $contest) {
                $syncState = $this->platformSyncStateService->markSyncing(
                    $contest->platform,
                    PlatformSyncEntityType::RatingChange,
                    (string) $contest->platform_contest_id,
                    [
                        'contest_id' => $contest->id,
                        'contest_name' => $contest->name,
                        'platform_slug' => $platformSlugKey,
                        'platform_contest_id' => $contest->platform_contest_id,
                    ]
                );

                if ($syncState === null) {
                    if ($progressBar !== null) {
                        $progressBar->advance();
                    }

                    continue;
                }

                if ($progressBar !== null) {
                    $progressBar->setMessage(sprintf(
                        'Syncing rating changes for %s',
                        $contest->name !== '' ? $contest->name : (string) $contest->id
                    ));
                }

                try {
                    $ratingChanges = $adapter->getRatingChanges((string) $contest->platform_contest_id);

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
                                'platform' => $platformSlugKey,
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
                                        'platform' => $platformSlugKey,
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
                        'platform_slug' => $platformSlugKey,
                        'platform_contest_id' => $contest->platform_contest_id,
                        'rating_changes_fetched' => count($ratingChanges),
                    ]);
                    $stats['contests_synced']++;
                } catch (Throwable $e) {
                    $stats['contests_failed']++;

                    $this->platformSyncStateService->markFailed($syncState, $e, [
                        'contest_id' => $contest->id,
                        'contest_name' => $contest->name,
                        'platform_slug' => $platformSlugKey,
                        'platform_contest_id' => $contest->platform_contest_id,
                    ]);

                    app(ApplicationLogger::class)->error('Rating change sync failed', [
                        'category' => 'import',
                        'platform' => $platformSlugKey,
                        'source' => self::class,
                        'contest_id' => $contest->id,
                        'platform_contest_id' => $contest->platform_contest_id,
                        'contest_name' => $contest->name,
                        'message' => $e->getMessage(),
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ], $e);
                } finally {
                    if ($progressBar !== null) {
                        $progressBar->advance();
                    }
                }
            }
        }

        if ($progressBar !== null) {
            $progressBar->setMessage('Rating change sync finished');
            $progressBar->finish();
            $this->newLine(2);
        }

        return $stats;
    }

    private function normalizePlatformSlug(?string $platformSlug): ?string
    {
        $platformSlug = trim((string) $platformSlug);

        return $platformSlug === '' ? null : strtolower($platformSlug);
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

