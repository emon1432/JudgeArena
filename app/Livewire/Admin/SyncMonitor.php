<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Enums\PlatformSyncEntityType;
use App\Enums\PlatformSyncStatus;
use App\Models\PlatformSyncState;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SyncMonitor extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    #[Url(as: 'platform')]
    public string $platform = '';

    #[Url(as: 'entity_type')]
    public string $entity_type = '';

    #[Url(as: 'status')]
    public string $status = '';

    public bool $autoRefresh = true;
    public int $refreshInterval = 3;
    public ?string $feedbackMessage = null;
    public ?string $feedbackType = null;

    public function updatedPlatform(): void
    {
        $this->resetPage('failures_page');
        $this->resetPage('activity_page');
    }

    public function updatedEntityType(): void
    {
        $this->resetPage('failures_page');
        $this->resetPage('activity_page');
    }

    public function updatedStatus(): void
    {
        $this->resetPage('failures_page');
        $this->resetPage('activity_page');
    }

    public function resetFilters(): void
    {
        $this->platform = '';
        $this->entity_type = '';
        $this->status = '';
        $this->resetPage('failures_page');
        $this->resetPage('activity_page');
    }

    public function toggleAutoRefresh(): void
    {
        $this->autoRefresh = ! $this->autoRefresh;
    }

    public function retry(int $syncStateId): void
    {
        $syncState = PlatformSyncState::with('platform')->find($syncStateId);

        if (! $syncState) {
            $this->feedbackMessage = __('Sync state not found.');
            $this->feedbackType = 'danger';
            return;
        }

        if ($syncState->sync_status !== PlatformSyncStatus::Failed) {
            $this->feedbackMessage = __('Only failed sync states can be reset for retry.');
            $this->feedbackType = 'warning';
            return;
        }

        app(PlatformSyncStateService::class)->resetForRetry($syncState, [
            'retry_reset_by' => auth()->id(),
            'retry_reset_source' => self::class,
        ]);

        app(ApplicationLogger::class)->info('Admin sync monitor retry reset requested via Livewire', [
            'category' => 'admin',
            'source' => self::class,
            'platform' => $syncState->platform?->slug,
            'entity_type' => $this->enumValue($syncState->entity_type),
            'entity_id' => $syncState->entity_platform_id,
            'sync_state_id' => $syncState->id,
            'user_id' => auth()->id(),
        ]);

        $this->feedbackMessage = __('Sync state #:id has been reset for retry.', ['id' => $syncState->id]);
        $this->feedbackType = 'success';
    }

    public function render(): View
    {
        $filters = [
            'platform' => $this->platform,
            'entity_type' => $this->entity_type,
            'status' => $this->status,
        ];

        $summary = $this->summary($filters);
        $platformBreakdown = $this->platformBreakdown($filters);
        $recentFailures = $this->recentFailures($filters);
        $recentActivity = $this->recentActivity($filters);
        $filterOptions = $this->filterOptions();
        $entityLabels = $this->entityLabels();
        $isSyncing = ($summary[PlatformSyncStatus::Syncing->value] ?? 0) > 0;

        return view('livewire.admin.sync-monitor', compact(
            'summary',
            'platformBreakdown',
            'recentFailures',
            'recentActivity',
            'filterOptions',
            'entityLabels',
            'filters',
            'isSyncing'
        ));
    }

    /**
     * @param array<string, string> $filters
     * @return array<string, int>
     */
    private function summary(array $filters): array
    {
        $query = $this->filteredQuery($filters);

        $statusCounts = (clone $query)
            ->select('sync_status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('sync_status')
            ->pluck('aggregate', 'sync_status');

        return [
            'total' => (clone $query)->count(),
            PlatformSyncStatus::Pending->value => (int) ($statusCounts[PlatformSyncStatus::Pending->value] ?? 0),
            PlatformSyncStatus::Syncing->value => (int) ($statusCounts[PlatformSyncStatus::Syncing->value] ?? 0),
            PlatformSyncStatus::Synced->value => (int) ($statusCounts[PlatformSyncStatus::Synced->value] ?? 0),
            PlatformSyncStatus::Failed->value => (int) ($statusCounts[PlatformSyncStatus::Failed->value] ?? 0),
        ];
    }

    /**
     * @param array<string, string> $filters
     */
    private function platformBreakdown(array $filters)
    {
        return $this->filteredQuery($filters)
            ->join('platforms', 'platform_sync_states.platform_id', '=', 'platforms.id')
            ->select(
                'platforms.name as platform_name',
                'platforms.slug as platform_slug',
                'platform_sync_states.entity_type',
                'platform_sync_states.sync_status',
                DB::raw('COUNT(*) as aggregate')
            )
            ->groupBy('platforms.id', 'platforms.name', 'platforms.slug', 'platform_sync_states.entity_type', 'platform_sync_states.sync_status')
            ->orderBy('platforms.name')
            ->orderBy('platform_sync_states.entity_type')
            ->get()
            ->groupBy('platform_slug')
            ->map(function ($rows) use ($filters) {
                $entities = $rows->groupBy('entity_type')->map(function ($entityRows) {
                    $counts = [
                        'total' => 0,
                        PlatformSyncStatus::Pending->value => 0,
                        PlatformSyncStatus::Syncing->value => 0,
                        PlatformSyncStatus::Synced->value => 0,
                        PlatformSyncStatus::Failed->value => 0,
                    ];

                    foreach ($entityRows as $row) {
                        $count = (int) $row->aggregate;
                        $status = $row->sync_status;

                        $counts['total'] += $count;
                        $counts[$status->value] = $count;
                    }

                    return $counts;
                });

                $entityTypes = $filters['entity_type'] !== ''
                    ? [$filters['entity_type']]
                    : array_unique(array_merge($this->expectedEntityTypes(), $entities->keys()->all()));

                foreach ($entityTypes as $entityType) {
                    if ($entities->has($entityType)) {
                        continue;
                    }

                    $entities[$entityType] = [
                        'total' => 0,
                        PlatformSyncStatus::Pending->value => 0,
                        PlatformSyncStatus::Syncing->value => 0,
                        PlatformSyncStatus::Synced->value => 0,
                        PlatformSyncStatus::Failed->value => 0,
                    ];
                }

                return [
                    'platform_name' => $rows->first()->platform_name,
                    'entities' => $entities->sortKeys(),
                ];
            });
    }

    /**
     * @param array<string, string> $filters
     */
    private function recentFailures(array $filters)
    {
        return $this->filteredQuery(array_merge($filters, [
            'status' => PlatformSyncStatus::Failed->value,
        ]))
            ->with('platform')
            ->latest('updated_at')
            ->paginate(10, ['*'], 'failures_page');
    }

    /**
     * @param array<string, string> $filters
     */
    private function recentActivity(array $filters)
    {
        return $this->filteredQuery($filters)
            ->with('platform')
            ->latest('updated_at')
            ->paginate(10, ['*'], 'activity_page');
    }

    /**
     * @return array{platforms: \Illuminate\Support\Collection, entityTypes: \Illuminate\Support\Collection, statuses: array<int, string>}
     */
    private function filterOptions(): array
    {
        return [
            'platforms' => PlatformSyncState::query()
                ->join('platforms', 'platform_sync_states.platform_id', '=', 'platforms.id')
                ->select('platforms.slug', 'platforms.name')
                ->distinct()
                ->orderBy('platforms.name')
                ->get(),
            'entityTypes' => PlatformSyncState::query()
                ->select('entity_type')
                ->distinct()
                ->orderBy('entity_type')
                ->pluck('entity_type'),
            'statuses' => array_map(fn(PlatformSyncStatus $status): string => $status->value, PlatformSyncStatus::cases()),
        ];
    }

    /**
     * @param array<string, string> $filters
     */
    private function filteredQuery(array $filters): Builder
    {
        $query = PlatformSyncState::query();

        if (($filters['platform'] ?? '') !== '') {
            $query->whereHas('platform', function (Builder $platformQuery) use ($filters): void {
                $platformQuery->where('slug', $filters['platform']);
            });
        }

        if (($filters['entity_type'] ?? '') !== '') {
            $query->where('entity_type', $filters['entity_type']);
        }

        if (($filters['status'] ?? '') !== '') {
            $query->where('sync_status', $filters['status']);
        }

        return $query;
    }

    /**
     * @return array<string, string>
     */
    private function entityLabels(): array
    {
        return [
            PlatformSyncEntityType::Contest->value => 'Contest syncs',
            PlatformSyncEntityType::ContestProblems->value => 'Problem syncs',
            PlatformSyncEntityType::User->value => 'User syncs',
            PlatformSyncEntityType::UserRatingHistory->value => 'User rating history syncs',
            PlatformSyncEntityType::UserSubmissions->value => 'User submission syncs',
            PlatformSyncEntityType::UserStandings->value => 'User standing syncs',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function expectedEntityTypes(): array
    {
        return [
            PlatformSyncEntityType::Contest->value,
            PlatformSyncEntityType::ContestProblems->value,
            PlatformSyncEntityType::User->value,
            PlatformSyncEntityType::UserRatingHistory->value,
            PlatformSyncEntityType::UserSubmissions->value,
            PlatformSyncEntityType::UserStandings->value,
        ];
    }

    private function enumValue(mixed $value): string
    {
        return $value instanceof \BackedEnum ? (string) $value->value : (string) $value;
    }
}
