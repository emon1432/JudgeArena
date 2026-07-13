<?php

namespace App\Platforms\AtCoder\Importers;

use App\Core\Contracts\Importers\UserImporter as UserImporterContract;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Throwable;


class UserImporter implements UserImporterContract
{

    public function __construct(
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
                'User import failed: platform not found',
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

                $user = $this->adapter->getUser(
                    $profile->handle
                );

                $profile->forceFill([
                    'raw' => $user->raw,

                    'metadata' => [
                        'source' => 'user-import',
                        'platform' => 'atcoder',
                        'handle' => $profile->handle,
                        'synced_at' => now(),
                    ],

                    'last_synced_at' => now(),
                ])->save();

                $this->platformSyncStateService->markSynced(
                    $syncState,
                    [
                        'profile_id' => $profile->id,
                        'handle' => $profile->handle,
                    ]
                );

                $result->incrementUpdated();
            } catch (Throwable $e) {

                $result->incrementFailed();

                $this->platformSyncStateService->markFailed(
                    $syncState,
                    $e,
                    [
                        'profile_id' => $profile->id,
                        'handle' => $profile->handle,
                    ]
                );

                app(ApplicationLogger::class)->error(
                    'User import failed',
                    [
                        'category' => 'import',
                        'platform' => 'atcoder',
                        'source' => self::class,

                        'profile_id' => $profile->id,
                        'user_id' => $profile->user_id,
                        'handle' => $profile->handle,

                        'message' => $e->getMessage(),
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                    $e
                );
            }
        }

        $result->metadata = array_merge(
            $result->metadata,
            [
                'platform' => 'atcoder',
                'entity' => 'user',
            ]
        );

        return $result;
    }
}
