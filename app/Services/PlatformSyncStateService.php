<?php

namespace App\Services;

use App\Enums\PlatformSyncEntityType;
use App\Enums\PlatformSyncStatus;
use App\Models\Platform;
use App\Models\PlatformSyncState;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class PlatformSyncStateService
{
    /**
     * Sync state belongs to orchestration, not adapters or transformers.
     *
     * The service centralizes the lifecycle so every importer can share the
     * same idempotent semantics:
     * - pending: eligible to run for the first time
     * - syncing: claimed by a worker and currently running
     * - synced: completed successfully
     * - failed: last run ended in an error, but the record can be retried
     *
     * Contest sync and contest-problem sync are separate entity types because a
     * contest-level sync will eventually track contest metadata, while a
     * contest-problem sync tracks the child problem list. Mixing them would
     * create false positives when one workflow is complete and the other is not.
     *
     * Example for future importers:
     * $state = $syncStates->markSyncing($platform, PlatformSyncEntityType::User, $dto->platformUserId);
     * if ($state === null) {
     *     return;
     * }
     *
     * try {
     *     // fetch DTOs, persist domain rows
     *     $syncStates->markSynced($state);
     * } catch (Throwable $exception) {
     *     $syncStates->markFailed($state, $exception);
     * }
     */
    public function findState(
        Platform $platform,
        PlatformSyncEntityType|string $entityType,
        ?string $entityPlatformId = null,
    ): ?PlatformSyncState {
        return PlatformSyncState::query()->where(
            $this->stateKey($platform, $entityType, $entityPlatformId)
        )->first();
    }

    public function getOrCreateState(
        Platform $platform,
        PlatformSyncEntityType|string $entityType,
        ?string $entityPlatformId = null,
        array $metadata = [],
    ): PlatformSyncState {
        $state = $this->findState($platform, $entityType, $entityPlatformId);

        if ($state !== null) {
            if ($metadata !== [] && $state->metadata === null) {
                $state->forceFill(['metadata' => $metadata])->save();
            }

            return $state->refresh();
        }

        try {
            return PlatformSyncState::query()->create([
                'platform_id' => $platform->id,
                'entity_type' => $this->entityTypeValue($entityType),
                'entity_platform_id' => $entityPlatformId,
                'sync_status' => PlatformSyncStatus::Pending->value,
                'metadata' => $metadata,
            ]);
        } catch (QueryException $exception) {
            $state = $this->findState($platform, $entityType, $entityPlatformId);

            if ($state === null) {
                throw $exception;
            }

            return $state;
        }
    }

    /**
     * Claiming a sync is the point where the importer commits to work.
     *
     * A syncing row is also eligible if it is stale, which recovers crashes,
     * server restarts, and deployment interruptions without manual database
     * intervention.
     */
    public function markSyncing(
        Platform $platform,
        PlatformSyncEntityType|string $entityType,
        ?string $entityPlatformId = null,
        array $metadata = [],
    ): ?PlatformSyncState {
        return DB::transaction(function () use ($platform, $entityType, $entityPlatformId, $metadata): ?PlatformSyncState {
            $state = $this->getOrCreateState($platform, $entityType, $entityPlatformId, $metadata);

            if (! $this->canBeRetried($state)) {
                return null;
            }

            $state = PlatformSyncState::query()
                ->whereKey($state->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $this->canBeRetried($state)) {
                return null;
            }

            $state->forceFill([
                'sync_status' => PlatformSyncStatus::Syncing->value,
                'last_attempted_at' => now(),
                'last_error' => null,
                'metadata' => $this->mergeMetadata($state->metadata, $metadata),
            ])->save();

            return $state->refresh();
        });
    }

    public function markSynced(PlatformSyncState $state, array $metadata = []): PlatformSyncState
    {
        $state->forceFill([
            'sync_status' => PlatformSyncStatus::Synced->value,
            'last_synced_at' => now(),
            'last_attempted_at' => $state->last_attempted_at ?? now(),
            'last_error' => null,
            'metadata' => $this->mergeMetadata($state->metadata, $metadata),
        ])->save();

        return $state->refresh();
    }

    public function markFailed(PlatformSyncState $state, Throwable|string $error, array $metadata = []): PlatformSyncState
    {
        $state->forceFill([
            'sync_status' => PlatformSyncStatus::Failed->value,
            'last_attempted_at' => now(),
            'last_error' => $error instanceof Throwable ? $error->getMessage() : $error,
            'metadata' => $this->mergeMetadata($state->metadata, $metadata),
        ])->save();

        return $state->refresh();
    }

    public function resetForRetry(PlatformSyncState $state, array $metadata = []): PlatformSyncState
    {
        $existingMetadata = is_array($state->metadata) ? $state->metadata : [];
        $retryCount = (int) ($existingMetadata['retry_count'] ?? 0);

        $state->forceFill([
            'sync_status' => PlatformSyncStatus::Pending->value,
            'last_attempted_at' => null,
            'last_error' => null,
            'metadata' => $this->mergeMetadata($existingMetadata, array_merge([
                'retry_count' => $retryCount + 1,
                'retry_reset_at' => now(),
            ], $metadata)),
        ])->save();

        return $state->refresh();
    }

    public function shouldSync(
        Platform $platform,
        PlatformSyncEntityType|string $entityType,
        ?string $entityPlatformId = null,
    ): bool {
        $state = $this->findState($platform, $entityType, $entityPlatformId);

        return $this->canBeRetried($state);
    }

    public function isSynced(
        Platform $platform,
        PlatformSyncEntityType|string $entityType,
        ?string $entityPlatformId = null,
    ): bool {
        return $this->findState($platform, $entityType, $entityPlatformId)?->sync_status === PlatformSyncStatus::Synced;
    }

    public function isStaleSync(PlatformSyncState $state, ?Carbon $referenceTime = null): bool
    {
        if ($state->sync_status !== PlatformSyncStatus::Syncing) {
            return false;
        }

        if ($state->last_attempted_at === null) {
            return false;
        }

        return $state->last_attempted_at->lte($this->staleSyncCutoff($referenceTime));
    }

    public function canBeRetried(?PlatformSyncState $state, ?Carbon $referenceTime = null): bool
    {
        if ($state === null) {
            return true;
        }

        if (in_array($state->sync_status, PlatformSyncStatus::retryableStatuses(), true)) {
            return true;
        }

        return $state->sync_status === PlatformSyncStatus::Syncing
            && $this->isStaleSync($state, $referenceTime);
    }

    private function stateKey(
        Platform $platform,
        PlatformSyncEntityType|string $entityType,
        ?string $entityPlatformId,
    ): array {
        return [
            'platform_id' => $platform->id,
            'entity_type' => $this->entityTypeValue($entityType),
            'entity_platform_id' => $entityPlatformId,
        ];
    }

    private function entityTypeValue(PlatformSyncEntityType|string $entityType): string
    {
        return $entityType instanceof PlatformSyncEntityType ? $entityType->value : $entityType;
    }

    private function mergeMetadata(mixed $existingMetadata, array $metadata): array
    {
        $existingMetadata = is_array($existingMetadata) ? $existingMetadata : [];

        return array_merge($existingMetadata, $metadata);
    }

    private function staleSyncCutoff(?Carbon $referenceTime = null): Carbon
    {
        $referenceTime ??= now();
        $timeoutMinutes = (int) config('app.platform_sync.stale_sync_timeout_minutes', 120);

        return $referenceTime->copy()->subMinutes($timeoutMinutes);
    }
}
