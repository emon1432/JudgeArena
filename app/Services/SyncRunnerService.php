<?php

namespace App\Services;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Core\Platforms\PlatformRegistry;
use App\Core\Results\ImportResult;
use App\Enums\SyncRunStatus;
use App\Models\PlatformSyncJob;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class SyncRunnerService
{
    public function __construct(
        private readonly PlatformRegistry $platformRegistry,
    ) {}

    public function run(PlatformSyncJob $job): SyncRunStatus
    {
        $lock = $this->acquireLock($job);

        if (! $lock->get()) {
            return SyncRunStatus::Skipped;
        }

        try {
            $this->markStarted($job);

            $adapter = $this->resolveAdapter($job);

            $stats = $this->execute($adapter, $job);

            $this->markSuccess($job, $stats);

            return SyncRunStatus::Success;
        } catch (Throwable $e) {
            $this->markFailure($job, $e);

            return SyncRunStatus::Failed;
        } finally {
            if ($lock->owner()) {
                $lock->release();
            }
        }
    }

    private function execute(
        PlatformAdapter $adapter,
        PlatformSyncJob $job
    ): ImportResult {
        $method = $job->entity->importerMethod();
        return $adapter->{$method}()->import();
    }

    private function acquireLock(PlatformSyncJob $job): Lock
    {
        $key = sprintf(
            'judgearena:sync:%s:%s',
            $job->platform->slug,
            $job->entity->value,
        );

        return Cache::lock($key, config('app.platform_sync.lock_timeout_seconds', 600));
    }

    private function resolveAdapter(
        PlatformSyncJob $job
    ): PlatformAdapter {
        $adapter = $this->platformRegistry
            ->resolve($job->platform->slug);

        if ($adapter === null) {
            throw new RuntimeException(
                "Unsupported platform: {$job->platform->slug}"
            );
        }

        return $adapter;
    }

    private function markStarted(PlatformSyncJob $job): void
    {
        $job->forceFill([
            'last_started_at' => now(),
            'last_error' => null,
        ])->save();
    }

    private function markSuccess(
        PlatformSyncJob $job,
        ImportResult $stats
    ): void {
        $job->forceFill([
            'last_finished_at' => now(),
            'last_success_at' => now(),
            'last_error' => null,
            'metadata' => array_merge(
                $job->metadata ?? [],
                [
                    'last_stats' => $stats,
                ]
            ),
        ])->save();
    }

    private function markFailure(
        PlatformSyncJob $job,
        Throwable $e
    ): void {
        $job->forceFill([
            'last_finished_at' => now(),
            'last_failed_at' => now(),
            'last_error' => $e->getMessage(),
            'metadata' => array_merge(
                $job->metadata ?? [],
                [
                    'exception' => get_class($e),
                ],
            ),
        ])->save();
    }
}
