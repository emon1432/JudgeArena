<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\PlatformSyncStatus;
use App\Http\Controllers\Controller;
use App\Models\PlatformSyncState;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SyncMonitorController extends Controller
{
    public function __construct(
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {}

    public function index(): View
    {
        return view('admin.pages.sync-monitor.index');
    }

    public function retry(PlatformSyncState $syncState): RedirectResponse
    {
        if ($syncState->sync_status !== PlatformSyncStatus::Failed) {
            return redirect()
                ->route('admin.sync-monitor.index')
                ->with('error', __('Only failed sync states can be reset for retry.'));
        }

        $syncState->load('platform');

        $this->platformSyncStateService->resetForRetry($syncState, [
            'retry_reset_by' => auth()->id(),
            'retry_reset_source' => self::class,
        ]);

        app(ApplicationLogger::class)->info('Admin sync monitor retry reset requested', [
            'category' => 'admin',
            'source' => self::class,
            'platform' => $syncState->platform?->slug,
            'entity_type' => $syncState->entity_type instanceof \BackedEnum ? $syncState->entity_type->value : (string) $syncState->entity_type,
            'entity_id' => $syncState->entity_platform_id,
            'sync_state_id' => $syncState->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.sync-monitor.index')
            ->with('success', __('Sync state reset for retry.'));
    }
}
