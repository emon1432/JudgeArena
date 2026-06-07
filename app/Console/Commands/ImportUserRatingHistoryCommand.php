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
use Throwable;

class ImportUserRatingHistoryCommand extends Command
{
    protected $signature = 'judgearena:import-user-rating-history {platform} {handle}';

    protected $description = 'Import user rating history and persist it into contest rating changes.';

    public function __construct(
        private readonly PlatformProfile $platformProfileModel,
        private readonly Contest $contestModel,
        private readonly ContestRatingChange $contestRatingChangeModel,
        private readonly PlatformSyncStateService $platformSyncStateService,
        private readonly PlatformRegistry $platformRegistry,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower(trim((string) $this->argument('platform')));
        $handle = trim((string) $this->argument('handle'));
        $adapter = $this->platformRegistry->resolve($platformSlug);

        app(ApplicationLogger::class)->info('User rating history import started', [
            'category' => 'import',
            'platform' => $platformSlug,
            'handle' => $handle,
            'source' => self::class,
        ]);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning('User rating history import skipped: unsupported platform', [
                'category' => 'import',
                'platform' => $platformSlug,
                'handle' => $handle,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platformSlug);

            return self::FAILURE;
        }

        $platformProfile = $this->findPlatformProfile($platformSlug, $handle);
        if ($platformProfile === null) {
            app(ApplicationLogger::class)->warning('User rating history import skipped: profile not found', [
                'category' => 'import',
                'platform' => $platformSlug,
                'handle' => $handle,
                'source' => self::class,
            ]);

            $this->error('Platform profile not found for ' . $platformSlug . ' / ' . $handle);

            return self::FAILURE;
        }

        $syncKey = mb_strtolower(trim($handle));
        $syncState = $this->platformSyncStateService->markSyncing(
            $platformProfile->platform,
            PlatformSyncEntityType::UserRatingHistory,
            $syncKey,
            [
                'platform_profile_id' => $platformProfile->id,
                'platform_id' => $platformProfile->platform_id,
                'platform_slug' => $platformSlug,
                'handle' => $handle,
            ]
        );

        if ($syncState === null) {
            $this->error('User rating history sync is already running or completed for this handle.');

            return self::FAILURE;
        }

        $this->info('Fetching rating history...');
        $progressBar = $this->output->createProgressBar(1);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Loading rating history');
        $progressBar->start();

        $rowsCreated = 0;
        $rowsUpdated = 0;
        $rowsSkipped = 0;
        $rowsFailed = 0;

        try {
            $ratingHistory = $adapter->getUserRatingHistory($handle);

            $progressBar->setMessage('Rating history loaded');
            $progressBar->advance();
            $progressBar->finish();
            $this->newLine(2);

            $this->line('Platform: ' . $platformSlug);
            $this->line('Handle: ' . $handle);
            $this->line('Rows Fetched: ' . count($ratingHistory));

            foreach ($ratingHistory as $ratingChange) {
                if (! $ratingChange instanceof RatingChangeDTO) {
                    $rowsSkipped++;
                    continue;
                }

                $contest = $this->findContest($platformProfile->platform_id, $ratingChange->contestPlatformId);
                if ($contest === null) {
                    $rowsSkipped++;

                    app(ApplicationLogger::class)->warning('User rating history contest mapping missing', [
                        'category' => 'import',
                        'platform' => $platformSlug,
                        'handle' => $handle,
                        'profile_id' => $platformProfile->id,
                        'platform_profile_id' => $platformProfile->id,
                        'contest_platform_id' => $ratingChange->contestPlatformId,
                        'source' => self::class,
                    ]);

                    continue;
                }

                try {
                    $contestRatingChange = $this->contestRatingChangeModel->newQuery()->updateOrCreate(
                        [
                            'contest_id' => $contest->id,
                            'handle' => $ratingChange->handle,
                        ],
                        [
                            'platform_id' => $platformProfile->platform_id,
                            'platform_profile_id' => $platformProfile->id,
                            'is_rated' => $ratingChange->isRated,
                            'rank' => $ratingChange->rank,
                            'old_rating' => $ratingChange->oldRating,
                            'new_rating' => $ratingChange->newRating,
                            'rating_change' => $ratingChange->ratingChange,
                            'performance' => $ratingChange->performance,
                            'last_synced_at' => now(),
                            'metadata' => array_merge([
                                'source' => 'user-rating-history-import',
                                'platform' => $platformSlug,
                                'handle' => $handle,
                                'platform_profile_id' => $platformProfile->id,
                                'contest_platform_id' => $ratingChange->contestPlatformId,
                                'contest_id' => $contest->id,
                                'synced_at' => now(),
                            ], $ratingChange->metadata),
                            'raw' => $ratingChange->raw,
                            'status' => 'Active',
                        ]
                    );

                    if ($contestRatingChange->wasRecentlyCreated) {
                        $rowsCreated++;
                    } else {
                        $rowsUpdated++;
                    }
                } catch (Throwable $e) {
                    $rowsFailed++;

                    app(ApplicationLogger::class)->error('User rating history row import failed', [
                        'category' => 'import',
                        'platform' => $platformSlug,
                        'handle' => $handle,
                        'profile_id' => $platformProfile->id,
                        'platform_profile_id' => $platformProfile->id,
                        'contest_platform_id' => $ratingChange->contestPlatformId,
                        'contest_id' => $contest->id,
                        'source' => self::class,
                        'message' => $e->getMessage(),
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ], $e);
                }
            }

            if ($rowsFailed > 0) {
                $this->platformSyncStateService->markFailed($syncState, 'One or more rating history rows failed.', [
                    'platform_profile_id' => $platformProfile->id,
                    'platform_id' => $platformProfile->platform_id,
                    'platform_slug' => $platformSlug,
                    'handle' => $handle,
                ]);
            } else {
                $this->platformSyncStateService->markSynced($syncState, [
                    'platform_profile_id' => $platformProfile->id,
                    'platform_id' => $platformProfile->platform_id,
                    'platform_slug' => $platformSlug,
                    'handle' => $handle,
                    'rows_fetched' => count($ratingHistory),
                    'rows_created' => $rowsCreated,
                    'rows_updated' => $rowsUpdated,
                    'rows_skipped' => $rowsSkipped,
                    'rows_failed' => $rowsFailed,
                ]);
            }

            $this->line('Rows Created: ' . $rowsCreated);
            $this->line('Rows Updated: ' . $rowsUpdated);
            $this->line('Rows Skipped: ' . $rowsSkipped);
            $this->line('Rows Failed: ' . $rowsFailed);

            app(ApplicationLogger::class)->info('User rating history import completed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'handle' => $handle,
                'profile_id' => $platformProfile->id,
                'platform_profile_id' => $platformProfile->id,
                'rows_fetched' => count($ratingHistory),
                'rows_created' => $rowsCreated,
                'rows_updated' => $rowsUpdated,
                'rows_skipped' => $rowsSkipped,
                'rows_failed' => $rowsFailed,
                'source' => self::class,
            ]);

            return $rowsFailed > 0 ? self::FAILURE : self::SUCCESS;
        } catch (Throwable $e) {
            $progressBar->setMessage('Rating history request failed');
            $progressBar->finish();
            $this->newLine(2);

            $this->platformSyncStateService->markFailed($syncState, $e, [
                'platform_profile_id' => $platformProfile->id,
                'platform_id' => $platformProfile->platform_id,
                'platform_slug' => $platformSlug,
                'handle' => $handle,
            ]);

            app(ApplicationLogger::class)->error('User rating history import failed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'handle' => $handle,
                'profile_id' => $platformProfile->id,
                'platform_profile_id' => $platformProfile->id,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('User rating history import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }

    private function findPlatformProfile(string $platformSlug, string $handle): ?PlatformProfile
    {
        return $this->platformProfileModel->newQuery()
            ->with('platform')
            ->whereHas('platform', function ($platformQuery) use ($platformSlug): void {
                $platformQuery->where('slug', $platformSlug);
            })
            ->whereRaw('LOWER(handle) = ?', [mb_strtolower(trim($handle))])
            ->first();
    }

    private function findContest(int $platformId, string $contestPlatformId): ?Contest
    {
        $contestPlatformId = trim($contestPlatformId);

        if ($contestPlatformId === '') {
            return null;
        }

        return $this->contestModel->newQuery()
            ->where('platform_id', $platformId)
            ->where('platform_contest_id', $contestPlatformId)
            ->first();
    }
}
