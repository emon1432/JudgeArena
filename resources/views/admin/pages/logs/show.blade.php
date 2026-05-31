@extends('admin.layouts.app')
@section('title', __('Application Log Details'))
@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Application Log Details') }}</h5>
            <a href="{{ route('logs.index') }}" class="btn btn-label-secondary">
                <i class="icon-base ti tabler-arrow-left me-1"></i>{{ __('Back') }}
            </a>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1">{{ __('Level') }}</label>
                    <div class="fw-medium text-uppercase">{{ $log->level }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1">{{ __('Category') }}</label>
                    <div class="fw-medium text-capitalize">{{ $log->category }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1">{{ __('Platform') }}</label>
                    <div class="fw-medium text-capitalize">{{ $log->platform ?? __('N/A') }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1">{{ __('Entity') }}</label>
                    <div class="fw-medium">
                        {{ $log->entity_type ?? __('N/A') }}
                        @if ($log->entity_id)
                            : {{ $log->entity_id }}
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1">{{ __('Source') }}</label>
                    <div class="fw-medium">{{ $log->source }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1">{{ __('Created At') }}</label>
                    <div>{{ $log->created_at?->format('d M, Y h:i A') }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1">{{ __('User') }}</label>
                    <div>{{ $log->user?->name ?? __('System') }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1">{{ __('IP Address') }}</label>
                    <div>{{ $log->ip_address ?? __('N/A') }}</div>
                </div>
                <div class="col-12">
                    <label class="form-label text-muted mb-1">{{ __('Message') }}</label>
                    <div class="border rounded p-3 bg-label-secondary">{{ $log->message }}</div>
                </div>
                <div class="col-12">
                    <label class="form-label text-muted mb-1">{{ __('Context JSON') }}</label>
                    <pre class="border rounded p-3 bg-body mb-0" style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($log->context ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>
    </div>
@endsection
