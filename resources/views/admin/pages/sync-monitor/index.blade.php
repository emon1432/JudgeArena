@extends('admin.layouts.app')
@section('title', __('Sync Monitor'))
@section('content')
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
            return $metadata['retry_count'] ?? $metadata['attempt_count'] ?? 'N/A';
        };
    @endphp

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">{{ __('Total Sync States') }}</div>
                    <div class="fs-3 fw-bold">{{ $summary['total'] }}</div>
                </div>
            </div>
        </div>
        @foreach (['pending', 'syncing', 'synced', 'failed'] as $status)
            <div class="col-md-6 col-xl">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">{{ __(ucfirst($status)) }}</div>
                        <div class="fs-3 fw-bold text-{{ $statusColors[$status] }}">{{ $summary[$status] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">{{ __('Filters') }}</h5>
        </div>
        <div class="card-body mt-5">
            <form method="GET" action="{{ route('sync-monitor.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">{{ __('Platform') }}</label>
                    <select name="platform" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($filterOptions['platforms'] as $platform)
                            <option value="{{ $platform->slug }}" @selected($filters['platform'] === $platform->slug)>
                                {{ $platform->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Entity Type') }}</label>
                    <select name="entity_type" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($filterOptions['entityTypes'] as $entityType)
                            <option value="{{ $entityType->value }}" @selected($filters['entity_type'] === $entityType->value)>
                                {{ $entityLabels[$entityType->value] ?? str($entityType->value)->replace('_', ' ')->title() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($filterOptions['statuses'] as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                    <a href="{{ route('sync-monitor.index') }}" class="btn btn-label-secondary">{{ __('Reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">{{ __('Platform Breakdown') }}</h5>
        </div>
        <div class="card-body">
            @forelse ($platformBreakdown as $platform)
                <div class="py-4 border-bottom">
                    <h6 class="mb-3">{{ $platform['platform_name'] }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
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
                                @foreach ($platform['entities'] as $entityType => $counts)
                                    <tr>
                                        <td>{{ $entityLabels[$entityType] ?? str($entityType)->replace('_', ' ')->title() }}</td>
                                        <td>{{ $counts['total'] }}</td>
                                        <td>{{ $counts['synced'] }}</td>
                                        <td>{{ $counts['failed'] }}</td>
                                        <td>{{ $counts['pending'] }}</td>
                                        <td>{{ $counts['syncing'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <p class="text-muted my-5">{{ __('No sync state records found.') }}</p>
            @endforelse
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">{{ __('Recent Failures') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
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
                                $entityType = $stateValue($state->entity_type);
                            @endphp
                            <tr>
                                <td>{{ $state->platform?->name ?? '-' }}</td>
                                <td>{{ $entityLabels[$entityType] ?? str($entityType)->replace('_', ' ')->title() }}</td>
                                <td>{{ $state->entity_platform_id ?? '-' }}</td>
                                <td>{{ str($state->last_error ?? '-')->limit(120) }}</td>
                                <td>{{ $retryCount($state) }}</td>
                                <td>{{ $state->updated_at?->format('d M, Y h:i A') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('sync-monitor.retry', $state) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-label-warning">{{ __('Retry Sync') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">{{ __('No failed sync states found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $recentFailures->links() }}
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">{{ __('Recent Activity') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
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
                                $entityType = $stateValue($state->entity_type);
                                $status = $stateValue($state->sync_status);
                            @endphp
                            <tr>
                                <td>{{ $state->platform?->name ?? '-' }}</td>
                                <td>{{ $entityLabels[$entityType] ?? str($entityType)->replace('_', ' ')->title() }}</td>
                                <td>{{ $state->entity_platform_id ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $statusColors[$status] ?? 'secondary' }} text-uppercase">
                                        {{ $status }}
                                    </span>
                                </td>
                                <td>{{ $state->updated_at?->format('d M, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">{{ __('No sync activity found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $recentActivity->links() }}
        </div>
    </div>
@endsection
