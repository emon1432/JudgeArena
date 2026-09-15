@extends('admin.layouts.app')
@section('title', __('Contests'))
@section('content')
    {{-- Filter Card --}}
    <div class="card mb-4">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="icon-base ti tabler-filter icon-sm text-primary"></i>
                <h6 class="card-title mb-0 fw-semibold">{{ __('Filter Contests') }}</h6>
            </div>
            <button type="button" class="btn btn-sm btn-label-secondary dt-filter-reset">
                <i class="icon-base ti tabler-rotate-clockwise icon-xs me-1"></i>
                {{ __('Reset Filters') }}
            </button>
        </div>
        <div class="card-body pt-4">
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-medium">{{ __('Platform') }}</label>
                    <select class="form-select" data-dt-filter="platform">
                        <option value="">{{ __('All Platforms') }}</option>
                        @foreach($platforms as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-medium">{{ __('Phase') }}</label>
                    <select class="form-select" data-dt-filter="phase">
                        <option value="">{{ __('All Phases') }}</option>
                        @foreach($phases as $phase)
                            <option value="{{ $phase }}">{{ ucfirst($phase) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-medium">{{ __('Standings Status') }}</label>
                    <select class="form-select" data-dt-filter="standings_status">
                        <option value="">{{ __('All Standings') }}</option>
                        <option value="uploaded">{{ __('Uploaded') }}</option>
                        <option value="missing">{{ __('Missing / Not Uploaded') }}</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-medium">{{ __('Status') }}</label>
                    <select class="form-select" data-dt-filter="status">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="1">{{ __('Active') }}</option>
                        <option value="0">{{ __('Inactive') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Datatable Card --}}
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Contests') }}</h5>
        </div>
        <div class="card-body">
            <div class="card-datatable">
                <table class="common-datatable table d-table" data-url="{{ route('admin.all-contests.index') }}"
                    data-columns='[
                { "data": "name" },
                { "data": "platformName", "className": "text-center" },
                { "data": "phase", "className": "text-center" },
                { "data": "startAt", "className": "text-center" },
                { "data": "standingsCache", "orderable": false, "className": "text-center" },
                { "data": "status", "className": "text-center" },
                { "data": "actions", "orderable": false, "className": "text-center" }
                ]'>
                    <thead class="border-top">
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th class="text-center">{{ __('Platform') }}</th>
                            <th class="text-center">{{ __('Phase') }}</th>
                            <th class="text-center">{{ __('Start At') }}</th>
                            <th class="text-center">{{ __('Standings') }}</th>
                            <th class="text-center">{{ __('Status') }}</th>
                            <th class="text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    @include('admin.pages.contests.scripts')
@endpush
