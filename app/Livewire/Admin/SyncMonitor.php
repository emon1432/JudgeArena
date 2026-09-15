<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Enums\PlatformSyncEntityType;
use App\Enums\PlatformSyncJobEntity;
use App\Enums\PlatformSyncStatus;
use App\Models\PlatformSyncJob;
use App\Models\PlatformSyncState;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use App\Services\StandingsCacheService;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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

    #[Url(as: 'tab')]
    public string $activeTab = 'overview';

    public bool $autoRefresh = true;

    public int $refreshInterval = 3;

    public ?string $feedbackMessage = null;

    public ?string $feedbackType = null;

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['overview', 'failures', 'activity'], true)) {
            $this->activeTab = $tab;
        }
    }

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

        if (! in_array($syncState->sync_status, [PlatformSyncStatus::Failed, PlatformSyncStatus::Syncing], true)) {
            $this->feedbackMessage = __('Only failed or stuck syncing states can be reset for retry.');
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

    public function resetStuckSyncs(): void
    {
        $count = app(PlatformSyncStateService::class)->resetAllSyncingStates();

        app(ApplicationLogger::class)->info('Admin reset all stuck syncing states via Livewire', [
            'category' => 'admin',
            'source' => self::class,
            'reset_count' => $count,
            'user_id' => auth()->id(),
        ]);

        if ($count > 0) {
            $this->feedbackMessage = __(':count stuck syncing state(s) have been reset to Pending.', ['count' => $count]);
            $this->feedbackType = 'success';
        } else {
            $this->feedbackMessage = __('No syncing states were found to reset.');
            $this->feedbackType = 'info';
        }
    }

    public function retryAllFailures(): void
    {
        $failedStates = $this->filteredQuery([
            'platform' => $this->platform,
            'entity_type' => $this->entity_type,
            'status' => PlatformSyncStatus::Failed->value,
        ])->get();

        $count = 0;
        $syncService = app(PlatformSyncStateService::class);

        foreach ($failedStates as $state) {
            $syncService->resetForRetry($state, [
                'retry_reset_by' => auth()->id(),
                'retry_reset_source' => self::class.'@retryAllFailures',
            ]);
            $count++;
        }

        app(ApplicationLogger::class)->info('Admin reset all failed sync states via Livewire', [
            'category' => 'admin',
            'source' => self::class,
            'reset_count' => $count,
            'user_id' => auth()->id(),
        ]);

        if ($count > 0) {
            $this->feedbackMessage = __(':count failed sync state(s) have been reset to Pending for retry.', ['count' => $count]);
            $this->feedbackType = 'success';
        } else {
            $this->feedbackMessage = __('No failed sync states found to reset.');
            $this->feedbackType = 'info';
        }
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
        $lastRefreshedAt = now()->format('h:i:s A');
        $driveHealth = app(StandingsCacheService::class)->getConnectionHealth();

        return view('livewire.admin.sync-monitor', compact(
            'summary',
            'driveHealth',
            'platformBreakdown',
            'recentFailures',
            'recentActivity',
            'filterOptions',
            'entityLabels',
            'filters',
            'isSyncing',
            'lastRefreshedAt'
        ));
    }

    /**
     * @param  array<string, string>  $filters
     * @return array<string, mixed>
     */
    private function summary(array $filters): array
    {
        $query = $this->filteredQuery($filters);

        $statusCounts = (clone $query)
            ->select('sync_status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('sync_status')
            ->pluck('aggregate', 'sync_status');

        $total = (clone $query)->count();
        $synced = (int) ($statusCounts[PlatformSyncStatus::Synced->value] ?? 0);
        $pending = (int) ($statusCounts[PlatformSyncStatus::Pending->value] ?? 0);
        $syncing = (int) ($statusCounts[PlatformSyncStatus::Syncing->value] ?? 0);
        $failed = (int) ($statusCounts[PlatformSyncStatus::Failed->value] ?? 0);

        $healthScore = $total > 0 ? round(($synced / $total) * 100, 1) : 100.0;

        $healthLabel = match (true) {
            $healthScore >= 95.0 => 'Optimal',
            $healthScore >= 80.0 => 'Healthy',
            $healthScore >= 50.0 => 'Degraded',
            default => 'Critical',
        };

        $healthClass = match (true) {
            $healthScore >= 95.0 => 'success',
            $healthScore >= 80.0 => 'info',
            $healthScore >= 50.0 => 'warning',
            default => 'danger',
        };

        return [
            'total' => $total,
            PlatformSyncStatus::Pending->value => $pending,
            PlatformSyncStatus::Syncing->value => $syncing,
            PlatformSyncStatus::Synced->value => $synced,
            PlatformSyncStatus::Failed->value => $failed,
            'health_score' => $healthScore,
            'health_label' => $healthLabel,
            'health_class' => $healthClass,
            'synced_pct' => $total > 0 ? round(($synced / $total) * 100, 1) : 0,
            'pending_pct' => $total > 0 ? round(($pending / $total) * 100, 1) : 0,
            'syncing_pct' => $total > 0 ? round(($syncing / $total) * 100, 1) : 0,
            'failed_pct' => $total > 0 ? round(($failed / $total) * 100, 1) : 0,
        ];
    }

    /**
     * @param  array<string, string>  $filters
     */
    private function platformBreakdown(array $filters)
    {
        $syncJobs = PlatformSyncJob::query()
            ->with('platform')
            ->get()
            ->keyBy(function (PlatformSyncJob $job) {
                return ($job->platform?->slug ?? '').':'.$this->enumValue($job->entity);
            });

        return $this->filteredQuery($filters)
            ->join('platforms', 'platform_sync_states.platform_id', '=', 'platforms.id')
            ->select(
                'platforms.id as platform_id',
                'platforms.name as platform_name',
                'platforms.slug as platform_slug',
                'platforms.base_url as platform_base_url',
                'platform_sync_states.entity_type',
                'platform_sync_states.sync_status',
                DB::raw('COUNT(*) as aggregate')
            )
            ->groupBy(
                'platforms.id',
                'platforms.name',
                'platforms.slug',
                'platforms.base_url',
                'platform_sync_states.entity_type',
                'platform_sync_states.sync_status'
            )
            ->orderBy('platforms.name')
            ->orderBy('platform_sync_states.entity_type')
            ->get()
            ->groupBy('platform_slug')
            ->map(function ($rows, $platformSlug) use ($filters, $syncJobs) {
                $firstRow = $rows->first();

                $entities = $rows->groupBy(function ($row) {
                    return $this->enumValue($row->entity_type);
                })->map(function ($entityRows, $entityType) use ($platformSlug, $syncJobs) {
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

                    $tot = $counts['total'];
                    $counts['synced_pct'] = $tot > 0 ? round(($counts[PlatformSyncStatus::Synced->value] / $tot) * 100, 1) : 0;
                    $counts['syncing_pct'] = $tot > 0 ? round(($counts[PlatformSyncStatus::Syncing->value] / $tot) * 100, 1) : 0;
                    $counts['pending_pct'] = $tot > 0 ? round(($counts[PlatformSyncStatus::Pending->value] / $tot) * 100, 1) : 0;
                    $counts['failed_pct'] = $tot > 0 ? round(($counts[PlatformSyncStatus::Failed->value] / $tot) * 100, 1) : 0;

                    $counts['job'] = $this->resolveJobInfo($syncJobs, (string) $platformSlug, (string) $entityType);

                    return $counts;
                });

                $entityTypes = $filters['entity_type'] !== ''
                    ? [$this->enumValue($filters['entity_type'])]
                    : array_unique(array_merge($this->expectedEntityTypes(), $entities->keys()->all()));

                foreach ($entityTypes as $entityType) {
                    $entityTypeKey = $this->enumValue($entityType);
                    if ($entities->has($entityTypeKey)) {
                        continue;
                    }

                    $entities[$entityTypeKey] = [
                        'total' => 0,
                        PlatformSyncStatus::Pending->value => 0,
                        PlatformSyncStatus::Syncing->value => 0,
                        PlatformSyncStatus::Synced->value => 0,
                        PlatformSyncStatus::Failed->value => 0,
                        'synced_pct' => 0,
                        'syncing_pct' => 0,
                        'pending_pct' => 0,
                        'failed_pct' => 0,
                        'job' => $this->resolveJobInfo($syncJobs, (string) $platformSlug, $entityTypeKey),
                    ];
                }

                $platformTotal = (int) $entities->sum('total');
                $platformSynced = (int) $entities->sum(PlatformSyncStatus::Synced->value);
                $platformFailed = (int) $entities->sum(PlatformSyncStatus::Failed->value);
                $platformSyncing = (int) $entities->sum(PlatformSyncStatus::Syncing->value);
                $platformPending = (int) $entities->sum(PlatformSyncStatus::Pending->value);
                $platformHealth = $platformTotal > 0 ? round(($platformSynced / $platformTotal) * 100, 1) : 100.0;

                return [
                    'platform_name' => $firstRow->platform_name,
                    'platform_slug' => $firstRow->platform_slug,
                    'platform_base_url' => $firstRow->platform_base_url,
                    'total' => $platformTotal,
                    'synced' => $platformSynced,
                    'failed' => $platformFailed,
                    'syncing' => $platformSyncing,
                    'pending' => $platformPending,
                    'health_score' => $platformHealth,
                    'entities' => $entities->sortKeys(),
                ];
            });
    }

    private function resolveJobInfo(Collection $syncJobs, string $platformSlug, mixed $stateEntityType): ?array
    {
        $entityValue = $this->enumValue($stateEntityType);
        $jobEntity = $this->mapStateEntityToJobEntity($entityValue);
        $jobKey = $platformSlug.':'.$jobEntity;
        $job = $syncJobs->get($jobKey);

        if (! $job) {
            return null;
        }

        $nextRunAt = $job->nextRunAt();
        $isDue = $job->isDue();

        return [
            'id' => $job->id,
            'enabled' => (bool) $job->enabled,
            'interval_minutes' => (int) $job->interval_minutes,
            'is_due' => $isDue,
            'next_run_at' => $nextRunAt,
            'next_run_timestamp' => $nextRunAt?->getTimestamp(),
            'next_run_human' => $job->enabled
                ? ($isDue ? 'Due now' : $this->formatRemainingTime($nextRunAt))
                : 'Disabled',
            'last_success_at' => $job->last_success_at,
            'last_success_human' => $job->last_success_at ? $job->last_success_at->diffForHumans() : null,
        ];
    }

    private function formatRemainingTime(?CarbonInterface $nextRunAt): string
    {
        if (! $nextRunAt) {
            return 'Immediate';
        }

        $diffSeconds = (int) now()->diffInSeconds($nextRunAt, false);

        if ($diffSeconds <= 0) {
            return 'Due now';
        }

        $hours = intdiv($diffSeconds, 3600);
        $minutes = intdiv($diffSeconds % 3600, 60);
        $seconds = $diffSeconds % 60;

        if ($hours > 0) {
            return sprintf('%02dh %02dm %02ds', $hours, $minutes, $seconds);
        }

        return sprintf('%02dm %02ds', $minutes, $seconds);
    }

    private function mapStateEntityToJobEntity(string $stateEntityType): string
    {
        return match ($stateEntityType) {
            PlatformSyncEntityType::ContestProblems->value => PlatformSyncJobEntity::Problem->value,
            default => $stateEntityType,
        };
    }

    /**
     * @param  array<string, string>  $filters
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
     * @param  array<string, string>  $filters
     */
    private function recentActivity(array $filters)
    {
        return $this->filteredQuery($filters)
            ->with('platform')
            ->latest('updated_at')
            ->paginate(10, ['*'], 'activity_page');
    }

    /**
     * @return array{platforms: Collection, entityTypes: Collection, statuses: array<int, string>}
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
            'statuses' => array_map(fn (PlatformSyncStatus $status): string => $status->value, PlatformSyncStatus::cases()),
        ];
    }

    /**
     * @param  array<string, string>  $filters
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
