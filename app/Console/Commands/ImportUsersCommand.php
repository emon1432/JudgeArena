<?php

namespace App\Console\Commands;

use App\Core\Platforms\PlatformRegistry;
use App\Enums\PlatformSyncEntityType;
use App\Models\PlatformProfile;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Throwable;

class ImportUsersCommand extends Command
{
    protected $signature = 'judgearena:import-users {platform?}';

    protected $description = 'Import user profiles by delegating synchronization to the platform profile table.';

    public function __construct(
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

        app(ApplicationLogger::class)->info('User import started', [
            'category' => 'import',
            'platform' => $platformLabel,
            'source' => self::class,
        ]);

        try {
            $stats = $this->sync(
                $platformLabel === 'all' ? null : $platformLabel
            );
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->error('User import failed', [
                'category' => 'import',
                'platform' => $platformLabel,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('User import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }

        $this->line('Platform: ' . $platformLabel);
        $this->line('Profiles Checked: ' . ($stats['profiles_checked'] ?? 0));
        $this->line('Profiles Synced: ' . ($stats['profiles_synced'] ?? 0));
        $this->line('Profiles Already Synced: ' . ($stats['profiles_already_synced'] ?? 0));
        $this->line('Profiles Failed: ' . ($stats['profiles_failed'] ?? 0));
        $this->line('Unsupported Platform Profiles: ' . ($stats['profiles_unsupported_platform'] ?? 0));
        $this->line('Profiles Missing Handle: ' . ($stats['profiles_missing_handle'] ?? 0));
        $this->info('User import completed successfully.');

        app(ApplicationLogger::class)->info('User import completed', [
            'category' => 'import',
            'platform' => $platformLabel,
            'source' => self::class,
            'profiles_checked' => $stats['profiles_checked'] ?? 0,
            'profiles_synced' => $stats['profiles_synced'] ?? 0,
            'profiles_already_synced' => $stats['profiles_already_synced'] ?? 0,
            'profiles_failed' => $stats['profiles_failed'] ?? 0,
            'profiles_unsupported_platform' => $stats['profiles_unsupported_platform'] ?? 0,
            'profiles_missing_handle' => $stats['profiles_missing_handle'] ?? 0,
        ]);

        return self::SUCCESS;
    }

    private function sync(?string $platformSlug = null): array
    {
        $query = $this->platformProfileModel->newQuery()
            ->active()
            ->with('platform');

        $normalizedPlatformSlug = $this->normalizePlatformSlug($platformSlug);
        if ($normalizedPlatformSlug !== null) {
            $query->whereHas('platform', function ($platformQuery) use ($normalizedPlatformSlug): void {
                $platformQuery->where('slug', $normalizedPlatformSlug);
            });
        }

        $profiles = $query->get();
        $missingHandleProfiles = $profiles->filter(function (PlatformProfile $profile): bool {
            return trim((string) $profile->handle) === '';
        });
        $profilesWithHandle = $profiles->reject(function (PlatformProfile $profile): bool {
            return trim((string) $profile->handle) === '';
        });

        foreach ($missingHandleProfiles as $profile) {
            app(ApplicationLogger::class)->warning('User import skipped: missing handle', [
                'category' => 'import',
                'platform' => (string) ($profile->platform?->slug ?? ''),
                'source' => self::class,
                'profile_id' => $profile->id,
                'platform_id' => $profile->platform_id,
                'user_id' => $profile->user_id,
            ]);
        }

        $profilesAlreadySynced = $profilesWithHandle->filter(function (PlatformProfile $profile): bool {
            $platform = $profile->platform;

            if ($platform === null) {
                return false;
            }

            return $this->platformSyncStateService->isSynced(
                $platform,
                PlatformSyncEntityType::User,
                (string) mb_strtolower(trim($profile->handle))
            );
        });

        $pendingProfiles = $profilesWithHandle->filter(function (PlatformProfile $profile): bool {
            $platform = $profile->platform;

            if ($platform === null) {
                return false;
            }

            $syncState = $this->platformSyncStateService->findState(
                $platform,
                PlatformSyncEntityType::User,
                (string) mb_strtolower(trim($profile->handle))
            );

            return $this->platformSyncStateService->canBeRetried($syncState);
        });

        $pendingProfileCount = $pendingProfiles->count();

        $progressBar = null;
        if ($pendingProfileCount > 0) {
            $progressBar = $this->output->createProgressBar($pendingProfileCount);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
            $progressBar->setMessage('Preparing user sync');
            $progressBar->start();
        } else {
            $this->info('No user profiles require synchronization.');
        }

        $stats = [
            'profiles_checked' => $profiles->count(),
            'profiles_synced' => 0,
            'profiles_already_synced' => $profilesAlreadySynced->count(),
            'profiles_failed' => 0,
            'profiles_unsupported_platform' => 0,
            'profiles_missing_handle' => $missingHandleProfiles->count(),
        ];

        /** @var Collection<string, Collection<int, PlatformProfile>> $profilesByPlatform */
        $profilesByPlatform = $pendingProfiles->groupBy(function (PlatformProfile $profile): string {
            return (string) ($profile->platform?->slug ?? '');
        });

        foreach ($profilesByPlatform as $platformSlugKey => $platformProfiles) {
            $adapter = $this->platformRegistry->resolve($platformSlugKey);

            if ($adapter === null) {
                $stats['profiles_unsupported_platform'] += $platformProfiles->count();

                app(ApplicationLogger::class)->warning('Skipping users for unsupported platform', [
                    'category' => 'import',
                    'platform' => $platformSlugKey,
                    'source' => self::class,
                    'profile_count' => $platformProfiles->count(),
                ]);

                if ($progressBar !== null) {
                    $progressBar->setMessage('Skipping unsupported platform ' . $platformSlugKey);
                    $progressBar->advance($platformProfiles->count());
                }

                continue;
            }

            foreach ($platformProfiles as $profile) {
                $syncState = $this->platformSyncStateService->markSyncing(
                    $profile->platform,
                    PlatformSyncEntityType::User,
                    (string) mb_strtolower(trim($profile->handle)),
                    [
                        'profile_id' => $profile->id,
                        'platform_slug' => $platformSlugKey,
                        'handle' => $profile->handle,
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
                        'Syncing user %s',
                        $profile->handle
                    ));
                }

                try {
                    $user = $adapter->getUser((string) $profile->handle);

                    $profile->forceFill([
                        'raw' => $user->raw,
                        'metadata' => [
                            'source' => 'user-sync',
                            'platform' => $platformSlugKey,
                            'handle' => $profile->handle,
                            'synced_at' => now(),
                        ],
                        'last_synced_at' => now(),
                    ])->save();

                    $this->platformSyncStateService->markSynced($syncState, [
                        'handle' => $profile->handle,
                        'platform_slug' => $platformSlugKey,
                    ]);

                    $stats['profiles_synced']++;

                    app(ApplicationLogger::class)->info('User profile synced', [
                        'category' => 'import',
                        'platform' => $platformSlugKey,
                        'source' => self::class,
                        'profile_id' => $profile->id,
                        'platform_profile_id' => $profile->id,
                        'handle' => $profile->handle,
                        'user_id' => $profile->user_id,
                    ]);
                } catch (Throwable $e) {
                    $stats['profiles_failed']++;

                    $this->platformSyncStateService->markFailed($syncState, $e, [
                        'profile_id' => $profile->id,
                        'platform_slug' => $platformSlugKey,
                        'handle' => $profile->handle,
                    ]);

                    app(ApplicationLogger::class)->error('User profile sync failed', [
                        'category' => 'import',
                        'platform' => $platformSlugKey,
                        'source' => self::class,
                        'profile_id' => $profile->id,
                        'platform_profile_id' => $profile->id,
                        'handle' => $profile->handle,
                        'user_id' => $profile->user_id,
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
            $progressBar->setMessage('User sync finished');
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
}
