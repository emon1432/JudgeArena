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
        return $metadata['retry_count'] ?? ($metadata['attempt_count'] ?? '1');
    };
@endphp

<div @if($autoRefresh) wire:poll.keep-alive.{{ $isSyncing ? '2s' : '5s' }} @endif>
    {{-- Scoped Styles for Modern Pulse, Segmented Progress, & Timeline --}}
    <style>
        .pulse-indicator {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            position: relative;
        }
        .pulse-indicator::after {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            border-radius: 50%;
            animation: pulse-ring 1.8s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }
        .pulse-success { background-color: #28c76f; }
        .pulse-success::after { border: 2px solid #28c76f; }
        .pulse-info { background-color: #00cfe8; }
        .pulse-info::after { border: 2px solid #00cfe8; }
        .pulse-warning { background-color: #ff9f43; }
        .pulse-warning::after { border: 2px solid #ff9f43; }
        .pulse-secondary { background-color: #a8aaae; }
        .pulse-secondary::after { border: 2px solid #a8aaae; }

        @keyframes pulse-ring {
            0% { transform: scale(0.6); opacity: 0.9; }
            80%, 100% { transform: scale(2.2); opacity: 0; }
        }

        .segmented-progress {
            height: 8px;
            border-radius: 6px;
            overflow: hidden;
            background-color: rgba(168, 170, 174, 0.2);
            display: flex;
        }
        .segmented-progress .segment {
            height: 100%;
            transition: width 0.4s ease;
        }

        .activity-timeline {
            position: relative;
            padding-left: 28px;
            list-style: none;
            margin-bottom: 0;
        }
        .activity-timeline::before {
            content: '';
            position: absolute;
            top: 6px;
            bottom: 6px;
            left: 11px;
            width: 2px;
            background-color: rgba(168, 170, 174, 0.25);
        }
        .activity-timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        .activity-timeline-item:last-child {
            padding-bottom: 0;
        }
        .activity-timeline-point {
            position: absolute;
            left: -28px;
            top: 4px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bs-body-bg, #fff);
            border: 2px solid #a8aaae;
            box-shadow: 0 0 0 2px var(--bs-body-bg, #fff);
            z-index: 1;
        }
        .activity-timeline-point.point-success { border-color: #28c76f; color: #28c76f; }
        .activity-timeline-point.point-info { border-color: #00cfe8; color: #00cfe8; }
        .activity-timeline-point.point-warning { border-color: #ff9f43; color: #ff9f43; }
        .activity-timeline-point.point-danger { border-color: #ea5455; color: #ea5455; }
        .activity-timeline-point i { font-size: 12px; }

        .diagnostics-code-block {
            background-color: rgba(234, 84, 85, 0.06);
            border: 1px solid rgba(234, 84, 85, 0.2);
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.8rem;
            color: #ea5455;
            word-break: break-word;
        }

        .entity-flex-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .entity-flex-item {
            flex: 1 1 100%;
            min-width: 0;
        }
        @media (min-width: 768px) {
            .entity-flex-item {
                flex: 1 1 calc(50% - 1rem);
            }
        }
        @media (min-width: 1200px) {
            .entity-flex-item {
                flex: 1 1 calc(33.333% - 1rem);
            }
        }

        .entity-tile {
            background-color: var(--bs-card-bg, #fff);
            border: 1px solid rgba(168, 170, 174, 0.2);
            border-radius: 12px;
            padding: 1.15rem;
            transition: all 0.25s ease-in-out;
            position: relative;
            height: 100%;
        }
        .entity-tile:hover {
            transform: translateY(-2px);
            border-color: rgba(115, 103, 240, 0.45);
            box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.08);
        }
        .entity-tile .entity-icon-box {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>

    {{-- Command & Real-time Live Control Header --}}
    <div class="card mb-4 shadow-sm border-0 bg-primary-subtle">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-lg">
                        <span class="avatar-initial rounded-3 bg-primary text-white shadow">
                            <i class="icon-base ti tabler-activity-heartbeat icon-lg"></i>
                        </span>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="mb-0 text-heading fw-bold">{{ __('Sync Monitor') }}</h4>
                            @if($autoRefresh)
                                @if($isSyncing)
                                    <span class="badge bg-label-info d-flex align-items-center gap-2 px-2 py-1">
                                        <span class="pulse-indicator pulse-info"></span>
                                        <span class="fw-semibold">{{ __('Syncing (2s)') }}</span>
                                    </span>
                                @else
                                    <span class="badge bg-label-success d-flex align-items-center gap-2 px-2 py-1">
                                        <span class="pulse-indicator pulse-success"></span>
                                        <span class="fw-semibold">{{ __('Live (5s)') }}</span>
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-label-secondary d-flex align-items-center gap-2 px-2 py-1">
                                    <span class="pulse-indicator pulse-secondary"></span>
                                    <span class="fw-semibold">{{ __('Paused') }}</span>
                                </span>
                            @endif
                        </div>
                        <p class="text-muted small mb-0 mt-1">
                            {{ __('Real-time platform synchronization, schedule tracker & error diagnostics') }}
                            <span class="ms-2 text-muted opacity-75">| {{ __('Updated:') }} <span class="fw-semibold">{{ $lastRefreshedAt }}</span></span>
                        </p>
                    </div>
                </div>

                {{-- Action Toolbar --}}
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @if(($summary['syncing'] ?? 0) > 0)
                        <button type="button" 
                                wire:click="resetStuckSyncs" 
                                wire:loading.attr="disabled"
                                wire:confirm="{{ __('Are you sure you want to reset all stuck syncing states back to Pending?') }}"
                                class="btn btn-sm btn-warning shadow-sm"
                                title="{{ __('Reset all stuck syncing states back to Pending') }}">
                            <i class="icon-base ti tabler-rotate-clockwise me-1"></i>
                            {{ __('Reset Stuck (:count)', ['count' => $summary['syncing']]) }}
                        </button>
                    @endif

                    @if(($summary['failed'] ?? 0) > 0)
                        <button type="button" 
                                wire:click="retryAllFailures" 
                                wire:loading.attr="disabled"
                                wire:confirm="{{ __('Are you sure you want to reset all failed states back to Pending for retry?') }}"
                                class="btn btn-sm btn-danger shadow-sm"
                                title="{{ __('Retry all failed sync states') }}">
                            <i class="icon-base ti tabler-refresh-alert me-1"></i>
                            {{ __('Retry All Failed (:count)', ['count' => $summary['failed']]) }}
                        </button>
                    @endif

                    <button type="button" 
                            wire:click="toggleAutoRefresh" 
                            class="btn btn-sm {{ $autoRefresh ? 'btn-label-primary' : 'btn-primary' }} shadow-sm"
                            title="{{ $autoRefresh ? __('Pause live updates') : __('Resume live updates') }}">
                        <i class="icon-base ti {{ $autoRefresh ? 'tabler-player-pause' : 'tabler-player-play' }} me-1"></i>
                        {{ $autoRefresh ? __('Pause Live') : __('Resume Live') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Feedback Alert Message --}}
    @if ($feedbackMessage)
        <div class="alert alert-{{ $feedbackType ?? 'info' }} alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="icon-base ti {{ $feedbackType === 'success' ? 'tabler-circle-check' : ($feedbackType === 'danger' ? 'tabler-alert-circle' : 'tabler-info-circle') }} me-2 icon-md"></i>
                <div>{{ $feedbackMessage }}</div>
            </div>
            <button type="button" class="btn-close" wire:click="$set('feedbackMessage', null)" aria-label="Close"></button>
        </div>
    @endif

    {{-- Hero Health & Metric Cards --}}
    <div class="row g-3 mb-4">
        {{-- Health Score Card --}}
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">{{ __('Health Score') }}</span>
                        <span class="badge bg-label-{{ $summary['health_class'] }} rounded-pill font-monospace">
                            {{ $summary['health_label'] }}
                        </span>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-{{ $summary['health_class'] }}">
                            {{ $summary['health_score'] }}<small class="fs-6">%</small>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-{{ $summary['health_class'] }}" role="progressbar" style="width: {{ $summary['health_score'] }}%" aria-valuenow="{{ $summary['health_score'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Sync States Card --}}
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">{{ __('Total States') }}</span>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded-2 bg-label-primary">
                                <i class="icon-base ti tabler-database icon-xs"></i>
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-heading">{{ number_format($summary['total']) }}</div>
                        <div class="text-muted small mt-1">
                            <span class="text-primary fw-semibold">{{ count($filterOptions['platforms']) }}</span> {{ __('Platforms Active') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Synced (Success) Card --}}
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">{{ __('Synced') }}</span>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded-2 bg-label-success">
                                <i class="icon-base ti tabler-check icon-xs"></i>
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-success">{{ number_format($summary['synced']) }}</div>
                        <div class="d-flex align-items-center justify-content-between text-muted small mt-1">
                            <span>{{ $summary['synced_pct'] }}% {{ __('of total') }}</span>
                        </div>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: {{ $summary['synced_pct'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Syncing (In Progress) Card --}}
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">{{ __('Syncing') }}</span>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded-2 bg-label-info">
                                <i class="icon-base ti tabler-refresh icon-xs {{ $summary['syncing'] > 0 ? 'icon-spin' : '' }}"></i>
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-info">
                            @if($summary['syncing'] > 0)
                                <span class="spinner-border spinner-border-sm me-1 text-info align-middle" role="status"></span>
                            @endif
                            {{ number_format($summary['syncing']) }}
                        </div>
                        <div class="d-flex align-items-center justify-content-between text-muted small mt-1">
                            <span>{{ $summary['syncing'] > 0 ? __('In Progress') : __('Idle') }}</span>
                        </div>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar bg-info {{ $summary['syncing'] > 0 ? 'progress-bar-striped progress-bar-animated' : '' }}" style="width: {{ $summary['syncing_pct'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Failed Card --}}
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <div class="card h-100 shadow-sm border-0 {{ $summary['failed'] > 0 ? 'border-danger border-opacity-25' : '' }}">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">{{ __('Failed') }}</span>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded-2 bg-label-danger">
                                <i class="icon-base ti tabler-alert-triangle icon-xs"></i>
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-danger">{{ number_format($summary['failed']) }}</div>
                        <div class="d-flex align-items-center justify-content-between text-muted small mt-1">
                            <span>{{ $summary['failed_pct'] }}% {{ __('errors') }}</span>
                            @if($summary['failed'] > 0)
                                <a href="javascript:void(0)" wire:click="switchTab('failures')" class="text-danger small fw-semibold">{{ __('View') }} &rarr;</a>
                            @endif
                        </div>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar bg-danger" style="width: {{ $summary['failed_pct'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Card --}}
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">{{ __('Pending') }}</span>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded-2 bg-label-warning">
                                <i class="icon-base ti tabler-clock icon-xs"></i>
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-warning">{{ number_format($summary['pending']) }}</div>
                        <div class="d-flex align-items-center justify-content-between text-muted small mt-1">
                            <span>{{ $summary['pending_pct'] }}% {{ __('queued') }}</span>
                        </div>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar bg-warning" style="width: {{ $summary['pending_pct'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Interactive Filter Toolbar --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">{{ __('Platform') }}</label>
                    <select wire:model.live="platform" class="form-select form-select-sm">
                        <option value="">{{ __('All Platforms') }}</option>
                        @foreach ($filterOptions['platforms'] as $p)
                            <option value="{{ $p->slug }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">{{ __('Entity Type') }}</label>
                    <select wire:model.live="entity_type" class="form-select form-select-sm">
                        <option value="">{{ __('All Entity Types') }}</option>
                        @foreach ($filterOptions['entityTypes'] as $eType)
                            @php $val = $eType instanceof \BackedEnum ? $eType->value : (string) $eType; @endphp
                            <option value="{{ $val }}">
                                {{ $entityLabels[$val] ?? str($val)->replace('_', ' ')->title() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">{{ __('Status') }}</label>
                    <select wire:model.live="status" class="form-select form-select-sm">
                        <option value="">{{ __('All Statuses') }}</option>
                        @foreach ($filterOptions['statuses'] as $st)
                            <option value="{{ $st }}">{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end pt-3">
                    <button type="button" wire:click="resetFilters" class="btn btn-sm btn-label-secondary w-100">
                        <i class="icon-base ti tabler-rotate-clockwise me-1"></i>
                        {{ __('Reset Filters') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modern Navigation Tabs --}}
    <div class="nav-align-top mb-4">
        <ul class="nav nav-pills nav-fill gap-2" role="tablist">
            <li class="nav-item">
                <button type="button" 
                        wire:click="switchTab('overview')"
                        class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }} py-2 px-3 fw-semibold shadow-none">
                    <i class="icon-base ti tabler-layout-grid me-1"></i>
                    {{ __('Platform Breakdown') }}
                    <span class="badge rounded-pill bg-label-primary ms-1">{{ count($platformBreakdown) }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button type="button" 
                        wire:click="switchTab('failures')"
                        class="nav-link {{ $activeTab === 'failures' ? 'active' : '' }} py-2 px-3 fw-semibold shadow-none">
                    <i class="icon-base ti tabler-alert-triangle me-1"></i>
                    {{ __('Failures & Diagnostics') }}
                    @if($summary['failed'] > 0)
                        <span class="badge rounded-pill bg-danger ms-1">{{ $summary['failed'] }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button type="button" 
                        wire:click="switchTab('activity')"
                        class="nav-link {{ $activeTab === 'activity' ? 'active' : '' }} py-2 px-3 fw-semibold shadow-none">
                    <i class="icon-base ti tabler-timeline me-1"></i>
                    {{ __('Activity Stream') }}
                </button>
            </li>
        </ul>
    </div>

    {{-- TAB 1: Platform Overview & Interactive Service Grid --}}
    @if($activeTab === 'overview')
        <div class="row g-4">
            @forelse ($platformBreakdown as $platformData)
                @php
                    $activeEntities = $platformData['entities']->filter(function($item) {
                        return $item['total'] > 0 || (isset($item['job']) && $item['job'] && $item['job']['enabled']);
                    });
                    $inactiveEntities = $platformData['entities']->filter(function($item) {
                        return $item['total'] === 0 && (!isset($item['job']) || ! $item['job'] || ! $item['job']['enabled']);
                    });
                    if ($activeEntities->isEmpty()) {
                        $activeEntities = $platformData['entities'];
                        $inactiveEntities = collect();
                    }
                @endphp
                <div class="col-12" wire:key="platform-card-{{ $platformData['platform_slug'] }}">
                    <div class="card shadow-sm border-0">
                        {{-- Platform Card Header --}}
                        <div class="card-header border-bottom bg-light bg-opacity-50 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar avatar-md">
                                    <span class="avatar-initial rounded-3 bg-label-primary fw-bold fs-5 shadow-xs">
                                        {{ strtoupper(substr($platformData['platform_name'], 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="mb-0 text-heading fw-bold">{{ $platformData['platform_name'] }}</h5>
                                        <a href="{{ route('platforms.show', $platformData['platform_slug']) }}" target="_blank" rel="noopener noreferrer" class="text-muted small" title="{{ __('View platform details') }}">
                                            <i class="icon-base ti tabler-external-link icon-xs"></i>
                                        </a>
                                    </div>
                                    <div class="text-muted small">
                                        {{ __('Total Records:') }} <span class="fw-semibold text-heading">{{ number_format($platformData['total']) }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Platform Quick Health Summary --}}
                            <div class="d-flex align-items-center gap-3">
                                <div class="text-end d-none d-sm-block">
                                    <div class="small fw-semibold text-heading">{{ __('Sync Health') }}</div>
                                    <div class="small text-muted">{{ number_format($platformData['synced']) }}/{{ number_format($platformData['total']) }} {{ __('Synced') }}</div>
                                </div>
                                <span class="badge bg-label-{{ $platformData['health_score'] >= 90 ? 'success' : ($platformData['health_score'] >= 70 ? 'warning' : 'danger') }} fs-6 px-3 py-2">
                                    {{ $platformData['health_score'] }}%
                                </span>
                            </div>
                        </div>

                        {{-- Option 1: Interactive Service Grid Cards (Flexible Dynamic Grid) --}}
                        <div class="card-body p-4">
                            <div class="entity-flex-grid">
                                @foreach ($activeEntities as $eType => $counts)
                                    <div class="entity-flex-item" wire:key="tile-{{ $platformData['platform_slug'] }}-{{ $eType }}">
                                        <div class="entity-tile">
                                            {{-- Header of Tile --}}
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="entity-icon-box bg-label-primary text-primary">
                                                        <i class="icon-base ti {{ str_contains($eType, 'problem') ? 'tabler-code' : (str_contains($eType, 'user') ? 'tabler-user' : 'tabler-trophy') }} icon-md"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-heading" style="font-size: 0.95rem;">
                                                            {{ $entityLabels[$eType] ?? str($eType)->replace('_', ' ')->title() }}
                                                        </h6>
                                                        <small class="text-muted font-monospace" style="font-size: 0.78rem;">{{ number_format($counts['total']) }} {{ __('Records') }}</small>
                                                    </div>
                                                </div>

                                                {{-- Status & Countdown Pill --}}
                                                <div>
                                                    @if(isset($counts['job']) && $counts['job'])
                                                        @php $job = $counts['job']; @endphp
                                                        @if(! $job['enabled'])
                                                            <span class="badge bg-label-secondary" data-bs-toggle="tooltip" title="{{ __('Sync job is disabled') }}">
                                                                <i class="icon-base ti tabler-player-pause icon-xs me-1"></i> {{ __('Disabled') }}
                                                            </span>
                                                        @elseif($counts['syncing'] > 0)
                                                            <span class="badge bg-label-info" data-bs-toggle="tooltip" title="{{ __('Sync in progress') }} (Every {{ $job['interval_minutes'] }}m)">
                                                                <span class="spinner-border spinner-border-sm me-1" role="status"></span> {{ __('Running...') }}
                                                            </span>
                                                        @elseif($job['is_due'])
                                                            <span class="badge bg-label-warning text-dark" data-bs-toggle="tooltip" title="{{ __('Due to run in next cycle') }} (Every {{ $job['interval_minutes'] }}m)">
                                                                <i class="icon-base ti tabler-clock-play icon-xs me-1"></i> {{ __('Due now') }}
                                                            </span>
                                                        @elseif($job['next_run_timestamp'])
                                                            <span class="badge bg-label-primary countdown-badge font-monospace py-1 px-2"
                                                                  data-sync-countdown="{{ $job['next_run_timestamp'] }}"
                                                                  data-bs-toggle="tooltip"
                                                                  title="Every {{ $job['interval_minutes'] }}m{{ $job['last_success_human'] ? ' • Last: ' . $job['last_success_human'] : '' }}">
                                                                <i class="icon-base ti tabler-clock icon-xs me-1 countdown-icon"></i>
                                                                <span class="countdown-text">{{ $job['next_run_human'] }}</span>
                                                            </span>
                                                        @else
                                                            <span class="badge bg-label-primary">{{ __('Immediate') }}</span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-label-secondary">{{ __('Disabled') }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Metric & Completion Ratio --}}
                                            <div class="mb-2">
                                                <div class="d-flex justify-content-between align-items-baseline mb-1">
                                                    <div class="fs-5 fw-bold text-heading">
                                                        {{ number_format($counts['synced']) }} <span class="text-muted small fw-normal">/ {{ number_format($counts['total']) }} {{ __('Synced') }}</span>
                                                    </div>
                                                    <span class="fw-bold text-success small">{{ $counts['synced_pct'] }}%</span>
                                                </div>

                                                {{-- Segmented Visual Progress Bar --}}
                                                <div class="segmented-progress" style="height: 8px;"
                                                     data-bs-toggle="tooltip" 
                                                     data-bs-html="true"
                                                     title="<div class='text-start'><strong>{{ number_format($counts['total']) }} Total</strong><br><span class='text-success'>● Synced: {{ $counts['synced'] }} ({{ $counts['synced_pct'] }}%)</span><br><span class='text-info'>● Syncing: {{ $counts['syncing'] }} ({{ $counts['syncing_pct'] }}%)</span><br><span class='text-warning'>● Pending: {{ $counts['pending'] }} ({{ $counts['pending_pct'] }}%)</span><br><span class='text-danger'>● Failed: {{ $counts['failed'] }} ({{ $counts['failed_pct'] }}%)</span></div>">
                                                    <div class="segment bg-success" style="width: {{ $counts['synced_pct'] }}%"></div>
                                                    <div class="segment bg-info {{ $counts['syncing'] > 0 ? 'progress-bar-striped progress-bar-animated' : '' }}" style="width: {{ $counts['syncing_pct'] }}%"></div>
                                                    <div class="segment bg-warning" style="width: {{ $counts['pending_pct'] }}%"></div>
                                                    <div class="segment bg-danger" style="width: {{ $counts['failed_pct'] }}%"></div>
                                                </div>
                                            </div>

                                            {{-- Micro Badges --}}
                                            <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top border-light-subtle">
                                                <span class="badge bg-label-success px-2 py-1">
                                                    <i class="icon-base ti tabler-check icon-xs me-1"></i> {{ __('Synced') }}: {{ number_format($counts['synced']) }}
                                                </span>
                                                <span class="badge bg-label-warning px-2 py-1">
                                                    <i class="icon-base ti tabler-clock icon-xs me-1"></i> {{ __('Pending') }}: {{ number_format($counts['pending']) }}
                                                </span>
                                                <span class="badge {{ $counts['failed'] > 0 ? 'bg-danger text-white fw-bold' : 'bg-label-danger' }} px-2 py-1">
                                                    <i class="icon-base ti tabler-alert-triangle icon-xs me-1"></i> {{ __('Failed') }}: {{ number_format($counts['failed']) }}
                                                </span>
                                                @if($counts['syncing'] > 0)
                                                    <span class="badge bg-label-info px-2 py-1">
                                                        <span class="spinner-border spinner-border-sm me-1 text-info" role="status"></span>
                                                        {{ __('Syncing') }}: {{ number_format($counts['syncing']) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Inactive / Zero-Data Footer Row --}}
                            @if($inactiveEntities->isNotEmpty())
                                <div class="mt-4 pt-3 border-top d-flex flex-wrap align-items-center gap-2">
                                    <span class="text-muted small fw-semibold">
                                        <i class="icon-base ti tabler-folder-off me-1"></i>
                                        {{ __('Inactive Syncs (0 records):') }}
                                    </span>
                                    @foreach ($inactiveEntities as $inType => $inCounts)
                                        <span class="badge bg-label-secondary font-monospace py-1 px-2" data-bs-toggle="tooltip" title="{{ __('No records found & sync disabled') }}">
                                            {{ $entityLabels[$inType] ?? str($inType)->replace('_', ' ')->title() }} (0)
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card shadow-sm border-0 py-5 text-center">
                        <div class="card-body">
                            <i class="icon-base ti tabler-database-off icon-xl text-muted mb-3 d-block" style="font-size: 48px;"></i>
                            <h5 class="text-muted">{{ __('No platform sync states match the selected filters.') }}</h5>
                            <button type="button" wire:click="resetFilters" class="btn btn-sm btn-outline-primary mt-2">
                                {{ __('Clear Filters') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    @endif

    {{-- TAB 2: Failures & Error Diagnostics --}}
    @if($activeTab === 'failures')
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-title mb-0 text-danger fw-bold">
                        <i class="icon-base ti tabler-alert-triangle me-1"></i>
                        {{ __('Sync Failures & Diagnostics') }}
                    </h5>
                    <span class="badge bg-danger rounded-pill">{{ $recentFailures->total() }}</span>
                </div>
                @if($recentFailures->total() > 0)
                    <button type="button" 
                            wire:click="retryAllFailures" 
                            wire:loading.attr="disabled"
                            wire:confirm="{{ __('Are you sure you want to reset all failed states back to Pending for retry?') }}"
                            class="btn btn-sm btn-danger shadow-sm">
                        <i class="icon-base ti tabler-refresh-alert me-1"></i>
                        {{ __('Retry All (:count)', ['count' => $recentFailures->total()]) }}
                    </button>
                @endif
            </div>
            <div class="card-body p-0">
                @if($recentFailures->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Platform') }}</th>
                                    <th>{{ __('Entity Type') }}</th>
                                    <th>{{ __('Identifier') }}</th>
                                    <th style="min-width: 320px;">{{ __('Error Diagnostics') }}</th>
                                    <th class="text-center">{{ __('Attempt') }}</th>
                                    <th>{{ __('Failed At') }}</th>
                                    <th class="text-center">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentFailures as $state)
                                    @php
                                        $eType = $stateValue($state->entity_type);
                                    @endphp
                                    <tr wire:key="diag-failure-{{ $state->id }}">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="avatar avatar-xs">
                                                    <span class="avatar-initial rounded bg-label-primary fw-bold" style="font-size: 10px;">
                                                        {{ strtoupper(substr($state->platform?->name ?? 'P', 0, 2)) }}
                                                    </span>
                                                </span>
                                                <span class="fw-semibold">{{ $state->platform?->name ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-secondary">
                                                {{ $entityLabels[$eType] ?? str($eType)->replace('_', ' ')->title() }}
                                            </span>
                                        </td>
                                        <td>
                                            <code class="fw-bold text-dark bg-light px-2 py-1 rounded">
                                                {{ $state->entity_platform_id ?? '-' }}
                                            </code>
                                        </td>
                                        <td>
                                            <div class="diagnostics-code-block">
                                                <i class="icon-base ti tabler-bug icon-xs me-1"></i>
                                                {{ $state->last_error ?? __('Unknown synchronization exception occurred.') }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-label-warning rounded-pill">
                                                #{{ $retryCount($state) }}
                                            </span>
                                        </td>
                                        <td class="text-muted small">
                                            <div>{{ $state->updated_at?->diffForHumans() }}</div>
                                            <div class="text-muted opacity-75" style="font-size: 0.72rem;">{{ $state->updated_at?->format('d M, Y h:i A') }}</div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                    wire:click="retry({{ $state->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="btn btn-sm btn-label-warning shadow-none">
                                                <i class="icon-base ti tabler-rotate-clockwise me-1"></i>
                                                {{ __('Retry') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 border-top">
                        {{ $recentFailures->links('livewire::bootstrap') }}
                    </div>
                @else
                    <div class="py-5 text-center">
                        <div class="avatar avatar-xl bg-label-success mx-auto mb-3">
                            <i class="icon-base ti tabler-shield-check icon-lg" style="font-size: 32px;"></i>
                        </div>
                        <h5 class="text-heading fw-bold mb-1">{{ __('All Systems Operational') }}</h5>
                        <p class="text-muted mb-0">{{ __('No failed sync states found matching current filters.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- TAB 3: Real-time Activity Stream Timeline --}}
    @if($activeTab === 'activity')
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-title mb-0 text-primary fw-bold">
                        <i class="icon-base ti tabler-activity me-1"></i>
                        {{ __('Live Activity Stream') }}
                    </h5>
                    <span class="badge bg-label-primary rounded-pill">{{ $recentActivity->total() }}</span>
                </div>
            </div>
            <div class="card-body p-4">
                @if($recentActivity->count() > 0)
                    <ul class="activity-timeline">
                        @foreach ($recentActivity as $state)
                            @php
                                $eType = $stateValue($state->entity_type);
                                $stValue = $stateValue($state->sync_status);
                                $pointClass = match($stValue) {
                                    'synced' => 'point-success',
                                    'syncing' => 'point-info',
                                    'failed' => 'point-danger',
                                    default => 'point-warning',
                                };
                                $pointIcon = match($stValue) {
                                    'synced' => 'tabler-check',
                                    'syncing' => 'tabler-refresh',
                                    'failed' => 'tabler-x',
                                    default => 'tabler-clock',
                                };
                            @endphp
                            <li class="activity-timeline-item" wire:key="activity-item-{{ $state->id }}">
                                <div class="activity-timeline-point {{ $pointClass }}">
                                    <i class="icon-base ti {{ $pointIcon }}"></i>
                                </div>
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold text-heading">{{ $state->platform?->name ?? 'Platform' }}</span>
                                        <span class="text-muted">•</span>
                                        <span class="badge bg-label-primary">
                                            {{ $entityLabels[$eType] ?? str($eType)->replace('_', ' ')->title() }}
                                        </span>
                                        @if($state->entity_platform_id)
                                            <code class="bg-light px-2 py-0 rounded text-dark small">#{{ $state->entity_platform_id }}</code>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-label-{{ $statusColors[$stValue] ?? 'secondary' }} text-uppercase font-monospace">
                                            @if($stValue === 'syncing')
                                                <span class="spinner-border spinner-border-sm me-1 text-info align-middle" role="status"></span>
                                            @endif
                                            {{ $stValue }}
                                        </span>
                                        <span class="text-muted small">{{ $state->updated_at?->diffForHumans() }}</span>
                                    </div>
                                </div>
                                @if($stValue === 'failed' && $state->last_error)
                                    <div class="diagnostics-code-block mt-2">
                                        <i class="icon-base ti tabler-alert-circle icon-xs me-1"></i>
                                        {{ str($state->last_error)->limit(150) }}
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-4 pt-3 border-top">
                        {{ $recentActivity->links('livewire::bootstrap') }}
                    </div>
                @else
                    <div class="py-5 text-center text-muted">
                        <i class="icon-base ti tabler-timeline-event-text icon-xl mb-3 d-block" style="font-size: 40px;"></i>
                        <p class="mb-0">{{ __('No recent synchronization activity recorded.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

@script
<script>
    function updateSyncCountdowns() {
        const now = Math.floor(Date.now() / 1000);
        document.querySelectorAll('[data-sync-countdown]').forEach(el => {
            const target = parseInt(el.getAttribute('data-sync-countdown'), 10);
            if (isNaN(target)) return;

            const diff = target - now;
            const textEl = el.querySelector('.countdown-text');
            const iconEl = el.querySelector('.countdown-icon');

            if (diff <= 0) {
                el.className = 'badge bg-label-warning text-dark countdown-badge font-monospace';
                if (iconEl) iconEl.className = 'icon-base ti tabler-clock-play icon-xs me-1 countdown-icon';
                if (textEl) textEl.textContent = '{{ __('Due now') }}';
            } else {
                el.className = 'badge bg-label-primary countdown-badge font-monospace';
                if (iconEl) iconEl.className = 'icon-base ti tabler-clock icon-xs me-1 countdown-icon';

                const m = Math.floor(diff / 60);
                const s = diff % 60;
                let formatted = '';

                if (m >= 60) {
                    const h = Math.floor(m / 60);
                    const remM = m % 60;
                    formatted = `${h}h ${remM < 10 ? '0' : ''}${remM}m ${s < 10 ? '0' : ''}${s}s`;
                } else {
                    formatted = `${m < 10 ? '0' : ''}${m}m ${s < 10 ? '0' : ''}${s}s`;
                }

                if (textEl) textEl.textContent = formatted;
            }
        });
    }

    if (!window.syncCountdownInterval) {
        window.syncCountdownInterval = setInterval(updateSyncCountdowns, 1000);
    }
    updateSyncCountdowns();
</script>
@endscript
