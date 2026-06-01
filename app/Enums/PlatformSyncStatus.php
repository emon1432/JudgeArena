<?php

namespace App\Enums;

/**
 * Sync states move through a small, explicit lifecycle so importers can be
 * retried safely without coupling that decision to adapter logic.
 */
enum PlatformSyncStatus: string
{
    case Pending = 'pending';
    case Syncing = 'syncing';
    case Synced = 'synced';
    case Failed = 'failed';

    public static function retryableStatuses(): array
    {
        return [self::Pending, self::Failed];
    }
}
