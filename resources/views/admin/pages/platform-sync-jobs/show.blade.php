@extends('admin.layouts.app')
@section('title', __('Platform Sync Job Details'))
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        {{ $platformSyncJob->platform->name }} -
                        {{ ucfirst(str_replace('_', ' ', $platformSyncJob->entity->value)) }}
                    </h5>
                    <a class="btn add-new btn-primary" href="{{ route('admin.platform-sync-jobs.index') }}">
                        <span class="d-flex align-items-center gap-2 text-white">
                            <i class="icon-base ti tabler-arrow-back-up icon-xs"></i>
                            {{ __('Back to Sync Jobs') }}
                        </span>
                    </a>
                </div>

                <div class="card-body mt-4">
                    <div class="row g-4">
                        <div class="col-xl-6 col-lg-6 col-md-12">
                            <div class="card bg-label-secondary shadow-none mb-4 h-100">
                                <div class="card-header border-bottom">
                                    <h6 class="mb-0 text-primary"><i class="ti tabler-settings me-1"></i>
                                        {{ __('Configuration') }}</h6>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            <span class="fw-semibold">{{ __('Platform') }}</span>
                                            <div>
                                                <x-platform-info :platform="$platformSyncJob->platform" />
                                            </div>
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            <span class="fw-semibold">{{ __('Entity') }}</span>
                                            <span
                                                class="fw-medium text-heading">{{ ucfirst(str_replace('_', ' ', $platformSyncJob->entity->value)) }}</span>
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            <span class="fw-semibold">{{ __('Status') }}</span>
                                            @if ($platformSyncJob->enabled)
                                                <span class="badge bg-label-success">{{ __('Enabled') }}</span>
                                            @else
                                                <span class="badge bg-label-secondary">{{ __('Disabled') }}</span>
                                            @endif
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            <span class="fw-semibold">{{ __('Priority') }}</span>
                                            <span class="badge bg-label-primary">{{ $platformSyncJob->priority }}</span>
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            <span class="fw-semibold">{{ __('Interval') }}</span>
                                            <span class="fw-medium text-heading">{{ $platformSyncJob->interval_minutes }}
                                                {{ __('Minutes') }}</span>
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
                                            <span class="fw-semibold">{{ __('Next Scheduled Run') }}</span>
                                            @php $next = $platformSyncJob->nextRunAt(); @endphp
                                            <span
                                                class="fw-medium text-heading">{{ $next ? $next->format('d M Y, h:i A') . ' (' . $next->diffForHumans() . ')' : __('Immediate') }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-lg-6 col-md-12">
                            <div class="card bg-label-secondary shadow-none mb-4 h-100">
                                <div class="card-header border-bottom">
                                    <h6 class="mb-0 text-primary"><i class="ti tabler-history me-1"></i>
                                        {{ __('Execution History') }}</h6>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            <span class="fw-semibold">{{ __('Last Started') }}</span>
                                            <span
                                                class="fw-medium text-heading">{{ $platformSyncJob->last_started_at ? $platformSyncJob->last_started_at->format('d M Y, h:i:s A') : __('Never') }}</span>
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            <span class="fw-semibold">{{ __('Last Finished') }}</span>
                                            <span
                                                class="fw-medium text-heading">{{ $platformSyncJob->last_finished_at ? $platformSyncJob->last_finished_at->format('d M Y, h:i:s A') : __('Never') }}</span>
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            <span class="fw-semibold">{{ __('Last Success') }}</span>
                                            <span
                                                class="fw-medium text-success">{{ $platformSyncJob->last_success_at ? $platformSyncJob->last_success_at->format('d M Y, h:i:s A') : __('Never') }}</span>
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
                                            <span class="fw-semibold">{{ __('Last Failed') }}</span>
                                            <span
                                                class="fw-medium text-danger">{{ $platformSyncJob->last_failed_at ? $platformSyncJob->last_failed_at->format('d M Y, h:i:s A') : __('Never') }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="text-danger"><i class="ti tabler-alert-circle me-1"></i>
                                {{ __('Last Error Details') }}</h6>
                            @if ($platformSyncJob->last_error)
                                <div class="alert alert-danger d-flex align-items-center" role="alert">
                                    <i class="icon-base ti tabler-alert-circle ti-xs me-2"></i>
                                    <div class="d-flex flex-column">
                                        <span>{{ $platformSyncJob->last_error }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-success d-flex align-items-center" role="alert">
                                    <i class="icon-base ti tabler-circle-check ti-xs me-2"></i>
                                    <span>{{ __('No errors in the last execution.') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="text-info"><i class="ti tabler-code me-1"></i> {{ __('Last Run Metadata') }}</h6>
                            <div class="bg-dark text-white p-3 rounded overflow-auto"
                                style="max-height: 400px; font-size: 13px;">
                                @if ($platformSyncJob->metadata)
                                    <pre class="mb-0"><code class="language-json">{{ json_encode($platformSyncJob->metadata, JSON_PRETTY_PRINT) }}</code></pre>
                                @else
                                    <span class="text-muted">{{ __('No metadata available.') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
