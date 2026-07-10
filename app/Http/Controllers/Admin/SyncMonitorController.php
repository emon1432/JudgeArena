<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PlatformSyncEntityType;
use App\Enums\PlatformSyncStatus;
use App\Http\Controllers\Controller;
use App\Models\PlatformSyncState;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncMonitorController extends Controller
{
    public function __construct(
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {}

    public function index(Request $request)
    {
        $filters = $this->filters($request);

        if ($this->hasFilters($filters)) {
            app(ApplicationLogger::class)->info('Admin sync monitor filters applied', [
                'category' => 'admin',
                'source' => self::class,
                'platform' => $filters['platform'] ?: null,
                'entity_type' => $filters['entity_type'] ?: null,
                'status' => $filters['status'] ?: null,
            ]);
        }

        $summary = $this->summary($filters);
        $platformBreakdown = $this->platformBreakdown($filters);
        $recentFailures = $this->recentFailures($filters);
        $recentActivity = $this->recentActivity($filters);
        $filterOptions = $this->filterOptions();
        $entityLabels = $this->entityLabels();

        return view('admin.pages.sync-monitor.index', compact(
            'summary',
            'platformBreakdown',
            'recentFailures',
            'recentActivity',
            'filterOptions',
            'entityLabels',
            'filters'
        ));
    }

    public function retry(PlatformSyncState $syncState): RedirectResponse
    {
        if ($syncState->sync_status !== PlatformSyncStatus::Failed) {
            return redirect()
                ->route('sync-monitor.index')
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
            'entity_type' => $this->enumValue($syncState->entity_type),
            'entity_id' => $syncState->entity_platform_id,
            'sync_state_id' => $syncState->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('sync-monitor.index')
            ->with('success', __('Sync state reset for retry.'));
    }

    /**
     * @return array{platform: string, entity_type: string, status: string}
     */
    private function filters(Request $request): array
    {
        return [
            'platform' => $this->filterInput($request, 'platform'),
            'entity_type' => $this->filterInput($request, 'entity_type'),
            'status' => $this->filterInput($request, 'status'),
        ];
    }

    private function hasFilters(array $filters): bool
    {
        return $filters['platform'] !== ''
            || $filters['entity_type'] !== ''
            || $filters['status'] !== '';
    }

    /**
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

    private function recentFailures(array $filters)
    {
        return $this->filteredQuery(array_merge($filters, [
            'status' => PlatformSyncStatus::Failed->value,
        ]))
            ->with('platform')
            ->latest('updated_at')
            ->paginate(10, ['*'], 'failures_page')
            ->withQueryString();
    }

    private function recentActivity(array $filters)
    {
        return $this->filteredQuery($filters)
            ->with('platform')
            ->latest('updated_at')
            ->paginate(10, ['*'], 'activity_page')
            ->withQueryString();
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

    private function filteredQuery(array $filters): Builder
    {
        $query = PlatformSyncState::query();

        if ($filters['platform'] !== '') {
            $query->whereHas('platform', function (Builder $platformQuery) use ($filters): void {
                $platformQuery->where('slug', $filters['platform']);
            });
        }

        if ($filters['entity_type'] !== '') {
            $query->where('entity_type', $filters['entity_type']);
        }

        if ($filters['status'] !== '') {
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
            PlatformSyncEntityType::ContestSubmissions->value => 'Submission syncs',
            PlatformSyncEntityType::ContestStandings->value => 'Standing syncs',
            PlatformSyncEntityType::Problem->value => 'Problem syncs',
            PlatformSyncEntityType::User->value => 'User syncs',
            PlatformSyncEntityType::Submission->value => 'Submission syncs',
            PlatformSyncEntityType::RatingChange->value => 'Rating change syncs',
            PlatformSyncEntityType::UserRatingHistory->value => 'User rating history syncs',
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
            PlatformSyncEntityType::RatingChange->value,
            PlatformSyncEntityType::ContestStandings->value,
            PlatformSyncEntityType::ContestSubmissions->value,
        ];
    }

    private function retryCount(PlatformSyncState $state): string
    {
        $metadata = is_array($state->metadata) ? $state->metadata : [];
        $retryCount = $metadata['retry_count'] ?? $metadata['attempt_count'] ?? null;

        return $retryCount === null ? 'N/A' : (string) $retryCount;
    }

    private function filterInput(Request $request, string $key): string
    {
        $value = $request->input($key, '');

        if (is_array($value)) {
            $value = $value[0] ?? '';
        }

        return trim((string) $value);
    }

    private function enumValue(mixed $value): string
    {
        return $value instanceof \BackedEnum ? (string) $value->value : (string) $value;
    }
}
