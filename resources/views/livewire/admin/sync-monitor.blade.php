@php
    $statusColors = [
        'pending' => 'warning',
        'syncing' => 'info',
        'synced' => 'success',
        'failed' => 'danger',
    ];

    $stateValue = function ($value) {
        return $value instanceof \BackedEnum ? $value->value : (string) $value;
    };

    $retryCount = function ($state) {
        $metadata = is_array($state->metadata) ? $state->metadata : [];
        return $metadata['retry_count'] ?? ($metadata['attempt_count'] ?? 'N/A');
    };
@endphp

<div @if($autoRefresh) wire:poll.keep-alive.{{ $isSyncing ? '2s' : '5s' }} @endif>
    {{-- Header & Real-time Live Controls --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 text-primary fw-bold">{{ __('Sync Monitor') }}</h4>
            <p class="text-muted mb-0">{{ __('Real-time background platform synchronization status & monitoring') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($autoRefresh)
                @if($isSyncing)
                    <span class="badge bg-label-info d-flex align-items-center gap-1 py-2 px-3">
                        <span class="spinner-border spinner-border-sm text-info" role="status" aria-hidden="true"></span>
                        <span class="fw-semibold">{{ __('Sync In Progress (Polling 2s)') }}</span>
                    </span>
                @else
                    <span class="badge bg-label-success d-flex align-items-center gap-1 py-2 px-3">
                        <span class="badge badge-dot bg-success me-1"></span>
                        <span class="fw-semibold">{{ __('Live (Polling 5s)') }}</span>
                    </span>
                @endif
            @else
                <span class="badge bg-label-secondary py-2 px-3">
                    <span class="badge badge-dot bg-secondary me-1"></span>
                    <span class="fw-semibold">{{ __('Auto-refresh Paused') }}</span>
                </span>
            @endif

            <button type="button" 
                    wire:click="toggleAutoRefresh" 
                    class="btn btn-sm {{ $autoRefresh ? 'btn-outline-primary' : 'btn-primary' }}"
                    title="{{ $autoRefresh ? __('Pause live updates') : __('Resume live updates') }}">
                <i class="icon-base ti {{ $autoRefresh ? 'tabler-player-pause' : 'tabler-player-play' }} me-1"></i>
                {{ $autoRefresh ? __('Pause') : __('Resume') }}
            </button>
        </div>
    </div>

    {{-- Feedback Message --}}
    @if ($feedbackMessage)
        <div class="alert alert-{{ $feedbackType ?? 'info' }} alert-dismissible fade show" role="alert">
            {{ $feedbackMessage }}
            <button type="button" class="btn-close" wire:click="$set('feedbackMessage', null)" aria-label="Close"></button>
        </div>
    @endif

    {{-- Top Summary Stats Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small mb-1">{{ __('Total Sync States') }}</div>
                            <div class="fs-3 fw-bold text-heading">{{ number_format($summary['total']) }}</div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="icon-base ti tabler-database icon-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @foreach (['pending', 'syncing', 'synced', 'failed'] as $statusName)
            <div class="col-md-6 col-xl">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small mb-1">{{ __(ucfirst($statusName)) }}</div>
                                <div class="fs-3 fw-bold text-{{ $statusColors[$statusName] }}">
                                    {{ number_format($summary[$statusName]) }}
                                </div>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-{{ $statusColors[$statusName] }}">
                                    @if($statusName === 'pending')
                                        <i class="icon-base ti tabler-clock icon-md"></i>
                                    @elseif($statusName === 'syncing')
                                        <i class="icon-base ti tabler-refresh icon-md"></i>
                                    @elseif($statusName === 'synced')
                                        <i class="icon-base ti tabler-check icon-md"></i>
                                    @elseif($statusName === 'failed')
                                        <i class="icon-base ti tabler-alert-triangle icon-md"></i>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filters Card --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">{{ __('Filters') }}</h5>
        </div>
        <div class="card-body mt-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">{{ __('Platform') }}</label>
                    <select wire:model.live="platform" class="form-select">
                        <option value="">{{ __('All Platforms') }}</option>
                        @foreach ($filterOptions['platforms'] as $p)
                            <option value="{{ $p->slug }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Entity Type') }}</label>
                    <select wire:model.live="entity_type" class="form-select">
                        <option value="">{{ __('All Entity Types') }}</option>
                        @foreach ($filterOptions['entityTypes'] as $eType)
                            @php $val = $eType instanceof \BackedEnum ? $eType->value : (string) $eType; @endphp
                            <option value="{{ $val }}">
                                {{ $entityLabels[$val] ?? str($val)->replace('_', ' ')->title() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select wire:model.live="status" class="form-select">
                        <option value="">{{ __('All Statuses') }}</option>
                        @foreach ($filterOptions['statuses'] as $st)
                            <option value="{{ $st }}">{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2 mt-3">
                    <button type="button" wire:click="resetFilters" class="btn btn-label-secondary">
                        <i class="icon-base ti tabler-rotate-clockwise me-1"></i>
                        {{ __('Reset Filters') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Platform Breakdown Card --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Platform Breakdown') }}</h5>
            <span class="badge bg-label-primary">{{ count($platformBreakdown) }} {{ __('Platforms') }}</span>
        </div>
        <div class="card-body">
            @forelse ($platformBreakdown as $platformData)
                <div class="py-4 border-bottom">
                    <h6 class="mb-3 text-primary fw-bold">{{ $platformData['platform_name'] }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Entity') }}</th>
                                    <th>{{ __('Total') }}</th>
                                    <th>{{ __('Synced') }}</th>
                                    <th>{{ __('Failed') }}</th>
                                    <th>{{ __('Pending') }}</th>
                                    <th>{{ __('Syncing') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($platformData['entities'] as $eType => $counts)
                                    <tr>
                                        <td class="fw-semibold">
                                            {{ $entityLabels[$eType] ?? str($eType)->replace('_', ' ')->title() }}
                                        </td>
                                        <td>{{ number_format($counts['total']) }}</td>
                                        <td class="text-success fw-bold">{{ number_format($counts['synced']) }}</td>
                                        <td class="{{ $counts['failed'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                            {{ number_format($counts['failed']) }}
                                        </td>
                                        <td class="text-warning">{{ number_format($counts['pending']) }}</td>
                                        <td class="text-info fw-bold">
                                            @if($counts['syncing'] > 0)
                                                <span class="spinner-border spinner-border-sm me-1 text-info" role="status"></span>
                                            @endif
                                            {{ number_format($counts['syncing']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <p class="text-muted my-5 text-center">{{ __('No sync state records found.') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Recent Failures Card --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 text-danger">
                <i class="icon-base ti tabler-alert-triangle me-1"></i>
                {{ __('Recent Failures') }}
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Platform') }}</th>
                            <th>{{ __('Entity Type') }}</th>
                            <th>{{ __('Entity Key') }}</th>
                            <th>{{ __('Error Message') }}</th>
                            <th>{{ __('Retry Count') }}</th>
                            <th>{{ __('Updated At') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentFailures as $state)
                            @php
                                $eType = $stateValue($state->entity_type);
                            @endphp
                            <tr wire:key="failure-{{ $state->id }}">
                                <td>{{ $state->platform?->name ?? '-' }}</td>
                                <td>{{ $entityLabels[$eType] ?? str($eType)->replace('_', ' ')->title() }}</td>
                                <td class="fw-semibold"><code>{{ $state->entity_platform_id ?? '-' }}</code></td>
                                <td class="text-danger small">{{ str($state->last_error ?? '-')->limit(120) }}</td>
                                <td>{{ $retryCount($state) }}</td>
                                <td class="text-muted small">{{ $state->updated_at?->format('d M, Y h:i A') }}</td>
                                <td>
                                    <button type="button"
                                            wire:click="retry({{ $state->id }})"
                                            wire:loading.attr="disabled"
                                            class="btn btn-sm btn-label-warning">
                                        <i class="icon-base ti tabler-rotate-clockwise me-1"></i>
                                        {{ __('Retry Sync') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    {{ __('No failed sync states found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="row mt-4 justify-content-between">
                {{ $recentFailures->links('livewire::bootstrap') }}
            </div>
        </div>
    </div>

    {{-- Recent Activity Card --}}
    <div class="card shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="icon-base ti tabler-activity me-1 text-primary"></i>
                {{ __('Recent Activity') }}
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Platform') }}</th>
                            <th>{{ __('Entity Type') }}</th>
                            <th>{{ __('Entity Key') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Updated At') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentActivity as $state)
                            @php
                                $eType = $stateValue($state->entity_type);
                                $stValue = $stateValue($state->sync_status);
                            @endphp
                            <tr wire:key="activity-{{ $state->id }}">
                                <td>{{ $state->platform?->name ?? '-' }}</td>
                                <td>{{ $entityLabels[$eType] ?? str($eType)->replace('_', ' ')->title() }}</td>
                                <td class="fw-semibold"><code>{{ $state->entity_platform_id ?? '-' }}</code></td>
                                <td>
                                    <span class="badge bg-label-{{ $statusColors[$stValue] ?? 'secondary' }} text-uppercase">
                                        @if($stValue === 'syncing')
                                            <span class="spinner-border spinner-border-sm me-1 text-info" role="status"></span>
                                        @endif
                                        {{ $stValue }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $state->updated_at?->format('d M, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    {{ __('No sync activity found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="row mt-4 justify-content-between">
                {{ $recentActivity->links('livewire::bootstrap') }}
            </div>
        </div>
    </div>
</div>
