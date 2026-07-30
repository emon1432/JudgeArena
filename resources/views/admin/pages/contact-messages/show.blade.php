@extends('admin.layouts.app')
@section('title', __('Contact Message Details'))
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">{{ __('Contact Message Details') }}</h4>
        <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-label-secondary">
            <i class="icon-base ti tabler-arrow-left me-1"></i>{{ __('Back') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1">{{ __('Name') }}</label>
                    <div class="fw-medium">{{ $contactMessage->name }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1">{{ __('Email') }}</label>
                    <div><a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1">{{ __('Subject') }}</label>
                    <div class="fw-medium">{{ $contactMessage->subject }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1">{{ __('Status') }}</label>
                    <div>
                        @php
                            $badgeColor = match ($contactMessage->status) {
                                'new' => 'warning',
                                'read' => 'info',
                                'replied' => 'success',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-label-{{ $badgeColor }}">{{ ucfirst($contactMessage->status) }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1">{{ __('Received At') }}</label>
                    <div>{{ $contactMessage->created_at?->format('d M, Y h:i A') }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted mb-1">{{ __('IP Address') }}</label>
                    <div>{{ $contactMessage->ip_address ?? __('N/A') }}</div>
                </div>
                <div class="col-12">
                    <label class="form-label text-muted mb-1">{{ __('Message') }}</label>
                    <div class="border rounded p-3 bg-label-secondary" style="white-space: pre-wrap;">{{ $contactMessage->message }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
