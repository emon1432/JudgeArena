<?php

namespace App\Services;

use App\Models\PlatformSyncJob;
use Illuminate\Database\Eloquent\Collection;

class SyncSchedulerService
{
    public function getDueJobs(): Collection
    {
        return PlatformSyncJob::query()
            ->with('platform')
            ->where('enabled', true)
            ->orderByDesc('priority')
            ->orderBy('id')
            ->get()
            ->filter(
                fn(PlatformSyncJob $job) => $job->isDue()
            )
            ->values();
    }
}
