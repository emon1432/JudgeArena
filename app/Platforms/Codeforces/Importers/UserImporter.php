<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Importers;

use App\Core\Contracts\Importers\UserImporter as UserImporterContract;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Platforms\Codeforces\Services\Users as CodeforcesUsersService;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Throwable;

class UserImporter implements UserImporterContract
{
    public function __construct(
        private readonly Platform $platformModel,
        private readonly PlatformProfile $platformProfileModel,
        private readonly CodeforcesUsersService $usersService,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {}

    public function import(?string $handle = null): ImportResult
    {
        $result = new ImportResult();

        $platform = $this->platformModel->newQuery()
            ->where('slug', 'codeforces')
            ->first();

        if ($platform === null) {
            app(ApplicationLogger::class)->error('User import failed: platform not found', [
                'category' => 'import',
                'platform' => 'codeforces',
                'source' => self::class,
                'message' => 'Platform "codeforces" not found in database',
            ]);

            return $result;
        }

        $query = $this->platformProfileModel
            ->newQuery()
            ->where('platform_id', $platform->id)
            ->active();

        if ($handle !== null) {
            $query->whereRaw('LOWER(handle) = ?', [mb_strtolower(trim($handle))]);
        }

        $profiles = $query->get();
        $result->incrementChecked($profiles->count());

        $profilesToSync = [];
        $syncStates = [];

        foreach ($profiles as $profile) {
            $normalizedHandle = mb_strtolower(trim((string) $profile->handle));

            if ($normalizedHandle === '') {
                $result->incrementSkipped();
                continue;
            }

            // Optimization: If no specific handle is requested, skip already synced profiles
            $isSynced = $this->platformSyncStateService->isSynced(
                $platform,
                PlatformSyncEntityType::User,
                $normalizedHandle
            );

            if ($handle === null && $isSynced) {
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
                    'platform_slug' => 'codeforces',
                ]
            );

            if ($syncState === null) {
                $result->incrementSkipped();
                continue;
            }

            $profilesToSync[] = $profile;
            $syncStates[$normalizedHandle] = $syncState;
        }

        $chunks = array_chunk($profilesToSync, 50);

        foreach ($chunks as $chunk) {
            $handles = array_map(fn($p) => $p->handle, $chunk);

            try {
                // Try fetching all handles in a single batch API call
                $users = $this->usersService->infos($handles);
                $this->processUsersBatch($users, $chunk, $syncStates, $result);
            } catch (Throwable $e) {
                // If the batch fails (e.g. one user not found), fallback to one-by-one fetch
                $this->processUsersSequentially($chunk, $syncStates, $result);
            }
        }

        $result->metadata = array_merge(
            $result->metadata,
            [
                'platform' => 'codeforces',
                'entity' => 'user',
            ]
        );

        return $result;
    }

    private function processUsersBatch(array $users, array $profilesChunk, array $syncStates, ImportResult $result): void
    {
        $fetchedUsersByHandle = [];
        foreach ($users as $user) {
            $fetchedUsersByHandle[mb_strtolower($user->raw['handle'] ?? '')] = $user;
        }

        foreach ($profilesChunk as $profile) {
            $normalizedHandle = mb_strtolower(trim((string) $profile->handle));
            $syncState = $syncStates[$normalizedHandle];

            if (!isset($fetchedUsersByHandle[$normalizedHandle])) {
                $this->failProfileSync($profile, $syncState, new \RuntimeException("User not found in API response"), $result);
                continue;
            }

            $this->saveProfileAndMarkSynced($profile, $fetchedUsersByHandle[$normalizedHandle], $syncState, $result);
        }
    }

    private function processUsersSequentially(array $profilesChunk, array $syncStates, ImportResult $result): void
    {
        foreach ($profilesChunk as $profile) {
            $normalizedHandle = mb_strtolower(trim((string) $profile->handle));
            $syncState = $syncStates[$normalizedHandle];

            try {
                $user = $this->usersService->info($profile->handle);
                $this->saveProfileAndMarkSynced($profile, $user, $syncState, $result);
            } catch (Throwable $e) {
                $this->failProfileSync($profile, $syncState, $e, $result);
            }
        }
    }

    private function saveProfileAndMarkSynced(PlatformProfile $profile, $user, $syncState, ImportResult $result): void
    {
        $profile->forceFill([
            'raw' => $user->raw,
            'metadata' => [
                'source' => 'user-import',
                'platform' => 'codeforces',
                'handle' => $profile->handle,
                'synced_at' => now(),
            ],
            'last_synced_at' => now(),
        ])->save();

        $this->platformSyncStateService->markSynced($syncState, [
            'profile_id' => $profile->id,
            'handle' => $profile->handle,
        ]);

        $result->incrementUpdated();
    }

    private function failProfileSync(PlatformProfile $profile, $syncState, Throwable $e, ImportResult $result): void
    {
        $result->incrementFailed();

        $this->platformSyncStateService->markFailed($syncState, $e, [
            'profile_id' => $profile->id,
            'handle' => $profile->handle,
        ]);

        app(ApplicationLogger::class)->error('User import failed', [
            'category' => 'import',
            'platform' => 'codeforces',
            'source' => self::class,
            'profile_id' => $profile->id,
            'user_id' => $profile->user_id,
            'handle' => $profile->handle,
            'message' => $e->getMessage(),
            'exception' => get_class($e),
        ], $e);
    }
}

